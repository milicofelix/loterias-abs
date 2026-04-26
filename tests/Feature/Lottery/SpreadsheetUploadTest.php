<?php

use App\Models\Draw;
use App\Models\LotteryModality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\User;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function makeUploadedLotterySpreadsheet(array $rows, array $headers, string $sheetName, string $filename): UploadedFile
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($sheetName);
    $sheet->fromArray($headers, null, 'A1');

    $rowNumber = 2;
    foreach ($rows as $row) {
        $sheet->fromArray($row, null, 'A' . $rowNumber);
        $rowNumber++;
    }

    $path = storage_path('app/testing/' . uniqid('lottery-upload-', true) . '.xlsx');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        $filename,
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

function makeUploadedQuinaSpreadsheet(array $rows, string $sheetName = 'QUINA', string $filename = 'quina-upload.xlsx'): UploadedFile
{
    $headers = [
        'Concurso', 'Data Sorteio', 'Bola1', 'Bola2', 'Bola3', 'Bola4', 'Bola5',
        'Ganhadores 5 acertos', 'Cidade / UF', 'Rateio 5 acertos',
        'Ganhadores 4 acertos', 'Rateio 4 acertos',
        'Ganhadores 3 acertos', 'Rateio 3 acertos',
        'Ganhadores 2 acertos', 'Rateio 2 acertos',
        'Acumulado 5 acertos', 'Arrecadacao Total', 'Estimativa Premio',
        'Acumulado Sorteio Especial Quina de São João', 'observação',
    ];

    return makeUploadedLotterySpreadsheet($rows, $headers, $sheetName, $filename);
}

function makeUploadedLotofacilSpreadsheet(array $rows, string $sheetName = 'LOTOFACIL', string $filename = 'lotofacil-upload.xlsx'): UploadedFile
{
    $headers = ['Concurso', 'Data Sorteio'];
    for ($i = 1; $i <= 15; $i++) {
        $headers[] = 'Bola' . $i;
    }
    $headers[] = 'observação';

    return makeUploadedLotterySpreadsheet($rows, $headers, $sheetName, $filename);
}

function makeUploadedMegaSenaSpreadsheet(array $rows, string $sheetName = 'MEGA-SENA', string $filename = 'mega-sena-upload.xlsx'): UploadedFile
{
    $headers = ['Concurso', 'Data Sorteio'];
    for ($i = 1; $i <= 6; $i++) {
        $headers[] = 'Bola' . $i;
    }
    $headers[] = 'observação';

    return makeUploadedLotterySpreadsheet($rows, $headers, $sheetName, $filename);
}



it('importa manualmente uma planilha da quina pela interface', function () {
    $user = User::factory()->create();
    actingAs($user);

    $quina = LotteryModality::factory()->quina()->create();

    $file = makeUploadedQuinaSpreadsheet([
        [1, '13/03/1994', 25, 45, 60, 76, 79, 3, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
        [2, '17/03/1994', 13, 30, 58, 63, 64, 1, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
    ], filename: 'quina-upload.xlsx');

    $response = $this->from(route('lottery.modalities.show', $quina))
        ->post(route('lottery.modalities.import-spreadsheet', $quina), [
            'spreadsheet' => $file,
            ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('import_result', function (array $result) {
        return $result['imported'] === 2
            && $result['existing'] === 0
            && $result['skipped'] === 0
            && $result['filename'] === 'quina-upload.xlsx';
    });

    expect(Draw::count())->toBe(2);
});

it('importa manualmente uma planilha da lotofacil pela interface', function () {
    $user = User::factory()->create();
    actingAs($user);

    $lotofacil = LotteryModality::factory()->lotofacil()->create();

    $file = makeUploadedLotofacilSpreadsheet([
        [1001, '01/04/2026', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 'teste'],
        [1002, '02/04/2026', 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 'teste 2'],
    ]);

    $response = $this->post(route('lottery.modalities.import-spreadsheet', $lotofacil), [
        'spreadsheet' => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(Draw::count())->toBe(2)
        ->and(Draw::query()->where('lottery_modality_id', $lotofacil->id)->count())->toBe(2);
});

it('importa manualmente uma planilha da mega-sena pela interface', function () {
    $user = User::factory()->create();
    actingAs($user);

    $megaSena = LotteryModality::factory()->megaSena()->create();

    $file = makeUploadedMegaSenaSpreadsheet([
        [1, '11/03/1996', 4, 5, 30, 33, 41, 52, 'primeiro concurso'],
        [2, '18/03/1996', 9, 37, 39, 41, 43, 49, 'segundo concurso'],
    ]);

    $response = $this->post(route('lottery.modalities.import-spreadsheet', $megaSena), [
        'spreadsheet' => $file,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('import_result', function (array $result) {
        return $result['imported'] === 2
            && $result['existing'] === 0
            && $result['skipped'] === 0
            && $result['filename'] === 'mega-sena-upload.xlsx';
    });

    expect(Draw::query()->where('lottery_modality_id', $megaSena->id)->count())->toBe(2);
});

it('ignora concursos já existentes no upload manual', function () {
    $user = User::factory()->create();
    actingAs($user);
    $quina = LotteryModality::factory()->quina()->create();

    $file1 = makeUploadedQuinaSpreadsheet([
        [1, '13/03/1994', 25, 45, 60, 76, 79, 3, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
    ], filename: 'primeiro.xlsx');

    $this->post(route('lottery.modalities.import-spreadsheet', $quina), [
        'spreadsheet' => $file1,
    ])->assertRedirect();

   $file2 = makeUploadedQuinaSpreadsheet([
        [1, '13/03/1994', 25, 45, 60, 76, 79, 3, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
        [2, '17/03/1994', 13, 30, 58, 63, 64, 1, null, null, 0, null, 0, null, 0, null, null, null, null, null, null],
    ], filename: 'quina-upload.xlsx');

    $response = $this->post(route('lottery.modalities.import-spreadsheet', $quina), [
        'spreadsheet' => $file2,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('import_result', function (array $result) {
        return $result['imported'] === 1
            && $result['existing'] === 1
            && $result['skipped'] === 0
            && $result['filename'] === 'quina-upload.xlsx';
    });

    expect(Draw::count())->toBe(2);
    expect(
        Draw::query()->where('contest_number', 1)->firstOrFail()->numbers()->pluck('number')->sort()->values()->all()
    )->toBe([25, 45, 60, 76, 79]);
});

it('valida o arquivo no upload manual', function () {
    $user = User::factory()->create();
    actingAs($user);

    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->from(route('lottery.modalities.show', $quina))
        ->post(route('lottery.modalities.import-spreadsheet', $quina), []);

    $response->assertRedirect(route('lottery.modalities.show', $quina));
    $response->assertSessionHasErrors(['spreadsheet']);
});
