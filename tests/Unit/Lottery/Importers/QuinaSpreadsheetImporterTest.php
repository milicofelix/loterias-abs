<?php

use App\Models\Draw;
use App\Models\LotteryModality;
use App\Services\Lottery\Importers\QuinaSpreadsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

function makeQuinaSpreadsheet(array $rows, string $sheetName = 'QUINA'): string
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($sheetName);

    $headers = [
        'Concurso',
        'Data Sorteio',
        'Bola1',
        'Bola2',
        'Bola3',
        'Bola4',
        'Bola5',
        'Ganhadores 5 acertos',
        'Cidade / UF',
        'Rateio 5 acertos',
        'Ganhadores 4 acertos',
        'Rateio 4 acertos',
        'Ganhadores 3 acertos',
        'Rateio 3 acertos',
        'Ganhadores 2 acertos',
        'Rateio 2 acertos',
        'Acumulado 5 acertos',
        'Arrecadacao Total',
        'Estimativa Premio',
        'Acumulado Sorteio Especial Quina de São João',
        'observação',
    ];

    $sheet->fromArray($headers, null, 'A1');

    $rowNumber = 2;
    foreach ($rows as $row) {
        $sheet->fromArray($row, null, 'A' . $rowNumber);
        $rowNumber++;
    }

    $path = storage_path('app/testing/quina-import-' . uniqid() . '.xlsx');

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('imports valid quina draws from spreadsheet', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $path = makeQuinaSpreadsheet([
        [
            1, '13/03/1994', 25, 45, 60, 76, 79,
            3, null, 'R$75.731.225,00',
            127, 'R$1.788.927,00',
            7030, 'R$42.982,00',
            0, 'R$0,00',
            'SIM', 'R$100.000,00', 'R$200.000,00', 'R$300.000,00', null,
        ],
        [
            2, '17/03/1994', 13, 30, 58, 63, 64,
            1, null, 'R$118.499.397,00',
            105, 'R$1.128.565,00',
            4861, 'R$32.422,00',
            0, 'R$0,00',
            'NÃO', 'R$110.000,00', 'R$210.000,00', 'R$310.000,00', 'teste',
        ],
    ]);

    $result = app(QuinaSpreadsheetImporter::class)->import($path, $quina);

    expect($result['imported'])->toBe(2)
        ->and($result['existing'])->toBe(0)
        ->and(Draw::count())->toBe(2);

    $draw = Draw::where('contest_number', 1)->firstOrFail();

    expect($draw->numbers()->pluck('number')->sort()->values()->all())
        ->toBe([25, 45, 60, 76, 79]);

    expect($draw->metadata)->toBeArray()
        ->and((int) $draw->metadata['Ganhadores 5 acertos'])->toBe(3);
});

it('importa apenas novos desenhos e ignora os existentes.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $path1 = makeQuinaSpreadsheet([
        [1, '13/03/1994', 1, 2, 3, 4, 5, 0, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
    ]);

    $path2 = makeQuinaSpreadsheet([
        [1, '13/03/1994', 10, 20, 30, 40, 50, 0, null, null, 0, null, 0, null, 0, null, null, null, null, null, 'atualizado'],
    ]);

    $importer = app(QuinaSpreadsheetImporter::class);
    $importer->import($path1, $quina);
    $result = $importer->import($path2, $quina);

    expect($result['imported'])->toBe(0)
        ->and($result['existing'])->toBe(1)
        ->and(Draw::count())->toBe(1);

    $draw = Draw::where('contest_number', 1)->firstOrFail();

    expect($draw->numbers()->pluck('number')->sort()->values()->all())
        ->toBe([1, 2, 3, 4, 5])
        ->and(($draw->metadata['observação'] ?? null))->toBeNull();
});

it('ignores empty rows', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $path = makeQuinaSpreadsheet([
        [1, '13/03/1994', 1, 2, 3, 4, 5, 0, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
        [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
    ]);

    $result = app(QuinaSpreadsheetImporter::class)->import($path, $quina);

    expect($result['imported'])->toBe(1)
        ->and(Draw::count())->toBe(1);
});

it('fails when sheet QUINA does not exist', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $path = makeQuinaSpreadsheet([], 'OUTRA');

    app(QuinaSpreadsheetImporter::class)->import($path, $quina);
})->throws(InvalidArgumentException::class, 'A aba QUINA não foi encontrada no arquivo.');

it('fails when required columns are missing', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('QUINA');
    $sheet->fromArray(['Concurso', 'Data Sorteio', 'Bola1'], null, 'A1');

    $path = storage_path('app/testing/quina-invalid-' . uniqid() . '.xlsx');

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    (new Xlsx($spreadsheet))->save($path);

    app(QuinaSpreadsheetImporter::class)->import($path, $quina);
})->throws(InvalidArgumentException::class, 'Coluna obrigatória ausente: Bola2');