<?php

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('Armazena um registro de histórico de combinações gerado.', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $history = CombinationHistory::create([
        'lottery_modality_id' => $quina->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'generated',
        'analysis_snapshot' => [
            'sum' => 15,
            'even_count' => 2,
        ],
    ]);

    expect($history->numbers)->toBe([1, 2, 3, 4, 5])
        ->and($history->source)->toBe('generated')
        ->and($history->analysis_snapshot['sum'])->toBe(15);
});

it('can list recent history records for a modality', function () {
    $quina = LotteryModality::factory()->quina()->create();

    CombinationHistory::create([
        'lottery_modality_id' => $quina->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => ['sum' => 15],
    ]);

    CombinationHistory::create([
        'lottery_modality_id' => $quina->id,
        'numbers' => [10, 20, 30, 40, 50],
        'source' => 'generated',
        'analysis_snapshot' => ['sum' => 150],
    ]);

    $items = CombinationHistory::where('lottery_modality_id', $quina->id)
        ->latest()
        ->get();

    expect($items)->toHaveCount(2);
});