<?php

use App\Models\LotteryModality;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Draw;
use App\Models\DrawNumber;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('pode listar as modalidades página', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $draw = Draw::create([
        'lottery_modality_id' => $quina->id,
        'contest_number' => 1234,
        'draw_date' => '2026-03-30',
        'metadata' => ['observação' => 'teste dashboard'],
    ]);

    foreach ([1, 2, 3, 4, 5] as $number) {
        DrawNumber::create([
            'draw_id' => $draw->id,
            'number' => $number,
        ]);
    }
    $response = $this->get("/lottery/modalities/{$quina->id}");

    $response->assertStatus(200);
    $response->assertSee('1234');
    $response->assertSee('teste dashboard');
    $response->assertSee('30\/03\/2026', false);
});

it('pode exibir o painel (Dashboard) de controle da modalidade', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->get("/lottery/modalities/{$quina->id}");

    $response->assertStatus(200);
});

it('pode gerar números através do endpoint', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->post("/lottery/modalities/{$quina->id}/generate", []);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'numbers',
        ]);
});

it('pode analisar números através do endpoint', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->post("/lottery/modalities/{$quina->id}/analyze", [
        'numbers' => [1, 2, 3, 4, 5],
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'sum',
            'even_count',
            'odd_count',
            'average_frequency',
            'average_delay',
            'top_frequency_hits',
            'top_delay_hits',
            'score' => [
                'value',
                'label',
                'profile',
                'breakdown',
            ],
        ]);
});

it('pode analisar números com comparação histórica por meio do ponto final.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->post("/lottery/modalities/{$quina->id}/analyze", [
        'numbers' => [1, 2, 3, 4, 5],
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'sum',
            'even_count',
            'odd_count',
            'average_frequency',
            'average_delay',
            'top_frequency_hits',
            'top_delay_hits',
            'score' => [
                'value',
                'label',
                'profile',
                'breakdown' => [
                    'historical_alignment',
                    'frequency_strength',
                    'delay_balance',
                    'pattern_balance',
                    'hot_cold_mix',
                ],
            ],
            'historical_averages' => [
                'sum',
                'even_count',
                'range',
            ],
            'historical_comparison' => [
                'sum_diff',
                'even_count_diff',
                'range_diff',
            ],
            'most_common_even_count',
            'within_common_even_pattern',
            'narrative' => [
                'headline',
                'insights',
            ],
            'agent' => [
                'name',
                'version',
                'summary',
                'strengths',
                'warnings',
            ],
            'profile_comparison' => [
                'name',
                'version',
                'left' => [
                    'numbers',
                    'statistics' => [
                        'sum',
                        'range',
                        'even_count',
                        'odd_count',
                        'consecutive_count',
                    ],
                    'historical_comparison',
                    'historical_alignment_score',
                ],
                'right',
                'summary',
                'highlights',
                'winner',
            ],
        ]);
});

it('Retorna 422 ao tentar analisar números inválidos.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->post("/lottery/modalities/{$quina->id}/analyze", [
        'numbers' => [1, 2, 3],
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['message']);
});

it('Retorna 422 ao tentar gerar uma contagem inválida.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->post("/lottery/modalities/{$quina->id}/generate", [
        'count' => 99,
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['message']);
});

it('Pode exibir janelas de frequência e atraso recentes no painel de controle (Dashboard).', function () {
    $quina = LotteryModality::factory()->quina()->create();

    foreach (range(1, 12) as $contest) {
        $draw = \App\Models\Draw::create([
            'lottery_modality_id' => $quina->id,
            'contest_number' => $contest,
            'draw_date' => now()->subDays(12 - $contest)->toDateString(),
            'metadata' => null,
        ]);

        foreach ([1, 2, 3, 4, 5] as $number) {
            \App\Models\DrawNumber::create([
                'draw_id' => $draw->id,
                'number' => $number + $contest - 1,
            ]);
        }
    }

    $response = $this->get("/lottery/modalities/{$quina->id}");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/Dashboard')
        ->has('frequenciesLast10')
        ->has('frequenciesLast20')
        ->has('frequenciesLast50')
        ->has('delaysLast10')
        ->has('delaysLast20')
        ->has('delaysLast50')
        ->has('recentDraws', 12)
        ->where('totalDraws', 12)
        ->where('latestContestNumber', 12)
    );
});