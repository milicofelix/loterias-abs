<?php

use App\Models\Draw;
use App\Models\LotteryModality;
use App\Services\Lottery\CaixaResultsDownloaderService;
use App\Services\Lottery\CaixaResultsSyncService;
use App\Services\Lottery\Importers\CaixaSpreadsheetImporter;
use App\Services\Lottery\LotteryRulesService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('passes only contests after the latest saved contest to the importer', function () {
    $modality = LotteryModality::factory()->quina()->create();

    Draw::factory()->create([
        'lottery_modality_id' => $modality->id,
        'contest_number' => 10,
    ]);

    $path = storage_path('app/testing/sync-incremental.xlsx');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    file_put_contents($path, 'fake spreadsheet contents');

    $downloader = Mockery::mock(CaixaResultsDownloaderService::class);
    $downloader->shouldReceive('downloadSpreadsheet')
        ->once()
        ->with($modality->name)
        ->andReturn($path);

    $importer = Mockery::mock(CaixaSpreadsheetImporter::class);
    $importer->shouldReceive('import')
        ->once()
        ->with($path, Mockery::on(fn ($arg) => $arg->is($modality)), ['min_contest_number' => 11])
        ->andReturn([
            'imported' => 1,
            'existing' => 10,
            'skipped' => 0,
        ]);

    $service = new CaixaResultsSyncService(
        $downloader,
        $importer,
        app(LotteryRulesService::class),
    );

    $result = $service->sync($modality);

    expect($result['imported'])->toBe(1)
        ->and($result['existing'])->toBe(10)
        ->and(file_exists($path))->toBeFalse();
});
