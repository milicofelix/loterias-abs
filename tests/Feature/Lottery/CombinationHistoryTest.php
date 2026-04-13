<?php

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('armazena um registro privado de histórico de combinações', function () {
    $user = User::factory()->create();
    $quina = LotteryModality::factory()->quina()->create();

    $history = CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'generated',
        'analysis_snapshot' => [
            'sum' => 15,
            'even_count' => 2,
        ],
    ]);

    expect($history->numbers)->toBe([1, 2, 3, 4, 5])
        ->and($history->user_id)->toBe($user->id)
        ->and($history->source)->toBe('generated')
        ->and($history->analysis_snapshot['sum'])->toBe(15);
});

it('can list recent private history records for a modality', function () {
    $user = User::factory()->create();
    $quina = LotteryModality::factory()->quina()->create();

    CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => ['sum' => 15],
    ]);

    CombinationHistory::create([
        'user_id' => $user->id,
        'lottery_modality_id' => $quina->id,
        'numbers' => [10, 20, 30, 40, 50],
        'source' => 'generated',
        'analysis_snapshot' => ['sum' => 150],
    ]);

    $items = CombinationHistory::where('user_id', $user->id)
        ->where('lottery_modality_id', $quina->id)
        ->latest()
        ->get();

    expect($items)->toHaveCount(2);
});
