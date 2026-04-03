<?php

use App\Models\LotteryModality;
use App\Services\Lottery\Agents\ProfileComparisonAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('retorna a estrutura de comparação de perfil com uma combinação', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);

    $result = app(ProfileComparisonAgentService::class)->compare(
        $quina,
        [1, 2, 20, 30, 40]
    );

    expect($result)->toHaveKeys([
        'name',
        'version',
        'left',
        'right',
        'summary',
        'highlights',
        'winner',
    ]);

    expect($result['name'])->toBe('profile-comparator');
    expect($result['left'])->toBeArray();
    expect($result['right'])->toBeNull();
    expect($result['summary'])->toBeString()->not->toBeEmpty();
    expect($result['highlights'])->toBeArray()->not->toBeEmpty();
});

it('compara duas combinações e define vencedor ou empate', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [10, 20, 30, 40, 50]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [11, 22, 33, 44, 55]);

    $result = app(ProfileComparisonAgentService::class)->compare(
        $quina,
        [1, 2, 20, 30, 40],
        [10, 11, 12, 13, 14]
    );

    expect($result['right'])->toBeArray();
    expect($result['winner'])->toBeArray();
    expect($result['winner'])->toHaveKeys(['side', 'reason']);
});

it('anexa profile_comparison na análise principal da combinação', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);

    $result = app(\App\Services\Lottery\Agents\CombinationAnalysisAgentService::class)
        ->analyze($quina, [1, 2, 20, 30, 40]);

    expect($result)
        ->toHaveKey('profile_comparison')
        ->and($result['profile_comparison'])->toHaveKeys([
            'name',
            'version',
            'left',
            'right',
            'summary',
            'highlights',
            'winner',
        ]);
});