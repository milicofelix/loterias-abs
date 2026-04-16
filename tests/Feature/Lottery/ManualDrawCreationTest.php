<?php

use App\Models\Draw;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create an individual draw manually from dashboard action', function () {
    $this->actingAs(User::factory()->create());

    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->from(route('lottery.modalities.show', $quina))
        ->post(route('lottery.modalities.draws.store', $quina), [
            'contest_number' => 7001,
            'draw_date' => '2026-04-15',
            'numbers' => [1, 12, 23, 34, 45],
            'observation' => 'Cadastro manual de teste',
        ]);

    $response->assertRedirect(route('lottery.modalities.show', $quina))
        ->assertSessionHas('success', 'Resultado do concurso 7001 cadastrado com sucesso.');

    $draw = Draw::query()->where('lottery_modality_id', $quina->id)->where('contest_number', 7001)->firstOrFail();

    expect($draw->draw_date->toDateString())->toBe('2026-04-15');
    expect($draw->numbers()->pluck('number')->sort()->values()->all())->toBe([1, 12, 23, 34, 45]);
    expect($draw->metadata['source'] ?? null)->toBe('manual_dashboard');
    expect($draw->metadata['created_manually'] ?? null)->toBeTrue();
    expect($draw->metadata['observation'] ?? null)->toBe('Cadastro manual de teste');
});

it('blocks duplicate contest numbers in manual draw creation', function () {
    $this->actingAs(User::factory()->create());

    $quina = LotteryModality::factory()->quina()->create();

    Draw::factory()->create([
        'lottery_modality_id' => $quina->id,
        'contest_number' => 7001,
        'draw_date' => '2026-04-10',
    ]);

    $response = $this->from(route('lottery.modalities.show', $quina))
        ->post(route('lottery.modalities.draws.store', $quina), [
            'contest_number' => 7001,
            'draw_date' => '2026-04-15',
            'numbers' => [1, 12, 23, 34, 45],
        ]);

    $response->assertRedirect(route('lottery.modalities.show', $quina))
        ->assertSessionHas('error', 'Já existe um resultado cadastrado para o concurso 7001.');

    expect(Draw::query()->where('lottery_modality_id', $quina->id)->count())->toBe(1);
});

it('validates repeated numbers when creating a manual draw', function () {
    $this->actingAs(User::factory()->create());

    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->from(route('lottery.modalities.show', $quina))
        ->post(route('lottery.modalities.draws.store', $quina), [
            'contest_number' => 7002,
            'draw_date' => '2026-04-15',
            'numbers' => [1, 12, 12, 34, 45],
        ]);

    $response->assertRedirect(route('lottery.modalities.show', $quina))
        ->assertSessionHas('error', 'O resultado informado possui números repetidos.');

    expect(Draw::query()->where('lottery_modality_id', $quina->id)->count())->toBe(0);
});


it('redireciona visitante para o login ao tentar cadastrar resultado manual', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $response = $this->post(route('lottery.modalities.draws.store', $quina), [
        'contest_number' => 7003,
        'draw_date' => '2026-04-15',
        'numbers' => [1, 12, 23, 34, 45],
    ]);

    $response->assertRedirect(route('login'));
    expect(Draw::query()->where('lottery_modality_id', $quina->id)->count())->toBe(0);
});
