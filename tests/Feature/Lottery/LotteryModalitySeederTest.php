<?php

use App\Models\LotteryModality;
use Database\Seeders\LotteryModalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds the main lottery modalities', function () {
    $this->seed(LotteryModalitySeeder::class);

    expect(LotteryModality::where('code', 'quina')->exists())->toBeTrue()
        ->and(LotteryModality::where('code', 'mega_sena')->exists())->toBeTrue()
        ->and(LotteryModality::where('code', 'lotofacil')->exists())->toBeTrue()
        ->and(LotteryModality::where('code', 'lotomania')->exists())->toBeTrue();
});

it('stores quina with correct rules', function () {
    $this->seed(LotteryModalitySeeder::class);

    $quina = LotteryModality::where('code', 'quina')->firstOrFail();

    expect($quina->min_number)->toBe(1)
        ->and($quina->max_number)->toBe(80)
        ->and($quina->draw_count)->toBe(5)
        ->and($quina->bet_min_count)->toBe(5)
        ->and($quina->allows_repetition)->toBeFalse()
        ->and($quina->order_matters)->toBeFalse();
});