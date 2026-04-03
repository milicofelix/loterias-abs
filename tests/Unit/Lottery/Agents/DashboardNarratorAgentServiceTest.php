<?php

use App\Models\LotteryModality;
use App\Services\Lottery\Agents\DashboardNarratorAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('retorna a estrutura do agente narrador do dashboard', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(DashboardNarratorAgentService::class)->narrate($modality, [
        'window' => 20,
        'top_recent_numbers' => [12, 37, 44, 58, 71],
        'top_delayed_numbers' => [5, 29, 61, 77, 80],
        'recent_average_sum' => 198.4,
        'historical_average_sum' => 191.2,
    ]);

    expect($result)
        ->toHaveKeys([
            'name',
            'version',
            'headline',
            'highlights',
            'summary',
        ]);

    expect($result['name'])->toBe('dashboard-narrator');
    expect($result['version'])->toBe(1);
    expect($result['headline'])->toBeString()->not->toBeEmpty();
    expect($result['summary'])->toBeString()->not->toBeEmpty();
    expect($result['highlights'])->toBeArray()->not->toBeEmpty();
});

it('funciona mesmo com contexto mínimo', function () {
    $modality = LotteryModality::factory()->quina()->create();

    $result = app(DashboardNarratorAgentService::class)->narrate($modality, [
        'window' => 10,
    ]);

    expect($result['headline'])->toBeString()->not->toBeEmpty();
    expect($result['summary'])->toBeString()->not->toBeEmpty();
    expect($result['highlights'])->toBeArray();
});