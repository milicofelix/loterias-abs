<?php

use App\Models\LotteryModality;
use App\Services\Lottery\DelayAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('Calcula o atraso para cada número com base no sorteio mais recente.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [1, 11, 12, 13, 14]);

    $delays = app(DelayAnalysisService::class)->numberDelays($quina);

    expect($delays[1])->toBe(0)
        ->and($delays[11])->toBe(0)
        ->and($delays[6])->toBe(1)
        ->and($delays[2])->toBe(2)
        ->and($delays[80])->toBe(3);
});

it('Não mistura atrasos de outras modalidades.', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $mega = LotteryModality::factory()->megaSena()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);

    $this->createDrawWithNumbers($mega, 1, '2026-01-03', [1, 2, 3, 4, 5, 6]);

    $delays = app(DelayAnalysisService::class)->numberDelays($quina);

    expect($delays[6])->toBe(0)
        ->and($delays[1])->toBe(1)
        ->and($delays[11])->toBe(2);
});

it('Retorna todos os números válidos, mesmo quando nenhum foi sorteado ainda.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $delays = app(DelayAnalysisService::class)->numberDelays($quina);

    expect($delays)->toHaveCount(80)
        ->and($delays[1])->toBe(0)
        ->and($delays[80])->toBe(0);
});

it('pode limitar a análise de atraso aos últimos sorteios', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [11, 12, 13, 14, 15]);

    $delays = app(DelayAnalysisService::class)->numberDelays($quina, lastDraws: 2);

    expect($delays[11])->toBe(0)
        ->and($delays[6])->toBe(1)
        ->and($delays[1])->toBe(2)
        ->and($delays[80])->toBe(2);
});