<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;

class CombinationInsightsService
{
    public function __construct(
        protected PatternAnalyzerService $patternAnalyzer,
        protected StatisticsService $statisticsService,
        protected DelayAnalysisService $delayAnalysisService,
        protected HistoricalProfileComparisonService $historicalProfileComparisonService,
        protected CombinationNarrativeService $combinationNarrativeService,
        protected GameScoreService $gameScoreService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function analyze(LotteryModality $modality, array $numbers, int $topLimit = 10): array
    {
        $pattern = $this->patternAnalyzer->analyze($modality, $numbers);

        $frequencies = $this->statisticsService->numberFrequencies($modality);
        $delays = $this->delayAnalysisService->numberDelays($modality);
        $historicalComparison = $this->historicalProfileComparisonService->compare($modality, $numbers);

        $averageFrequency = round(
            array_sum(array_map(fn (int $number) => $frequencies[$number] ?? 0, $numbers)) / count($numbers),
            2
        );

        $averageDelay = round(
            array_sum(array_map(fn (int $number) => $delays[$number] ?? 0, $numbers)) / count($numbers),
            2
        );

        $topFrequentNumbers = $this->topKeysByValue($frequencies, $topLimit, desc: true);
        $topDelayedNumbers = $this->topKeysByValue($delays, $topLimit, desc: true);

        $topFrequencyHits = count(array_intersect($numbers, $topFrequentNumbers));
        $topDelayHits = count(array_intersect($numbers, $topDelayedNumbers));

        $score = $this->gameScoreService->score($frequencies, $delays, array_merge($pattern, [
            'average_frequency' => $averageFrequency,
            'average_delay' => $averageDelay,
            'top_frequency_hits' => $topFrequencyHits,
            'top_delay_hits' => $topDelayHits,
            'historical_averages' => $historicalComparison['historical_averages'],
            'historical_comparison' => $historicalComparison['comparison'],
            'most_common_even_count' => $historicalComparison['most_common_even_count'],
            'within_common_even_pattern' => $historicalComparison['within_common_even_pattern'],
        ]));

        $result = array_merge($pattern, [
            'numbers' => $pattern['sorted_numbers'],
            'average_frequency' => $averageFrequency,
            'average_delay' => $averageDelay,
            'top_frequency_hits' => $topFrequencyHits,
            'top_delay_hits' => $topDelayHits,
            'historical_averages' => $historicalComparison['historical_averages'],
            'historical_comparison' => $historicalComparison['comparison'],
            'most_common_even_count' => $historicalComparison['most_common_even_count'],
            'within_common_even_pattern' => $historicalComparison['within_common_even_pattern'],
            'score' => $score,
        ]);

        $narrative = $this->combinationNarrativeService->build($result);

        return array_merge($result, [
            'narrative' => $narrative,
        ]);
    }

    /**
     * @param array<int, int> $values
     * @return array<int, int>
     */
    protected function topKeysByValue(array $values, int $limit, bool $desc = true): array
    {
        if ($desc) {
            arsort($values);
        } else {
            asort($values);
        }

        return array_map('intval', array_keys(array_slice($values, 0, $limit, true)));
    }
}