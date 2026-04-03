<?php

use App\Services\Lottery\CombinationNarrativeService;

it('Constrói uma narrativa para uma combinação abaixo da soma histórica e dentro de um padrão uniforme comum.', function () {
    $analysis = [
        'sum' => 120,
        'range' => 40,
        'even_count' => 2,
        'consecutive_count' => 1,
        'historical_averages' => [
            'sum' => 150,
            'even_count' => 2.4,
            'range' => 45,
        ],
        'historical_comparison' => [
            'sum_diff' => -30,
            'even_count_diff' => -0.4,
            'range_diff' => -5,
        ],
        'most_common_even_count' => 2,
        'within_common_even_pattern' => true,
        'top_frequency_hits' => 2,
        'top_delay_hits' => 1,
    ];

    $result = app(CombinationNarrativeService::class)->build($analysis);

    expect($result)->toHaveKeys(['headline', 'insights'])
        ->and($result['headline'])->toBeString()
        ->and($result['insights'])->toBeArray()
        ->and($result['insights'])->not->toBeEmpty();
});

it('menciona quando a contagem par está fora do padrão mais comum', function () {
    $analysis = [
        'sum' => 180,
        'range' => 55,
        'even_count' => 4,
        'consecutive_count' => 0,
        'historical_averages' => [
            'sum' => 150,
            'even_count' => 2.4,
            'range' => 45,
        ],
        'historical_comparison' => [
            'sum_diff' => 30,
            'even_count_diff' => 1.6,
            'range_diff' => 10,
        ],
        'most_common_even_count' => 2,
        'within_common_even_pattern' => false,
        'top_frequency_hits' => 1,
        'top_delay_hits' => 3,
    ];

    $result = app(CombinationNarrativeService::class)->build($analysis);

    expect(implode(' ', $result['insights']))->toContain('pares')
        ->and(implode(' ', $result['insights']))->toContain('mais comum');
});

it('menciona sequências consecutivas quando presentes', function () {
    $analysis = [
        'sum' => 140,
        'range' => 50,
        'even_count' => 2,
        'consecutive_count' => 2,
        'historical_averages' => [
            'sum' => 145,
            'even_count' => 2.2,
            'range' => 49,
        ],
        'historical_comparison' => [
            'sum_diff' => -5,
            'even_count_diff' => -0.2,
            'range_diff' => 1,
        ],
        'most_common_even_count' => 2,
        'within_common_even_pattern' => true,
        'top_frequency_hits' => 3,
        'top_delay_hits' => 0,
    ];

    $result = app(CombinationNarrativeService::class)->build($analysis);

    expect(implode(' ', $result['insights']))->toContain('sequência');
});