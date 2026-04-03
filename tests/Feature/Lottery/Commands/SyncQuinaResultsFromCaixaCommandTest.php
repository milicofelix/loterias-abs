<?php

use App\Services\Lottery\CaixaResultsSyncService;

it('sincroniza a quina pela caixa via comando artisan', function () {
    $service = Mockery::mock(CaixaResultsSyncService::class);
    $service->shouldReceive('syncQuina')
        ->once()
        ->andReturn([
            'modality' => 'Quina',
            'imported' => 3,
            'existing' => 10,
            'skipped' => 1,
        ]);

    $this->app->instance(CaixaResultsSyncService::class, $service);

    $this->artisan('lottery:sync-quina')
        ->expectsOutput('Sincronização concluída com sucesso.')
        ->assertExitCode(0);
});
