<?php

use App\Models\LotteryModality;
use App\Models\User;
use App\Services\Lottery\CaixaResultsSyncService;
use App\Services\Lottery\LotterySyncStatusStore;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can sync modality results from caixa through the dashboard action', function () {
    $this->actingAs(User::factory()->create());

    $modality = LotteryModality::factory()->quina()->create();

    $service = Mockery::mock(CaixaResultsSyncService::class);
    $service->shouldReceive('sync')
        ->once()
        ->withArgs(fn ($arg) => $arg->is($modality))
        ->andReturn([
            'imported' => 2,
            'existing' => 5,
            'skipped' => 0,
        ]);

    $this->app->instance(CaixaResultsSyncService::class, $service);

    $response = $this->from("/lottery/modalities/{$modality->id}")
        ->post("/lottery/modalities/{$modality->id}/sync-results");

    $response->assertRedirect("/lottery/modalities/{$modality->id}")
        ->assertSessionHas('success', 'Sincronização concluída: 2 novos, 5 já existentes, 0 ignorados.');
});

it('returns a friendly error when sync fails', function () {
    $this->actingAs(User::factory()->create());

    $modality = LotteryModality::factory()->megaSena()->create();

    $service = Mockery::mock(CaixaResultsSyncService::class);
    $service->shouldReceive('sync')
        ->once()
        ->andThrow(new InvalidArgumentException('A sincronização automática pela CAIXA está disponível apenas para a Quina nesta etapa.'));

    $this->app->instance(CaixaResultsSyncService::class, $service);

    $response = $this->from("/lottery/modalities/{$modality->id}")
        ->post("/lottery/modalities/{$modality->id}/sync-results");

    $response->assertRedirect("/lottery/modalities/{$modality->id}")
        ->assertSessionHas('error', 'A sincronização automática pela CAIXA está disponível apenas para a Quina nesta etapa.');
});

it('redirects guests to login when trying to sync caixa results', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $response = $this->post("/lottery/modalities/{$modality->id}/sync-results");

    $response->assertRedirect(route('login'));
});

it('starts an async caixa sync when json is requested', function () {
    $this->actingAs(User::factory()->create());

    $modality = LotteryModality::factory()->quina()->create();

    $service = Mockery::mock(CaixaResultsSyncService::class);
    $service->shouldReceive('sync')
        ->zeroOrMoreTimes()
        ->andReturn([
            'imported' => 1,
            'existing' => 0,
            'skipped' => 0,
        ]);

    $this->app->instance(CaixaResultsSyncService::class, $service);

    $response = $this->postJson("/lottery/modalities/{$modality->id}/sync-results");

    $response->assertAccepted()
        ->assertJsonStructure([
            'sync' => [
                'id',
                'status',
                'message',
                'modality_id',
                'user_id',
            ],
        ])
        ->assertJsonPath('sync.status', 'queued');
});

it('returns async caixa sync status only for the owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $modality = LotteryModality::factory()->quina()->create();
    $store = app(LotterySyncStatusStore::class);

    $syncId = $store->create($modality, $user->id);

    $this->actingAs($user)
        ->getJson("/lottery/modalities/{$modality->id}/sync-results/{$syncId}")
        ->assertOk()
        ->assertJsonPath('sync.id', $syncId);

    $this->actingAs($otherUser)
        ->getJson("/lottery/modalities/{$modality->id}/sync-results/{$syncId}")
        ->assertNotFound();
});
