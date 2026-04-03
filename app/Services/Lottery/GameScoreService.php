<?php

namespace App\Services\Lottery;

class GameScoreService
{
    /**
     * @param array<int, int> $frequencies
     * @param array<int, int> $delays
     * @param array<string, mixed> $analysis
     * @return array<string, mixed>
     */
    public function score(array $frequencies, array $delays, array $analysis): array
    {
        $averageFrequency = (float) ($analysis['average_frequency'] ?? 0);
        $averageDelay = (float) ($analysis['average_delay'] ?? 0);
        $topFrequencyHits = (int) ($analysis['top_frequency_hits'] ?? 0);
        $topDelayHits = (int) ($analysis['top_delay_hits'] ?? 0);
        $sumDiff = abs((float) data_get($analysis, 'historical_comparison.sum_diff', 0));
        $rangeDiff = abs((float) data_get($analysis, 'historical_comparison.range_diff', 0));
        $evenDiff = abs((float) data_get($analysis, 'historical_comparison.even_count_diff', 0));
        $withinCommonEvenPattern = (bool) ($analysis['within_common_even_pattern'] ?? false);
        $consecutiveCount = (int) ($analysis['consecutive_count'] ?? 0);

        $historicalAlignment = max(0, 40 - (($sumDiff * 1.2) + ($rangeDiff * 1.4) + ($evenDiff * 8)));
        $frequencyStrength = $this->normalizeToScale(
            $averageFrequency,
            min($frequencies),
            max($frequencies),
            20
        );

        $globalAverageDelay = count($delays) > 0
            ? (array_sum($delays) / count($delays))
            : 0.0;

        $delayBalance = $this->distanceScore(
            $averageDelay,
            $globalAverageDelay,
            max(1.0, max($delays) - min($delays)),
            15
        );

        $patternBalance = 0;
        $patternBalance += $withinCommonEvenPattern ? 8 : 3;
        $patternBalance += $consecutiveCount === 0 ? 7 : ($consecutiveCount === 1 ? 5 : 1);

        $hotColdMix = 0;
        $hotColdMix += match (true) {
            $topFrequencyHits >= 1 && $topFrequencyHits <= 2 => 5,
            $topFrequencyHits === 0 || $topFrequencyHits === 3 => 3,
            default => 1,
        };
        $hotColdMix += match (true) {
            $topDelayHits <= 1 => 5,
            $topDelayHits === 2 => 3,
            default => 1,
        };

        $score = round($historicalAlignment + $frequencyStrength + $delayBalance + $patternBalance + $hotColdMix, 2);
        $score = max(0, min(100, $score));

        return [
            'value' => $score,
            'label' => $this->label($score),
            'profile' => $this->profile($topFrequencyHits, $topDelayHits, $averageDelay, $globalAverageDelay),
            'breakdown' => [
                'historical_alignment' => round($historicalAlignment, 2),
                'frequency_strength' => round($frequencyStrength, 2),
                'delay_balance' => round($delayBalance, 2),
                'pattern_balance' => round($patternBalance, 2),
                'hot_cold_mix' => round($hotColdMix, 2),
            ],
        ];
    }

    protected function normalizeToScale(float $value, float $min, float $max, float $scale): float
    {
        if ($max <= $min) {
            return $scale / 2;
        }

        return (($value - $min) / ($max - $min)) * $scale;
    }

    protected function distanceScore(float $value, float $target, float $span, float $scale): float
    {
        if ($span <= 0) {
            return $scale / 2;
        }

        $ratio = min(1, abs($value - $target) / $span);

        return (1 - $ratio) * $scale;
    }

    protected function label(float $score): string
    {
        return match (true) {
            $score >= 85 => 'Excelente',
            $score >= 70 => 'Boa',
            $score >= 55 => 'Razoável',
            default => 'Arriscada',
        };
    }

    protected function profile(
        int $topFrequencyHits,
        int $topDelayHits,
        float $averageDelay,
        float $globalAverageDelay
    ): string {
        if ($topFrequencyHits >= 1 && $topDelayHits <= 1 && $averageDelay <= ($globalAverageDelay + 2)) {
            return 'Equilibrado';
        }

        if ($topFrequencyHits >= max(1, $topDelayHits)) {
            return 'Conservador';
        }

        return 'Agressivo';
    }
}
