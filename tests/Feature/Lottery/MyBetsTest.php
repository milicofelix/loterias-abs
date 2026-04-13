<?php

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('lista apenas as apostas do usuário autenticado dentro do período', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $quina = LotteryModality::factory()->quina()->create();

    CombinationHistory::factory()->forUser($user)->create([
        'lottery_modality_id' => $quina->id,
        'bet_contest_number' => 6999,
        'bet_registered_at' => now()->subDays(5),
    ]);

    CombinationHistory::factory()->forUser($user)->create([
        'lottery_modality_id' => $quina->id,
        'bet_contest_number' => 6998,
        'bet_registered_at' => now()->subDays(40),
    ]);

    CombinationHistory::factory()->forUser($otherUser)->create([
        'lottery_modality_id' => $quina->id,
        'bet_contest_number' => 6997,
        'bet_registered_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($user)->get('/lottery/my-bets?days=30');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/MyBets')
        ->has('items.data', 1)
        ->where('filters.days', 30)
    );
});
