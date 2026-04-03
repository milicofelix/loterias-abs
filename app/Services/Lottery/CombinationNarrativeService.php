<?php

namespace App\Services\Lottery;

class CombinationNarrativeService
{
    /**
     * @param array<string, mixed> $analysis
     * @return array{headline: string, insights: array<int, string>}
     */
    public function build(array $analysis): array
    {
        $sumDiff = (float) ($analysis['historical_comparison']['sum_diff'] ?? 0);
        $rangeDiff = (float) ($analysis['historical_comparison']['range_diff'] ?? 0);
        $evenCount = (int) ($analysis['even_count'] ?? 0);
        $mostCommonEvenCount = (int) ($analysis['most_common_even_count'] ?? 0);
        $withinCommonEvenPattern = (bool) ($analysis['within_common_even_pattern'] ?? false);
        $consecutiveCount = (int) ($analysis['consecutive_count'] ?? 0);
        $topFrequencyHits = (int) ($analysis['top_frequency_hits'] ?? 0);
        $topDelayHits = (int) ($analysis['top_delay_hits'] ?? 0);

        $headline = $this->buildHeadline($sumDiff, $withinCommonEvenPattern);

        $insights = [];

        $insights[] = $this->describeSum($sumDiff);
        $insights[] = $this->describeEvenPattern($evenCount, $mostCommonEvenCount, $withinCommonEvenPattern);
        $insights[] = $this->describeRange($rangeDiff);

        if ($consecutiveCount > 0) {
            $insights[] = "A combinação apresenta {$consecutiveCount} sequência(s) consecutiva(s).";
        } else {
            $insights[] = 'A combinação não apresenta sequências consecutivas.';
        }

        if ($topFrequencyHits > 0) {
            $insights[] = "Há {$topFrequencyHits} número(s) entre os mais frequentes do histórico.";
        } else {
            $insights[] = 'Nenhum número da combinação está entre os mais frequentes do histórico.';
        }

        if ($topDelayHits > 0) {
            $insights[] = "Há {$topDelayHits} número(s) entre os mais atrasados do histórico.";
        } else {
            $insights[] = 'Nenhum número da combinação está entre os mais atrasados do histórico.';
        }

        return [
            'headline' => $headline,
            'insights' => $insights,
        ];
    }

    protected function buildHeadline(float $sumDiff, bool $withinCommonEvenPattern): string
    {
        if (abs($sumDiff) <= 10 && $withinCommonEvenPattern) {
            return 'A combinação está relativamente próxima do perfil histórico médio.';
        }

        if ($sumDiff > 10) {
            return 'A combinação está acima da média histórica em pontos importantes.';
        }

        if ($sumDiff < -10) {
            return 'A combinação está abaixo da média histórica em pontos importantes.';
        }

        return 'A combinação apresenta um perfil misto em relação ao histórico.';
    }

    protected function describeSum(float $sumDiff): string
    {
        if ($sumDiff > 10) {
            return 'A soma da combinação está acima da média histórica.';
        }

        if ($sumDiff < -10) {
            return 'A soma da combinação está abaixo da média histórica.';
        }

        return 'A soma da combinação está próxima da média histórica.';
    }

    protected function describeEvenPattern(int $evenCount, int $mostCommonEvenCount, bool $withinCommonEvenPattern): string
    {
        if ($withinCommonEvenPattern) {
            return "A quantidade de pares ({$evenCount}) coincide com o padrão mais comum do histórico.";
        }

        return "A quantidade de pares ({$evenCount}) difere do padrão mais comum do histórico ({$mostCommonEvenCount}).";
    }

    protected function describeRange(float $rangeDiff): string
    {
        if ($rangeDiff > 8) {
            return 'A amplitude da combinação está acima da média histórica.';
        }

        if ($rangeDiff < -8) {
            return 'A amplitude da combinação está abaixo da média histórica.';
        }

        return 'A amplitude da combinação está próxima da média histórica.';
    }
}