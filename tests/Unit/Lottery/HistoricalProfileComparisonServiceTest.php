<?php

use App\Models\LotteryModality;
use App\Services\Lottery\HistoricalProfileComparisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('calculates historical averages for the modality', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);     // sum 15, even 2, range 4
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [10, 20, 30, 40, 50]); // sum 150, even 5, range 40

    $result = app(HistoricalProfileComparisonService::class)->compare($quina, [1, 2, 3, 4, 5]);

    expect($result['historical_averages']['sum'])->toBe(82.5)
        ->and($result['historical_averages']['even_count'])->toBe(3.5)
        ->and($result['historical_averages']['range'])->toBe(22.0);
});

it('calculates differences against the provided combination', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);     // sum 15
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [10, 20, 30, 40, 50]); // sum 150

    $result = app(HistoricalProfileComparisonService::class)->compare($quina, [1, 2, 3, 4, 5]);

    expect($result['comparison']['sum_diff'])->toBe(-67.5)
        ->and($result['comparison']['even_count_diff'])->toBe(-1.5)
        ->and($result['comparison']['range_diff'])->toBe(-18.0);
});

it('detects when even count matches the most common historical even pattern', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);     // even 2
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 11]);    // even 2
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [10, 12, 14, 15, 17]); // even 3

    $result = app(HistoricalProfileComparisonService::class)->compare($quina, [21, 22, 23, 24, 25]); // even 2

    expect($result['most_common_even_count'])->toBe(2)
        ->and($result['within_common_even_pattern'])->toBeTrue();
});

it('can limit comparison to the last draws', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);       // old
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [10, 20, 30, 40, 50]);  // recent
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [11, 21, 31, 41, 51]);  // recent

    $result = app(HistoricalProfileComparisonService::class)->compare(
        $quina,
        [10, 20, 30, 40, 50],
        lastDraws: 2
    );

    expect($result['historical_averages']['sum'])->toBe(152.5)
        ->and($result['historical_averages']['even_count'])->toBe(2.5);
});