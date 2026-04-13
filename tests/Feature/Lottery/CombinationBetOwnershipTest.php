<?php

use App\Models\CombinationHistory;
use App\Models\Draw;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('nao permite conferir ou vincular aposta de outro usuario', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $modality = LotteryModality::factory()->create();

    Draw::factory()->create([
        'lottery_modality_id' => $modality->id,
        'contest_number' => 6999,
    ]);

    $history = CombinationHistory::factory()->create([
        'user_id' => $owner->id,
        'lottery_modality_id' => $modality->id,
        'bet_contest_number' => 6999,
        'bet_registered_at' => now(),
    ]);

    $this->actingAs($intruder)
        ->postJson(route('lottery.combination-history.register-bet', ['modality' => $modality, 'item' => $history]))
        ->assertNotFound();

    $this->actingAs($intruder)
        ->get(route('lottery.combination-history.check-bet', ['modality' => $modality, 'item' => $history]))
        ->assertNotFound();
});
