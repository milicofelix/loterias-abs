<?php

use App\Models\LotteryModality;
use App\Services\Lottery\CombinationInsightsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('builds statistical insights for a valid quina combination', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 10, 20]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 4, 11, 21]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [1, 5, 6, 12, 22]);

    $insights = app(CombinationInsightsService::class)->analyze($quina, [1, 2, 3, 4, 5]);

    expect($insights['numbers'])->toBe([1, 2, 3, 4, 5])
        ->and($insights['even_count'])->toBe(2)
        ->and($insights['odd_count'])->toBe(3)
        ->and($insights['sum'])->toBe(15)
        ->and($insights['average_frequency'])->toBeFloat()
        ->and($insights['average_delay'])->toBeFloat()
        ->and($insights['top_frequency_hits'])->toBeInt()
        ->and($insights['top_delay_hits'])->toBeInt()
        ->and($insights['range_distribution'])->toBeArray()
        ->and($insights['consecutive_groups'])->toBeArray()
        ->and($insights['score'])->toBeArray()
        ->and($insights['score'])->toHaveKeys(['value', 'label', 'profile', 'breakdown']);
});

it('calculates average frequency based on selected numbers', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 6, 7, 8]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [1, 9, 10, 11, 12]);

    $insights = app(CombinationInsightsService::class)->analyze($quina, [1, 2, 3, 4, 5]);

    // frequências: 1=>3, 2=>2, 3=>1, 4=>1, 5=>1 => média = 8/5 = 1.6
    expect($insights['average_frequency'])->toBe(1.6);
});

it('calculates average delay based on selected numbers', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [1, 11, 12, 13, 14]);

    $insights = app(CombinationInsightsService::class)->analyze($quina, [1, 2, 6, 15, 16]);

    // delays: 1=>0, 2=>2, 6=>1, 15=>3, 16=>3 => média = 9/5 = 1.8
    expect($insights['average_delay'])->toBe(1.8);
});

it('counts how many selected numbers are inside top frequent numbers', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 6, 7, 8]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [1, 2, 9, 10, 11]);

    $insights = app(CombinationInsightsService::class)->analyze($quina, [1, 2, 50, 51, 52], topLimit: 3);

    expect($insights['top_frequency_hits'])->toBeGreaterThanOrEqual(2);
});

it('counts how many selected numbers are inside top delayed numbers', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [1, 2, 6, 7, 8]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [1, 2, 9, 10, 11]);

    $insights = app(CombinationInsightsService::class)->analyze($quina, [50, 51, 52, 53, 54], topLimit: 80);

    expect($insights['top_delay_hits'])->toBe(5);
});