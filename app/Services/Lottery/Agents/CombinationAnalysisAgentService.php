<?php

namespace App\Services\Lottery\Agents;

use App\Models\LotteryModality;
use App\Services\Lottery\CombinationInsightsService;
use App\Services\Lottery\Agents\ProfileComparisonAgentService;

class CombinationAnalysisAgentService
{
    public function __construct(
        protected CombinationInsightsService $combinationInsightsService,
        protected ProfileComparisonAgentService $profileComparisonAgentService
    ) {
    }

    /**
     * @param array<int, int> $numbers
     * @return array<string, mixed>
     */
    // public function analyze(LotteryModality $modality, array $numbers): array
    // {
    //     $analysis = $this->combinationInsightsService->analyze($modality, $numbers);

    //     return array_merge($analysis, [
    //         'agent' => [
    //             'name' => 'combination-analyst',
    //             'version' => 1,
    //             'summary' => $this->buildSummary($analysis),
    //             'strengths' => $this->buildStrengths($analysis),
    //             'warnings' => $this->buildWarnings($analysis),
    //         ],
    //     ]);
    // }

    public function analyze(LotteryModality $modality, array $numbers): array
    {
        $analysis = $this->combinationInsightsService->analyze($modality, $numbers);
        $profileComparison = $this->profileComparisonAgentService->compare($modality, $numbers);

        return array_merge($analysis, [
            'agent' => [
                'name' => 'combination-analyst',
                'version' => 1,
                'summary' => $this->buildSummary($analysis),
                'strengths' => $this->buildStrengths($analysis),
                'warnings' => $this->buildWarnings($analysis),
            ],
            'profile_comparison' => $profileComparison,
        ]);
    }

    /**
     * @param array<string, mixed> $analysis
     */
    protected function buildSummary(array $analysis): string
    {
        $headline = (string) data_get($analysis, 'narrative.headline', '');
        $avgFrequency = (float) ($analysis['average_frequency'] ?? 0);
        $avgDelay = (float) ($analysis['average_delay'] ?? 0);
        $topFrequencyHits = (int) ($analysis['top_frequency_hits'] ?? 0);
        $topDelayHits = (int) ($analysis['top_delay_hits'] ?? 0);

        $scoreValue = (float) data_get($analysis, 'score.value', 0);
        $scoreLabel = (string) data_get($analysis, 'score.label', '');

        $parts = [];

        if ($scoreLabel !== '') {
            $parts[] = sprintf('Score %.2f/100 (%s).', $scoreValue, $scoreLabel);
        }

        if ($headline !== '') {
            $parts[] = $headline;
        }

        $parts[] = "Frequência média: {$avgFrequency}.";
        $parts[] = "Atraso médio: {$avgDelay}.";
        $parts[] = "Números entre os mais frequentes: {$topFrequencyHits}.";
        $parts[] = "Números entre os mais atrasados: {$topDelayHits}.";

        return implode(' ', $parts);
    }

    /**
     * @param array<string, mixed> $analysis
     * @return array<int, string>
     */
    protected function buildStrengths(array $analysis): array
    {
        $strengths = [];

        if ((float) data_get($analysis, 'score.value', 0) >= 70) {
            $strengths[] = 'O score geral da combinação ficou em uma faixa positiva.';
        }

        if ((bool) ($analysis['within_common_even_pattern'] ?? false)) {
            $strengths[] = 'A quantidade de pares está dentro do padrão mais comum do histórico.';
        }

        if (abs((float) data_get($analysis, 'historical_comparison.sum_diff', 0)) <= 10) {
            $strengths[] = 'A soma está próxima da média histórica.';
        }

        if (abs((float) data_get($analysis, 'historical_comparison.range_diff', 0)) <= 8) {
            $strengths[] = 'A amplitude está próxima da média histórica.';
        }

        if ((int) ($analysis['top_frequency_hits'] ?? 0) >= 2) {
            $strengths[] = 'A combinação contém boa presença de números frequentes.';
        }

        return $strengths;
    }

    /**
     * @param array<string, mixed> $analysis
     * @return array<int, string>
     */
    protected function buildWarnings(array $analysis): array
    {
        $warnings = [];

        if ((float) data_get($analysis, 'score.value', 0) < 55) {
            $warnings[] = 'O score geral ficou abaixo do ideal para um jogo equilibrado.';
        }

        if (!(bool) ($analysis['within_common_even_pattern'] ?? false)) {
            $warnings[] = 'A distribuição de pares e ímpares foge do padrão mais recorrente.';
        }

        if (abs((float) data_get($analysis, 'historical_comparison.sum_diff', 0)) > 20) {
            $warnings[] = 'A soma está bem distante da média histórica.';
        }

        if (abs((float) data_get($analysis, 'historical_comparison.range_diff', 0)) > 12) {
            $warnings[] = 'A amplitude está distante da média histórica.';
        }

        if ((int) ($analysis['consecutive_count'] ?? 0) >= 2) {
            $warnings[] = 'Há mais de uma sequência consecutiva na combinação.';
        }

        if ((int) ($analysis['top_delay_hits'] ?? 0) >= 3) {
            $warnings[] = 'Há forte presença de números muito atrasados.';
        }

        return $warnings;
    }
}