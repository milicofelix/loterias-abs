<?php

use App\Models\LotteryModality;
use App\Services\Lottery\PatternAnalyzerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('analyzes a valid quina combination', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $analysis = app(PatternAnalyzerService::class)->analyze($quina, [25, 2, 3, 40, 41]);

    expect($analysis['sorted_numbers'])->toBe([2, 3, 25, 40, 41])
        ->and($analysis['even_count'])->toBe(2)
        ->and($analysis['odd_count'])->toBe(3)
        ->and($analysis['sum'])->toBe(111)
        ->and($analysis['min'])->toBe(2)
        ->and($analysis['max'])->toBe(41)
        ->and($analysis['range'])->toBe(39);
});

it('calculates distribution by ranges for quina', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $analysis = app(PatternAnalyzerService::class)->analyze($quina, [1, 16, 32, 48, 80]);

    expect($analysis['range_distribution'])->toBe([
        '1-16' => 2,
        '17-32' => 1,
        '33-48' => 1,
        '49-64' => 0,
        '65-80' => 1,
    ]);
});

it('detects consecutive groups', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $analysis = app(PatternAnalyzerService::class)->analyze($quina, [5, 6, 7, 20, 21]);

    expect($analysis['consecutive_groups'])->toBe([
        [5, 6, 7],
        [20, 21],
    ])->and($analysis['consecutive_count'])->toBe(2);
});

it('returns no consecutive groups when there are no sequences', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $analysis = app(PatternAnalyzerService::class)->analyze($quina, [1, 10, 20, 30, 40]);

    expect($analysis['consecutive_groups'])->toBe([])
        ->and($analysis['consecutive_count'])->toBe(0);
});

it('works with mega sena ranges based on modality limits', function () {
    $mega = LotteryModality::factory()->megaSena()->create();

    $analysis = app(PatternAnalyzerService::class)->analyze($mega, [1, 12, 24, 36, 48, 60]);

    expect($analysis['range_distribution'])->toBe([
        '1-12' => 2,
        '13-24' => 1,
        '25-36' => 1,
        '37-48' => 1,
        '49-60' => 1,
    ]);
});