<?php

use App\Models\Draw;
use App\Models\LotteryModality;
use App\Services\Lottery\Importers\CaixaSpreadsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

function makeMegaSenaSpreadsheet(array $rows, string $sheetName = 'MEGA-SENA'): string
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
        'Bola6',
        'Ganhadores 6 acertos',
        'Rateio 6 acertos',
        'Ganhadores 5 acertos',
        'Rateio 5 acertos',
        'Ganhadores 4 acertos',
        'Rateio 4 acertos',
        'Acumulado 6 acertos',
        'Arrecadacao Total',
        'Estimativa Premio',
        'observação',
    ];

    $sheet->fromArray($headers, null, 'A1');

    $rowNumber = 2;
    foreach ($rows as $row) {
        $sheet->fromArray($row, null, 'A' . $rowNumber);
        $rowNumber++;
    }

    $path = storage_path('app/testing/mega-sena-import-' . uniqid() . '.xlsx');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('imports valid mega-sena draws from spreadsheet', function () {
    $megaSena = LotteryModality::factory()->megaSena()->create();

    $path = makeMegaSenaSpreadsheet([
        [1, '11/03/1996', 4, 5, 30, 33, 41, 52, 0, null, 17, null, 2016, null, 'SIM', 'R$ 0,00', 'R$ 0,00', 'primeiro concurso'],
        [2, '18/03/1996', 9, 37, 39, 41, 43, 49, 1, null, 65, null, 4488, null, 'NÃO', 'R$ 0,00', 'R$ 0,00', 'segundo concurso'],
    ]);

    $result = app(CaixaSpreadsheetImporter::class)->import($path, $megaSena);

    expect($result['imported'])->toBe(2)
        ->and($result['existing'])->toBe(0)
        ->and(Draw::query()->where('lottery_modality_id', $megaSena->id)->count())->toBe(2);

    $draw = Draw::query()->where('contest_number', 1)->firstOrFail();

    expect($draw->numbers()->pluck('number')->sort()->values()->all())
        ->toBe([4, 5, 30, 33, 41, 52])
        ->and($draw->metadata)->toBeArray()
        ->and($draw->metadata['observação'])->toBe('primeiro concurso');
});

it('accepts alternative mega-sena sheet names from caixa', function () {
    $megaSena = LotteryModality::factory()->megaSena()->create();

    $path = makeMegaSenaSpreadsheet([
        [10, '01/04/1996', 1, 2, 3, 4, 5, 6, 0, null, 0, null, 0, null, 'NÃO', null, null, null],
    ], 'MEGA SENA');

    $result = app(CaixaSpreadsheetImporter::class)->import($path, $megaSena);

    expect($result['imported'])->toBe(1)
        ->and(Draw::count())->toBe(1);
});

it('rejects mega-sena rows with numbers outside the modality range', function () {
    $megaSena = LotteryModality::factory()->megaSena()->create();

    $path = makeMegaSenaSpreadsheet([
        [1, '11/03/1996', 4, 5, 30, 33, 41, 61, 0, null, 0, null, 0, null, 'SIM', null, null, null],
    ]);

    app(CaixaSpreadsheetImporter::class)->import($path, $megaSena);
})->throws(InvalidArgumentException::class, 'Número fora do intervalo permitido para Mega-Sena.');
