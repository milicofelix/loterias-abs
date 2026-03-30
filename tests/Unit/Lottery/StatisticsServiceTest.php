<?php

use App\Models\LotteryModality;
use App\Services\Lottery\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('Calcula a frequência total para uma modalidade.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 6, 7, 8]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [2, 9, 10, 11, 12]);

    $stats = app(StatisticsService::class)->numberFrequencies($quina);

    expect($stats[1])->toBe(2)
        ->and($stats[2])->toBe(3)
        ->and($stats[3])->toBe(1)
        ->and($stats[12])->toBe(1)
        ->and($stats[80])->toBe(0);
});

it('Não mistura frequências de outras modalidades.', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $mega = LotteryModality::factory()->megaSena()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 6, 7, 8]);

    $this->createDrawWithNumbers($mega, 1, '2026-01-01', [1, 2, 3, 4, 5, 6]);
    $this->createDrawWithNumbers($mega, 2, '2026-01-02', [1, 9, 10, 11, 12, 13]);

    $stats = app(StatisticsService::class)->numberFrequencies($quina);

    expect($stats[1])->toBe(2)
        ->and($stats[2])->toBe(2)
        ->and($stats[6])->toBe(1)
        ->and($stats[9])->toBe(0);
});

it('É possível calcular as frequências usando apenas os últimos sorteios.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 6, 7, 8]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [9, 10, 11, 12, 13]);

    $stats = app(StatisticsService::class)->numberFrequencies($quina, lastDraws: 2);

    expect($stats[1])->toBe(1)
        ->and($stats[2])->toBe(1)
        ->and($stats[3])->toBe(0)
        ->and($stats[9])->toBe(1)
        ->and($stats[13])->toBe(1);
});

it('Retorna todos os números válidos da modalidade, mesmo quando a frequência é zero.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [10, 20, 30, 40, 50]);

    $stats = app(StatisticsService::class)->numberFrequencies($quina);

    expect($stats)->toHaveCount(80)
        ->and($stats[1])->toBe(0)
        ->and($stats[10])->toBe(1)
        ->and($stats[80])->toBe(0);
});