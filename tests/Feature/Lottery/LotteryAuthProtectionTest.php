<?php

use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mantem resultados publicos e protege area privada', function () {
    $modality = LotteryModality::factory()->create();

    $this->get(route('lottery.modalities.index'))->assertOk();
    $this->get(route('lottery.modalities.show', $modality))->assertOk();
    $this->get(route('lottery.modalities.history', $modality))->assertOk();

    $this->get(route('lottery.modalities.play', $modality))->assertRedirect('/login');
    $this->get(route('lottery.combination-history', $modality))->assertRedirect('/login');
    $this->get(route('lottery.bets', $modality))->assertRedirect('/login');
});

it('lista apenas apostas do usuario autenticado no periodo filtrado', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $modality = LotteryModality::factory()->create();

    $mineRecent = \App\Models\CombinationHistory::factory()->create([
        'user_id' => $user->id,
        'lottery_modality_id' => $modality->id,
        'bet_contest_number' => 100,
        'bet_registered_at' => now()->subDays(5),
    ]);

    \App\Models\CombinationHistory::factory()->create([
        'user_id' => $user->id,
        'lottery_modality_id' => $modality->id,
        'bet_contest_number' => 90,
        'bet_registered_at' => now()->subDays(40),
    ]);

    \App\Models\CombinationHistory::factory()->create([
        'user_id' => $otherUser->id,
        'lottery_modality_id' => $modality->id,
        'bet_contest_number' => 101,
        'bet_registered_at' => now()->subDays(3),
    ]);

    $this->actingAs($user)
        ->get(route('lottery.bets', ['modality' => $modality, 'days' => 7]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Lottery/MyBets')
            ->has('items.data', 1)
            ->where('items.data.0.id', $mineRecent->id)
            ->where('filters.days', 7)
        );
});
