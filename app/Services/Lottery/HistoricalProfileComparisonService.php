<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;

class HistoricalProfileComparisonService
{
    public function __construct(
        protected PatternAnalyzerService $patternAnalyzer
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function compare(LotteryModality $modality, array $numbers, ?int $lastDraws = null): array
    {
        $current = $this->patternAnalyzer->analyze($modality, $numbers);

        $drawsQuery = $modality->draws()
            ->with('numbers')
            ->orderByDesc('draw_date')
            ->orderByDesc('contest_number');

        if ($lastDraws !== null) {
            $drawsQuery->limit($lastDraws);
        }

        $draws = $drawsQuery->get();

        if ($draws->isEmpty()) {
            return [
                'historical_averages' => [
                    'sum' => 0.0,
                    'even_count' => 0.0,
                    'range' => 0.0,
                ],
                'comparison' => [
                    'sum_diff' => 0.0,
                    'even_count_diff' => 0.0,
                    'range_diff' => 0.0,
                ],
                'most_common_even_count' => 0,
                'within_common_even_pattern' => false,
            ];
        }

        $sums = [];
        $evenCounts = [];
        $ranges = [];

        foreach ($draws as $draw) {
            $drawNumbers = $draw->numbers
                ->pluck('number')
                ->map(fn ($value) => (int) $value)
                ->sort()
                ->values()
                ->all();

            $analysis = $this->patternAnalyzer->analyze($modality, $drawNumbers);

            $sums[] = $analysis['sum'];
            $evenCounts[] = $analysis['even_count'];
            $ranges[] = $analysis['range'];
        }

        $historicalAverages = [
            'sum' => round(array_sum($sums) / count($sums), 2),
            'even_count' => round(array_sum($evenCounts) / count($evenCounts), 2),
            'range' => round(array_sum($ranges) / count($ranges), 2),
        ];

        $evenCountFrequency = array_count_values($evenCounts);
        arsort($evenCountFrequency);
        $mostCommonEvenCount = (int) array_key_first($evenCountFrequency);

        return [
            'historical_averages' => $historicalAverages,
            'comparison' => [
                'sum_diff' => round($current['sum'] - $historicalAverages['sum'], 2),
                'even_count_diff' => round($current['even_count'] - $historicalAverages['even_count'], 2),
                'range_diff' => round($current['range'] - $historicalAverages['range'], 2),
            ],
            'most_common_even_count' => $mostCommonEvenCount,
            'within_common_even_pattern' => $current['even_count'] === $mostCommonEvenCount,
        ];
    }
}