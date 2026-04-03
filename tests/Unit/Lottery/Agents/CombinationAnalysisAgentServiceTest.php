<?php

use App\Models\LotteryModality;
use App\Services\Lottery\Agents\CombinationAnalysisAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesDraws;

uses(RefreshDatabase::class, CreatesDraws::class);

it('retorna a estrutura do agente junto com a análise base', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);
    $this->createDrawWithNumbers($quina, 3, '2026-01-03', [11, 12, 13, 14, 15]);

    $result = app(CombinationAnalysisAgentService::class)->analyze(
        $quina,
        [1, 2, 20, 30, 40]
    );

    expect($result)
        ->toHaveKey('sum')
        ->toHaveKey('average_frequency')
        ->toHaveKey('narrative')
        ->toHaveKey('score')
        ->toHaveKey('agent')
        ->and($result['agent'])->toBeArray()
        ->and($result['agent'])->toHaveKeys([
            'name',
            'version',
            'summary',
            'strengths',
            'warnings',
        ]);
});

it('retorna summary textual do agente', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);
    $this->createDrawWithNumbers($quina, 2, '2026-01-02', [6, 7, 8, 9, 10]);

    $result = app(CombinationAnalysisAgentService::class)->analyze(
        $quina,
        [1, 2, 20, 30, 40]
    );

    expect($result['agent']['summary'])
        ->toBeString()
        ->not->toBeEmpty();
});

it('retorna listas de strengths e warnings mesmo quando vazias', function () {
    $quina = LotteryModality::factory()->quina()->create();

    $this->createDrawWithNumbers($quina, 1, '2026-01-01', [1, 2, 3, 4, 5]);

    $result = app(CombinationAnalysisAgentService::class)->analyze(
        $quina,
        [1, 2, 10, 20, 30]
    );

    expect($result['agent']['strengths'])->toBeArray()
        ->and($result['agent']['warnings'])->toBeArray();
});