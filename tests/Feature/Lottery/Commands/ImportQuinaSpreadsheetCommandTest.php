<?php

use App\Models\Draw;
use App\Models\LotteryModality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

function makeQuinaSpreadsheetForCommand(array $rows, string $sheetName = 'QUINA'): string
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

    $path = storage_path('app/testing/quina-command-' . uniqid() . '.xlsx');

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('importa planilha quina através de comando artisan', function () {
    LotteryModality::factory()->quina()->create();

    $path = makeQuinaSpreadsheetForCommand([
        [1, '13/03/1994', 25, 45, 60, 76, 79, 3, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
        [2, '17/03/1994', 13, 30, 58, 63, 64, 1, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
    ]);

    $this->artisan("lottery:import-quina {$path}")
        ->expectsOutput('Importação concluída com sucesso.')
        ->assertExitCode(0);

    expect(Draw::count())->toBe(2);
});

it('ignora concursos já existentes ao reimportar a planilha', function () {
    LotteryModality::factory()->quina()->create();

    $path = makeQuinaSpreadsheetForCommand([
        [1, '13/03/1994', 25, 45, 60, 76, 79, 3, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
    ]);

    $this->artisan("lottery:import-quina {$path}")
        ->expectsOutput('Importação concluída com sucesso.')
        ->assertExitCode(0);

    $this->artisan("lottery:import-quina {$path}")
        ->expectsOutput('Importação concluída com sucesso.')
        ->assertExitCode(0);

    expect(Draw::count())->toBe(1);
});

it('Falha quando o arquivo não existe.', function () {
    LotteryModality::factory()->quina()->create();

    $this->artisan('lottery:import-quina /arquivo/inexistente.xlsx')
        ->expectsOutput('Arquivo não encontrado: /arquivo/inexistente.xlsx')
        ->assertExitCode(1);
});