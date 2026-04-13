<?php

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('soft deletes one private combination history item from the modality', function () {
    $user = User::factory()->create();
    $modality = LotteryModality::factory()->quina()->create();

    $item = CombinationHistory::factory()->create([
        'user_id' => $user->id,
        'lottery_modality_id' => $modality->id,
    ]);

    $response = $this->actingAs($user)->delete("/lottery/modalities/{$modality->id}/combination-history/{$item->id}");

    $response->assertRedirect();
    $this->assertSoftDeleted('combination_histories', ['id' => $item->id]);
});

it('does not delete combination history item from another modality', function () {
    $user = User::factory()->create();
    $quina = LotteryModality::factory()->quina()->create();
    $otherModality = LotteryModality::factory()->create();

    $item = CombinationHistory::factory()->create([
        'user_id' => $user->id,
        'lottery_modality_id' => $otherModality->id,
    ]);

    $response = $this->actingAs($user)->delete("/lottery/modalities/{$quina->id}/combination-history/{$item->id}");

    $response->assertNotFound();
    $this->assertDatabaseHas('combination_histories', ['id' => $item->id, 'deleted_at' => null]);
});

it('soft deletes all private combination history items from the modality', function () {
    $user = User::factory()->create();
    $modality = LotteryModality::factory()->quina()->create();
    $otherModality = LotteryModality::factory()->create();

    $items = CombinationHistory::factory()->count(3)->create([
        'user_id' => $user->id,
        'lottery_modality_id' => $modality->id,
    ]);

    $otherItem = CombinationHistory::factory()->create([
        'user_id' => $user->id,
        'lottery_modality_id' => $otherModality->id,
    ]);

    $response = $this->actingAs($user)->delete("/lottery/modalities/{$modality->id}/combination-history");

    $response->assertRedirect();

    foreach ($items as $item) {
        $this->assertSoftDeleted('combination_histories', ['id' => $item->id]);
    }

    $this->assertDatabaseHas('combination_histories', ['id' => $otherItem->id, 'deleted_at' => null]);
});
