<?php

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('pode listar o histórico privado de combinações da modalidade', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $user = User::factory()->create();

    CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => [
            'sum' => 15,
            'even_count' => 2,
            'odd_count' => 3,
        ],
    ]);

    CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [10, 20, 30, 40, 50],
        'source' => 'generated',
        'analysis_snapshot' => [
            'sum' => 150,
            'even_count' => 5,
            'odd_count' => 0,
        ],
    ]);

    $response = $this->actingAs($user)->get("/lottery/modalities/{$quina->id}/combination-history");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/CombinationHistory')
        ->has('items.data', 2)
        ->where('modality.code', 'quina')
    );
});

it('pode filtrar o histórico privado de combinações por origem', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $user = User::factory()->create();

    CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => ['sum' => 15],
    ]);

    CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [10, 20, 30, 40, 50],
        'source' => 'generated',
        'analysis_snapshot' => ['sum' => 150],
    ]);

    $response = $this->actingAs($user)->get("/lottery/modalities/{$quina->id}/combination-history?source=generated");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/CombinationHistory')
        ->has('items.data', 1)
        ->where('filters.source', 'generated')
    );
});

it('pode exibir a narrativa no histórico privado de combinações', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $user = User::factory()->create();

    CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => [
            'sum' => 15,
            'even_count' => 2,
            'odd_count' => 3,
            'average_frequency' => 1.6,
            'score' => [
                'value' => 74.5,
                'label' => 'Boa',
                'profile' => 'Equilibrado',
                'breakdown' => [
                    'historical_alignment' => 30,
                    'frequency_strength' => 15,
                    'delay_balance' => 10,
                    'pattern_balance' => 12,
                    'hot_cold_mix' => 7.5,
                ],
            ],
            'narrative' => [
                'headline' => 'A combinação está relativamente próxima do perfil histórico médio.',
                'insights' => [
                    'A soma da combinação está próxima da média histórica.',
                    'A quantidade de pares coincide com o padrão mais comum do histórico.',
                ],
            ],
        ],
    ]);

    $response = $this->actingAs($user)->get("/lottery/modalities/{$quina->id}/combination-history");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/CombinationHistory')
        ->has('items.data', 1)
        ->where('items.data.0.analysis_snapshot.narrative.headline', 'A combinação está relativamente próxima do perfil histórico médio.')
        ->where('items.data.0.analysis_snapshot.score.label', 'Boa')
    );
});
