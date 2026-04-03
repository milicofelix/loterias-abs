<?php

namespace App\Services\Lottery\Agents;

use App\Models\LotteryModality;
use App\Services\Lottery\HistoricalProfileComparisonService;

class ProfileComparisonAgentService
{
    public function __construct(
        protected HistoricalProfileComparisonService $historicalProfileComparisonService,
    ) {
    }

    /**
     * @param array<int, int> $leftNumbers
     * @param array<int, int>|null $rightNumbers
     * @return array<string, mixed>
     */
    public function compare(
        LotteryModality $modality,
        array $leftNumbers,
        ?array $rightNumbers = null
    ): array {
        sort($leftNumbers);

        $leftProfile = $this->buildProfile($modality, $leftNumbers);

        $rightProfile = $rightNumbers !== null
            ? $this->buildProfile($modality, $rightNumbers)
            : null;

        return [
            'name' => 'profile-comparator',
            'version' => 1,
            'left' => $leftProfile,
            'right' => $rightProfile,
            'summary' => $this->buildSummary($leftProfile, $rightProfile),
            'highlights' => $this->buildHighlights($leftProfile, $rightProfile),
            'winner' => $this->resolveWinner($leftProfile, $rightProfile),
        ];
    }

    /**
     * @param array<int, int> $numbers
     * @return array<string, mixed>
     */
    protected function buildProfile(LotteryModality $modality, array $numbers): array
    {
        sort($numbers);

        $sum = array_sum($numbers);
        $range = max($numbers) - min($numbers);
        $evenCount = count(array_filter($numbers, fn (int $n) => $n % 2 === 0));
        $oddCount = count($numbers) - $evenCount;
        $consecutiveCount = $this->countConsecutivePairs($numbers);

        $historical = $this->historicalProfileComparisonService->compare($modality, $numbers);

        $score = $this->historicalAlignmentScore($historical);

        return [
            'numbers' => array_values($numbers),
            'statistics' => [
                'sum' => $sum,
                'range' => $range,
                'even_count' => $evenCount,
                'odd_count' => $oddCount,
                'consecutive_count' => $consecutiveCount,
            ],
            'historical_comparison' => $historical,
            'historical_alignment_score' => $score,
        ];
    }

    protected function countConsecutivePairs(array $numbers): int
    {
        $count = 0;

        for ($i = 1; $i < count($numbers); $i++) {
            if ($numbers[$i] === $numbers[$i - 1] + 1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param array<string, mixed> $historical
     */
    protected function historicalAlignmentScore(array $historical): float
    {
        $sumDiff = abs((float) data_get($historical, 'sum_diff', 0));
        $rangeDiff = abs((float) data_get($historical, 'range_diff', 0));
        $evenDiff = abs((float) data_get($historical, 'even_count_diff', 0));

        return round(100 - (($sumDiff * 1.5) + ($rangeDiff * 1.2) + ($evenDiff * 8)), 2);
    }

    /**
     * @param array<string, mixed> $left
     * @param array<string, mixed>|null $right
     */
    protected function buildSummary(array $left, ?array $right): string
    {
        if ($right === null) {
            return sprintf(
                'A combinação analisada teve score de alinhamento histórico %.2f.',
                $left['historical_alignment_score']
            );
        }

        $leftScore = (float) $left['historical_alignment_score'];
        $rightScore = (float) $right['historical_alignment_score'];

        if (abs($leftScore - $rightScore) <= 2) {
            return 'As duas combinações estão muito próximas em alinhamento com o perfil histórico.';
        }

        if ($leftScore > $rightScore) {
            return 'A combinação A está mais alinhada ao perfil histórico do que a combinação B.';
        }

        return 'A combinação B está mais alinhada ao perfil histórico do que a combinação A.';
    }

    /**
     * @param array<string, mixed> $left
     * @param array<string, mixed>|null $right
     * @return array<int, string>
     */
    protected function buildHighlights(array $left, ?array $right): array
    {
        $highlights = [];

        if ($right === null) {
            $highlights[] = sprintf(
                'Score de alinhamento histórico: %.2f.',
                $left['historical_alignment_score']
            );

            $highlights[] = sprintf(
                'Soma %s, amplitude %s.',
                $left['statistics']['sum'],
                $left['statistics']['range']
            );

            return $highlights;
        }

        $leftScore = (float) $left['historical_alignment_score'];
        $rightScore = (float) $right['historical_alignment_score'];

        $leftSum = (int) data_get($left, 'statistics.sum', 0);
        $rightSum = (int) data_get($right, 'statistics.sum', 0);

        $leftRange = (int) data_get($left, 'statistics.range', 0);
        $rightRange = (int) data_get($right, 'statistics.range', 0);

        $highlights[] = sprintf(
            'Combinação A: score %.2f | soma %d | amplitude %d.',
            $leftScore,
            $leftSum,
            $leftRange
        );

        $highlights[] = sprintf(
            'Combinação B: score %.2f | soma %d | amplitude %d.',
            $rightScore,
            $rightSum,
            $rightRange
        );

        if ($leftRange === $rightRange) {
            $highlights[] = 'As duas combinações têm a mesma amplitude.';
        } elseif ($leftRange > $rightRange) {
            $highlights[] = 'A combinação A é mais dispersa que a combinação B.';
        } else {
            $highlights[] = 'A combinação B é mais dispersa que a combinação A.';
        }

        return $highlights;
    }

    /**
     * @param array<string, mixed> $left
     * @param array<string, mixed>|null $right
     * @return array<string, mixed>|null
     */
    protected function resolveWinner(array $left, ?array $right): ?array
    {
        if ($right === null) {
            return null;
        }

        $leftScore = (float) $left['historical_alignment_score'];
        $rightScore = (float) $right['historical_alignment_score'];

        if (abs($leftScore - $rightScore) <= 2) {
            return [
                'side' => 'tie',
                'reason' => 'As duas combinações ficaram muito próximas no alinhamento histórico.',
            ];
        }

        if ($leftScore > $rightScore) {
            return [
                'side' => 'left',
                'reason' => 'A combinação A ficou mais próxima do perfil histórico.',
            ];
        }

        return [
            'side' => 'right',
            'reason' => 'A combinação B ficou mais próxima do perfil histórico.',
        ];
    }
}