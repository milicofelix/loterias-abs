<?php

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('soft deletes one combination history item from the modality', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $item = CombinationHistory::factory()->create([
        'lottery_modality_id' => $modality->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => [
            'sum' => 15,
        ],
    ]);

    $response = $this->delete(
        "/lottery/modalities/{$modality->id}/combination-history/{$item->id}"
    );

    $response->assertRedirect();

    $this->assertSoftDeleted('combination_histories', [
        'id' => $item->id,
    ]);
});

it('does not delete combination history item from another modality', function () {
    $quina = LotteryModality::factory()->quina()->create();
    $otherModality = LotteryModality::factory()->create();

    $item = CombinationHistory::factory()->create([
        'lottery_modality_id' => $otherModality->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => [
            'sum' => 15,
        ],
    ]);

    $response = $this->delete(
        "/lottery/modalities/{$quina->id}/combination-history/{$item->id}"
    );

    $response->assertNotFound();

    $this->assertDatabaseHas('combination_histories', [
        'id' => $item->id,
        'deleted_at' => null,
    ]);
});

it('soft deletes all combination history items from the modality', function () {
    $modality = LotteryModality::factory()->quina()->create();
    $otherModality = LotteryModality::factory()->create();

    $items = CombinationHistory::factory()->count(3)->create([
        'lottery_modality_id' => $modality->id,
        'numbers' => [1, 2, 3, 4, 5],
        'source' => 'manual',
        'analysis_snapshot' => [
            'sum' => 15,
        ],
    ]);

    $otherItem = CombinationHistory::factory()->create([
        'lottery_modality_id' => $otherModality->id,
        'numbers' => [10, 20, 30, 40, 50],
        'source' => 'generated',
        'analysis_snapshot' => [
            'sum' => 150,
        ],
    ]);

    $response = $this->delete(
        "/lottery/modalities/{$modality->id}/combination-history"
    );

    $response->assertRedirect();

    foreach ($items as $item) {
        $this->assertSoftDeleted('combination_histories', [
            'id' => $item->id,
        ]);
    }

    $this->assertDatabaseHas('combination_histories', [
        'id' => $otherItem->id,
        'deleted_at' => null,
    ]);
});