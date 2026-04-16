<?php

use App\Models\LotteryModality;
use App\Services\Lottery\HistoricalPrizeSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('counts historical prize occurrences for the informed sequence', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 3, 10, 11]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [1, 2, 20, 21, 22]);
    $this->createDrawWithNumbers($quina, 4, '2026-01-04', [30, 31, 32, 33, 34]);

    $summary = app(HistoricalPrizeSummaryService::class)->analyze($quina, [1, 2, 3, 40, 41]);

    expect($summary['best_hit'])->toBe(3)
        ->and($summary['ever_prized'])->toBeTrue()
        ->and($summary['contest_count_checked'])->toBe(4)
        ->and($summary['total_prized_occurrences'])->toBe(3)
        ->and($summary['hit_counts']['2'])->toBe(1)
        ->and($summary['hit_counts']['3'])->toBe(2)
        ->and($summary['hit_counts']['4'])->toBe(0)
        ->and($summary['hit_counts']['5'])->toBe(0)
        ->and($summary['last_occurrence']['contest_number'])->toBe(3);
});

it('returns an empty historical prize summary when the sequence never prizes', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);

    $summary = app(HistoricalPrizeSummaryService::class)->analyze($quina, [20, 30, 40, 50, 60]);

    expect($summary['best_hit'])->toBe(0)
        ->and($summary['ever_prized'])->toBeFalse()
        ->and($summary['total_prized_occurrences'])->toBe(0)
        ->and($summary['last_occurrence'])->toBeNull();
});
