<?php

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('protege a página de reprodução para usuários não autenticados', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->get("/lottery/modalities/{$quina->id}/play")
        ->assertRedirect('/login');
});

it('pode abrir a tela play com números vindos da query quando autenticado', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get("/lottery/modalities/{$quina->id}/play?numbers=1,2,3,4,5");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/Play')
        ->where('prefilledNumbers', [1, 2, 3, 4, 5])
    );
});

it('pode abrir a tela play com números vindos do combination history via history_id', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $user = User::factory()->create();

    $history = CombinationHistory::factory()->create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [22, 41, 42, 49, 53],
        'source' => 'generated',
        'analysis_snapshot' => [],
    ]);

    $response = $this->actingAs($user)
        ->get("/lottery/modalities/{$quina->id}/play?history_id={$history->id}");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/Play')
        ->where('prefilledNumbers', [22, 41, 42, 49, 53])
    );
});
