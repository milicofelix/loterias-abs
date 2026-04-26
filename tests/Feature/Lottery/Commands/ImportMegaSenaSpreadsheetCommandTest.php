<?php

use App\Models\LotteryModality;
use App\Services\Lottery\Importers\CaixaSpreadsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('importa a mega-sena por comando artisan', function () {
    $megaSena = LotteryModality::factory()->megaSena()->create();
    $file = storage_path('app/testing/mega-sena-command.xlsx');

    if (! is_dir(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }

    file_put_contents($file, 'fake spreadsheet');

    $importer = Mockery::mock(CaixaSpreadsheetImporter::class);
    $importer->shouldReceive('import')
        ->once()
        ->withArgs(fn (string $path, LotteryModality $modality) => $path === $file && $modality->is($megaSena))
        ->andReturn([
            'imported' => 2,
            'existing' => 1,
            'skipped' => 0,
        ]);

    $this->app->instance(CaixaSpreadsheetImporter::class, $importer);

    $this->artisan('lottery:import-mega-sena', ['file' => $file])
        ->expectsOutput('Importação concluída com sucesso.')
        ->assertExitCode(0);
});
