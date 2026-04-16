<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;

class HistoricalPrizeSummaryService
{
    /**
     * @param array<int, int> $numbers
     * @return array<string, mixed>
     */
    public function analyze(LotteryModality $modality, array $numbers): array
    {
        $results = $this->analyzeBatch($modality, [$numbers]);

        return $results[0]['historical_prize_summary'] ?? $this->emptySummary($modality);
    }

    /**
     * @param array<int, array<int, int>> $games
     * @return array<int, array{numbers: array<int, int>, historical_prize_summary: array<string, mixed>}>
     */
    public function analyzeBatch(LotteryModality $modality, array $games): array
    {
        $normalizedGames = array_map(fn (array $numbers) => $this->normalizeNumbers($numbers), $games);
        $draws = $this->loadNormalizedDraws($modality);

        return array_map(function (array $gameNumbers) use ($modality, $draws) {
            return [
                'numbers' => $gameNumbers,
                'historical_prize_summary' => $this->buildSummary($modality, $gameNumbers, $draws),
            ];
        }, $normalizedGames);
    }

    /**
     * @return array<int, array{contest_number:int|null, draw_date:string|null, numbers:array<int,int>}>
     */
    protected function loadNormalizedDraws(LotteryModality $modality): array
    {
        return $modality->draws()
            ->with('numbers')
            ->orderBy('contest_number')
            ->get()
            ->map(function ($draw) {
                return [
                    'contest_number' => $draw->contest_number,
                    'draw_date' => $draw->draw_date?->format('Y-m-d'),
                    'numbers' => $draw->numbers
                        ->pluck('number')
                        ->map(fn ($number) => (int) $number)
                        ->sort()
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param array<int, int> $gameNumbers
     * @param array<int, array{contest_number:int|null, draw_date:string|null, numbers:array<int,int>}> $draws
     * @return array<string, mixed>
     */
    protected function buildSummary(LotteryModality $modality, array $gameNumbers, array $draws): array
    {
        $summary = $this->emptySummary($modality);
        $sampleLimit = 5;

        foreach ($draws as $draw) {
            $hits = count(array_intersect($gameNumbers, $draw['numbers']));

            if ($hits < 2) {
                continue;
            }

            $hitKey = (string) $hits;

            if (array_key_exists($hitKey, $summary['hit_counts'])) {
                $summary['hit_counts'][$hitKey]++;
            }

            $summary['best_hit'] = max((int) $summary['best_hit'], $hits);
            $summary['ever_prized'] = true;
            $summary['total_prized_occurrences']++;
            $summary['last_occurrence'] = [
                'contest_number' => $draw['contest_number'],
                'draw_date' => $draw['draw_date'],
                'hits' => $hits,
            ];

            if (count($summary['sample_occurrences']) < $sampleLimit) {
                $summary['sample_occurrences'][] = [
                    'contest_number' => $draw['contest_number'],
                    'draw_date' => $draw['draw_date'],
                    'hits' => $hits,
                ];
            }
        }

        return $summary;
    }

    /**
     * @param array<int, int> $numbers
     * @return array<int, int>
     */
    protected function normalizeNumbers(array $numbers): array
    {
        $normalized = array_map(static fn ($number) => (int) $number, $numbers);
        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptySummary(LotteryModality $modality): array
    {
        $hitCounts = [];

        for ($hits = 2; $hits <= (int) $modality->draw_count; $hits++) {
            $hitCounts[(string) $hits] = 0;
        }

        return [
            'best_hit' => 0,
            'ever_prized' => false,
            'total_prized_occurrences' => 0,
            'contest_count_checked' => (int) $modality->draws()->count(),
            'hit_counts' => $hitCounts,
            'last_occurrence' => null,
            'sample_occurrences' => [],
        ];
    }
}
