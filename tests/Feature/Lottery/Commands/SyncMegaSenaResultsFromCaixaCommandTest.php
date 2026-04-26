<?php

use App\Services\Lottery\CaixaResultsSyncService;

it('sincroniza a mega-sena pela caixa via comando artisan', function () {
    $service = Mockery::mock(CaixaResultsSyncService::class);
    $service->shouldReceive('syncMegaSena')
        ->once()
        ->andReturn([
            'modality' => 'Mega-Sena',
            'imported' => 3,
            'existing' => 10,
            'skipped' => 1,
        ]);

    $this->app->instance(CaixaResultsSyncService::class, $service);

    $this->artisan('lottery:sync-mega-sena')
        ->expectsOutput('Sincronização concluída com sucesso.')
        ->assertExitCode(0);
});
