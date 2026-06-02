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

it('filtra apostas por status de conferência', function () {
    $user = User::factory()->create();
    $quina = LotteryModality::factory()->quina()->create();

    CombinationHistory::factory()->forUser($user)->create([
        'lottery_modality_id' => $quina->id,
        'bet_contest_number' => 7001,
        'bet_registered_at' => now()->subDays(1),
    ]);

    CombinationHistory::factory()->forUser($user)->create([
        'lottery_modality_id' => $quina->id,
        'bet_contest_number' => 7002,
        'bet_registered_at' => now()->subDays(1),
        'bet_checked_at' => now(),
        'bet_result_snapshot' => [
            'check_result' => [
                'hit_count' => 5,
                'is_prized' => true,
                'prize_label' => 'Quina',
            ],
        ],
    ]);

    CombinationHistory::factory()->forUser($user)->create([
        'lottery_modality_id' => $quina->id,
        'bet_contest_number' => 7003,
        'bet_registered_at' => now()->subDays(1),
        'bet_checked_at' => now(),
        'bet_result_snapshot' => [
            'check_result' => [
                'hit_count' => 1,
                'is_prized' => false,
                'prize_label' => null,
            ],
        ],
    ]);

    $response = $this->actingAs($user)->get('/lottery/my-bets?days=30&status=prized');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/MyBets')
        ->has('items.data', 1)
        ->where('items.data.0.bet_status', 'prized')
        ->where('items.data.0.bet_result_snapshot.check_result.hit_count', 5)
        ->where('filters.status', 'prized')
        ->where('summary.total', 3)
        ->where('summary.pending', 1)
        ->where('summary.checked', 2)
        ->where('summary.prized', 1)
        ->where('summary.not_prized', 1)
    );
});
