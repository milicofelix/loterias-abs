<?php

use App\Models\CombinationHistory;
use App\Models\Draw;
use App\Models\DrawNumber;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('pode vincular uma combinação ao concurso atual', function () {
    $user = User::factory()->create();
    $quina = LotteryModality::factory()->quina()->create();

    Draw::factory()->create([
        'lottery_modality_id' => $quina->id,
        'contest_number' => 6999,
    ]);

    $history = CombinationHistory::factory()->forUser($user)->create([
        'lottery_modality_id' => $quina->id,
        'numbers' => [22, 49, 51, 56, 71],
    ]);

    $response = $this->actingAs($user)
        ->postJson("/lottery/modalities/{$quina->id}/combination-history/{$history->id}/register-bet");

    $response
        ->assertOk()
        ->assertJsonPath('item.bet_contest_number', 6999);

    expect($history->fresh()->bet_contest_number)->toBe(6999)
        ->and($history->fresh()->bet_registered_at)->not->toBeNull();
});

it('pode conferir a aposta com o resultado oficial', function () {
    $user = User::factory()->create();
    $quina = LotteryModality::factory()->quina()->create();

    $draw = Draw::factory()->create([
        'lottery_modality_id' => $quina->id,
        'contest_number' => 6999,
    ]);

    foreach ([19, 46, 49, 70, 79] as $number) {
        DrawNumber::factory()->create([
            'draw_id' => $draw->id,
            'number' => $number,
        ]);
    }

    $history = CombinationHistory::factory()->forUser($user)->create([
        'lottery_modality_id' => $quina->id,
        'numbers' => [22, 49, 51, 56, 71],
        'bet_contest_number' => 6999,
        'bet_registered_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get("/lottery/modalities/{$quina->id}/combination-history/{$history->id}/check-bet");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/CheckBet')
        ->where('historyItem.bet_contest_number', 6999)
        ->where('officialResult.contest_number', 6999)
        ->where('checkResult.hit_count', 1)
    );
});
