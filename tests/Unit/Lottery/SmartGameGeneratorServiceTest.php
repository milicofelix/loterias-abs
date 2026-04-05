<?php

use App\Models\Draw;
use App\Models\DrawNumber;
use App\Models\LotteryModality;
use App\Services\Lottery\SmartGameGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedQuinaHistory(LotteryModality $quina): void
{
    $baseDraws = [
        [5, 11, 22, 49, 71],
        [7, 18, 22, 53, 71],
        [9, 11, 29, 49, 67],
        [5, 22, 41, 53, 79],
        [11, 22, 41, 49, 63],
        [22, 29, 41, 53, 71],
        [5, 18, 41, 63, 79],
        [11, 22, 49, 53, 67],
        [18, 29, 41, 63, 71],
        [5, 22, 53, 67, 79],
        [11, 29, 41, 49, 71],
        [18, 22, 53, 63, 79],
    ];

    foreach ($baseDraws as $index => $numbers) {
        $draw = Draw::create([
            'lottery_modality_id' => $quina->id,
            'contest_number' => $index + 1,
            'draw_date' => now()->subDays(count($baseDraws) - $index)->toDateString(),
            'metadata' => null,
        ]);

        foreach ($numbers as $number) {
            DrawNumber::create([
                'draw_id' => $draw->id,
                'number' => $number,
            ]);
        }
    }
}

it('gera jogos inteligentes equilibrados com estrutura esperada', function () {
    $quina = LotteryModality::factory()->quina()->create();
    seedQuinaHistory($quina);

    $games = app(SmartGameGeneratorService::class)->generate($quina, [
        'strategy' => 'balanced',
        'games' => 4,
        'candidate_pool' => 300,
    ]);

    expect($games)->toHaveCount(4);

    foreach ($games as $game) {
        expect($game)
            ->toHaveKeys([
                'numbers',
                'strategy',
                'weighted_score',
                'historical_alignment_score',
                'average_frequency',
                'average_delay',
                'top_frequency_hits',
                'top_delay_hits',
                'classification',
                'profile',
                'reason',
                'pattern',
                'historical_comparison',
            ])
            ->and($game['numbers'])->toHaveCount(5)
            ->and($game['numbers'])->toBe(array_values(array_unique($game['numbers'])))
            ->and($game['numbers'])->toBeSorted()
            ->and($game['strategy'])->toBe('balanced')
            ->and($game['weighted_score'])->toBeInt();
    }
});

it('na estratégia hot privilegia presença de números frequentes', function () {
    $quina = LotteryModality::factory()->quina()->create();
    seedQuinaHistory($quina);

    $games = app(SmartGameGeneratorService::class)->generate($quina, [
        'strategy' => 'hot',
        'games' => 3,
        'candidate_pool' => 300,
    ]);

    expect($games)->toHaveCount(3);

    foreach ($games as $game) {
        expect($game['strategy'])->toBe('hot')
            ->and($game['top_frequency_hits'])->toBeGreaterThanOrEqual(1);
    }
});

it('não retorna jogos duplicados na mesma execução', function () {
    $quina = LotteryModality::factory()->quina()->create();
    seedQuinaHistory($quina);

    $games = app(SmartGameGeneratorService::class)->generate($quina, [
        'strategy' => 'balanced',
        'games' => 5,
        'candidate_pool' => 400,
    ]);

    $keys = array_map(fn (array $game) => implode('-', $game['numbers']), $games);

    expect($keys)->toHaveCount(count(array_unique($keys)));
});
