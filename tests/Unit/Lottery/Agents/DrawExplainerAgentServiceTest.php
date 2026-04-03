<?php

use App\Models\LotteryModality;
use App\Services\Lottery\Agents\DrawExplainerAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('retorna a estrutura completa da explicação do concurso', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $draw = $this->createDrawWithNumbers($quina, 2, '2026-01-02', [10, 20, 30, 40, 50]);

    $result = app(DrawExplainerAgentService::class)->explain($quina, $draw);

    expect($result)->toHaveKeys([
        'name',
        'version',
        'contest_number',
        'numbers',
        'summary',
        'highlights',
        'statistics',
        'comparison',
    ]);

    expect($result['name'])->toBe('draw-explainer');
    expect($result['summary'])->toBeString()->not->toBeEmpty();
    expect($result['highlights'])->toBeArray()->not->toBeEmpty();
    expect($result['statistics'])->toHaveKeys([
        'sum',
        'range',
        'even_count',
        'odd_count',
        'consecutive_count',
    ]);
});

it('explica concurso mesmo sem sequências consecutivas', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $draw = $this->createDrawWithNumbers($quina, 2, '2026-01-02', [10, 20, 30, 40, 50]);

    $result = app(DrawExplainerAgentService::class)->explain($quina, $draw);

    expect($result['statistics']['consecutive_count'])->toBe(0);
    expect($result['summary'])->toBeString()->not->toBeEmpty();
});

it('explica concurso com sequências consecutivas', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [10, 20, 30, 40, 50]);
    $draw = $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 10, 11, 30]);

    $result = app(DrawExplainerAgentService::class)->explain($quina, $draw);

    expect($result['statistics']['consecutive_count'])->toBeGreaterThan(0);
    expect($result['highlights'])->toBeArray()->not->toBeEmpty();
});