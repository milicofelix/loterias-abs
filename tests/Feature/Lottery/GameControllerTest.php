<?php

use App\Models\LotteryModality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\CombinationHistory;
use App\Models\Draw;
use App\Models\DrawNumber;

uses(RefreshDatabase::class);

it('pode abrir a página de reprodução para uma modalidade', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->get("/lottery/modalities/{$quina->id}/play");

    $response->assertStatus(200);
});

it('pode abrir a tela play com números vindos do histórico', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->get("/lottery/modalities/{$quina->id}/play?numbers=1,2,3,4,5");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/Play')
        ->where('prefilledNumbers', [1, 2, 3, 4, 5])
    );
});

it('pode abrir a tela play com números vindos do combination history via history_id', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $history = CombinationHistory::factory()->create([
        'lottery_modality_id' => $quina->id,
        'numbers' => [22, 41, 42, 49, 53],
        'source' => 'generated',
        'analysis_snapshot' => [],
    ]);

    $response = $this->get("/lottery/modalities/{$quina->id}/play?history_id={$history->id}");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/Play')
        ->where('prefilledNumbers', [22, 41, 42, 49, 53])
    );
});