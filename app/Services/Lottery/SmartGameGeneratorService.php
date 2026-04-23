<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use App\Services\Lottery\Agents\ProfileComparisonAgentService;
use InvalidArgumentException;

class SmartGameGeneratorService
{
    public function __construct(
        protected PatternAnalyzerService $patternAnalyzerService,
        protected StatisticsService $statisticsService,
        protected DelayAnalysisService $delayAnalysisService,
        protected HistoricalProfileComparisonService $historicalProfileComparisonService,
        protected ProfileComparisonAgentService $profileComparisonAgentService,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function generate(LotteryModality $modality, array $options = []): array
    {
        $strategy = (string) ($options['strategy'] ?? 'balanced');
        $games = (int) ($options['games'] ?? 5);
        // $candidatePool = (int) ($options['candidate_pool'] ?? 180);
        // $targetValidCandidates = max($games * 4, 12);
        // $attemptLimit = $candidatePool;
        // $attempts = 0;
        $candidatePool = (int) ($options['candidate_pool'] ?? 140);

        $this->validateOptions($modality, $strategy, $games, $candidatePool);

        $frequencies = $this->statisticsService->numberFrequencies($modality);
        $delays = $this->delayAnalysisService->numberDelays($modality);
        $historicalReference = $this->historicalProfileComparisonService->compare(
            $modality,
            range($modality->min_number, $modality->min_number + $modality->draw_count - 1)
        )['historical_averages'] ?? ['sum' => 0.0, 'range' => 0.0];
        $topFrequentNumbers = collect($frequencies)
            ->sortDesc()
            ->keys()
            ->take(12)
            ->map(fn ($number) => (int) $number)
            ->values()
            ->all();

        $topDelayedNumbers = collect($delays)
            ->sortDesc()
            ->keys()
            ->take(12)
            ->map(fn ($number) => (int) $number)
            ->values()
            ->all();

        $candidates = [];
        $seen = [];
        // $attemptLimit = $candidatePool * 6;
        $targetValidCandidates = max($games * 3, 10);
        $attemptLimit = max($candidatePool, $targetValidCandidates * 8);
        $attempts = 0;

        while (count($candidates) < $targetValidCandidates && $attempts < $attemptLimit) {
            $attempts++;
            $numbers = $this->generateCandidateNumbers($modality, $strategy, $frequencies, $delays);
            sort($numbers);

            $key = implode('-', $numbers);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            if (! $this->passesQuickFilters($modality, $numbers, $historicalReference)) {
                continue;
            }

            // $candidates[] = $this->buildCandidatePayload($modality, $numbers, $strategy, $frequencies, $delays);
            $candidates[] = $this->buildLightCandidatePayload(
                $modality,
                $numbers,
                $strategy,
                $frequencies,
                $delays,
                $topFrequentNumbers,
                $topDelayedNumbers,
            );
        }

        usort($candidates, fn (array $left, array $right) => $right['weighted_score'] <=> $left['weighted_score']);

        // return array_slice($candidates, 0, $games);
        $finalists = array_slice($candidates, 0, min(count($candidates), max($games * 2, $games)));

        $finalists = array_map(function (array $candidate) use ($modality) {
            return $this->enrichCandidatePayload($modality, $candidate);
        }, $finalists);

        usort($finalists, fn (array $left, array $right) => $right['weighted_score'] <=> $left['weighted_score']);

        return array_slice($finalists, 0, $games);
    }

    protected function validateOptions(
        LotteryModality $modality,
        string $strategy,
        int $games,
        int $candidatePool
    ): void {
        if (! in_array($strategy, ['balanced', 'hot'], true)) {
            throw new InvalidArgumentException('Estratégia inválida. Utilize balanced ou hot.');
        }

        if ($games < 1 || $games > 20) {
            throw new InvalidArgumentException('A quantidade de jogos deve estar entre 1 e 20.');
        }

        if ($candidatePool < 40 || $candidatePool > 2000) {
            throw new InvalidArgumentException('O volume de candidatos deve estar entre 40 e 2000.');
        }
    }

    /**
     * @param array<int, int> $frequencies
     * @param array<int, int> $delays
     * @return array<int, int>
     */
    protected function generateCandidateNumbers(
        LotteryModality $modality,
        string $strategy,
        array $frequencies,
        array $delays
    ): array {
        $drawCount = (int) $modality->draw_count;

        $hotTake = max($drawCount + 4, (int) ceil($drawCount * 1.8));
        $warmTake = max($drawCount + 8, (int) ceil($drawCount * 2.5));

        if ($strategy === 'hot') {
            $hotBucket = collect($frequencies)
                ->sortDesc()
                ->keys()
                ->take(min($hotTake, (int) $modality->max_number))
                ->map(fn ($number) => (int) $number)
                ->values()
                ->all();

            $warmBucket = collect($frequencies)
                ->sortDesc()
                ->keys()
                ->take(min($warmTake, (int) $modality->max_number))
                ->map(fn ($number) => (int) $number)
                ->values()
                ->all();

            return $this->pickUniqueNumbers([
                ['pool' => $hotBucket, 'count' => min($drawCount, max(3, (int) round($drawCount * 0.45)))],
                ['pool' => $warmBucket, 'count' => min($drawCount, max(1, (int) round($drawCount * 0.20)))],
                ['pool' => range($modality->min_number, $modality->max_number), 'count' => $drawCount],
            ], $drawCount, $modality);
        }

        $hotBucket = collect($frequencies)
            ->sortDesc()
            ->keys()
            ->take(min($hotTake, (int) $modality->max_number))
            ->map(fn ($number) => (int) $number)
            ->values()
            ->all();

        $delayBucket = collect($delays)
            ->sortDesc()
            ->keys()
            ->take(min($hotTake, (int) $modality->max_number))
            ->map(fn ($number) => (int) $number)
            ->values()
            ->all();

        $neutralBucket = range($modality->min_number, $modality->max_number);

        return $this->pickUniqueNumbers([
            ['pool' => $hotBucket, 'count' => min($drawCount, max(2, (int) round($drawCount * 0.35)))],
            ['pool' => $delayBucket, 'count' => min($drawCount, max(1, (int) round($drawCount * 0.15)))],
            ['pool' => $neutralBucket, 'count' => $drawCount],
        ], $drawCount, $modality);
    }

    /**
     * @param array<int, array{pool: array<int, int>, count: int}> $buckets
     * @return array<int, int>
     */
    protected function pickUniqueNumbers(array $buckets, int $targetCount, LotteryModality $modality): array
    {
        $selected = [];

        foreach ($buckets as $bucket) {
            $pool = $bucket['pool'];
            shuffle($pool);
            $bucketHits = 0;

            foreach ($pool as $number) {
                if (count($selected) >= $targetCount) {
                    break 2;
                }

                if (! in_array($number, $selected, true)) {
                    $selected[] = (int) $number;
                    $bucketHits++;
                }

                if ($bucketHits >= $bucket['count']) {
                    break;
                }
            }
        }

        while (count($selected) < $targetCount) {
            $number = random_int((int) $modality->min_number, (int) $modality->max_number);

            if (! in_array($number, $selected, true)) {
                $selected[] = $number;
            }
        }

        sort($selected);

        return $selected;
    }

    /**
     * @param array<int, int> $numbers
     * @param array<string, float> $historicalReference
     */
    protected function passesQuickFilters(LotteryModality $modality, array $numbers, array $historicalReference): bool
    {
        $pattern = $this->patternAnalyzerService->analyze($modality, $numbers);
        $sum = (int) $pattern['sum'];
        $range = (int) $pattern['range'];
        $evenCount = (int) $pattern['even_count'];
        $distribution = array_values($pattern['range_distribution']);
        $maxBucket = $distribution !== [] ? max($distribution) : 0;

        $drawCount = (int) $modality->draw_count;
        $minEven = max(1, (int) floor($drawCount * 0.20));
        $maxEven = min($drawCount - 1, (int) ceil($drawCount * 0.80));

        if ($evenCount < $minEven || $evenCount > $maxEven) {
            return false;
        }

        if ($this->longestConsecutiveRun($numbers) >= max(4, (int) ceil($drawCount / 3))) {
            return false;
        }

        if ($maxBucket > ((int) ceil($drawCount / 5) + 2)) {
            return false;
        }

        $historicalSum = (float) ($historicalReference['sum'] ?? 0);
        if ($historicalSum > 0 && abs($sum - $historicalSum) > max(15, $drawCount * 4)) {
            return false;
        }

        $historicalRange = (float) ($historicalReference['range'] ?? 0);
        if ($historicalRange > 0 && abs($range - $historicalRange) > max(10, $drawCount * 2)) {
            return false;
        }

        return true;
    }

    protected function longestConsecutiveRun(array $numbers): int
    {
        $longest = 1;
        $current = 1;

        for ($i = 1; $i < count($numbers); $i++) {
            if ($numbers[$i] === $numbers[$i - 1] + 1) {
                $current++;
                $longest = max($longest, $current);
            } else {
                $current = 1;
            }
        }

        return $longest;
    }

    /**
     * @param array<int, int> $numbers
     * @param array<int, int> $frequencies
     * @param array<int, int> $delays
     * @param array<int, int> $topFrequentNumbers
     * @param array<int, int> $topDelayedNumbers
     * @return array<string, mixed>
     */
    protected function buildLightCandidatePayload(
        LotteryModality $modality,
        array $numbers,
        string $strategy,
        array $frequencies,
        array $delays,
        array $topFrequentNumbers,
        array $topDelayedNumbers
    ): array {
        // $historical = $this->historicalProfileComparisonService->compare($modality, $numbers);
        // $profileComparison = $this->profileComparisonAgentService->compare($modality, $numbers);
        $pattern = $this->patternAnalyzerService->analyze($modality, $numbers);

        $averageFrequency = round(
            collect($numbers)->avg(fn (int $number) => $frequencies[$number] ?? 0),
            2
        );

        $averageDelay = round(
            collect($numbers)->avg(fn (int $number) => $delays[$number] ?? 0),
            2
        );

        // $topFrequentNumbers = collect($frequencies)
        //     ->sortDesc()
        //     ->keys()
        //     ->take(12)
        //     ->map(fn ($number) => (int) $number)
        //     ->all();

        // $topDelayedNumbers = collect($delays)
        //     ->sortDesc()
        //     ->keys()
        //     ->take(12)
        //     ->map(fn ($number) => (int) $number)
        //     ->all();

        $topFrequencyHits = count(array_intersect($numbers, $topFrequentNumbers));
        $topDelayHits = count(array_intersect($numbers, $topDelayedNumbers));

        // $baseScore = max(0.0, min(100.0, (float) data_get($profileComparison, 'left.historical_alignment_score', 0.0)));
        // $frequencyStrength = $this->normalizeValue($averageFrequency, min($frequencies), max($frequencies));
        $frequencyStrength = $this->normalizeValue($averageFrequency, (float) min($frequencies), (float) max($frequencies));
        $delayBalance = max(0, 100 - abs(45 - ($averageDelay * 5)));
        $patternQuality = $this->calculatePatternQuality($pattern);

        $lightHistoricalScore = ($topFrequencyHits * 12) + ($topDelayHits * 5);
        $baseScore = (int) round(
            min(100, ($frequencyStrength * 0.45) + ($patternQuality * 0.35) + ($delayBalance * 0.15) + $lightHistoricalScore)
        );

        $weightedScore = $strategy === 'hot'
            // ? round(($baseScore * 0.40) + ($frequencyStrength * 0.40) + ($patternQuality * 0.15) + ($delayBalance * 0.05))
            // : round(($baseScore * 0.50) + ($patternQuality * 0.20) + ($frequencyStrength * 0.15) + ($delayBalance * 0.15));
            ? (int) round(($baseScore * 0.55) + ($frequencyStrength * 0.30) + ($patternQuality * 0.15))
            : (int) round(($baseScore * 0.60) + ($patternQuality * 0.20) + ($frequencyStrength * 0.10) + ($delayBalance * 0.10));

        return [
            'numbers' => $numbers,
            'strategy' => $strategy,
            // 'weighted_score' => (int) max(0, min(100, $weightedScore)),
            'weighted_score' => max(0, min(100, $weightedScore)),
            'historical_alignment_score' => round($baseScore, 2),
            'average_frequency' => $averageFrequency,
            'average_delay' => $averageDelay,
            'top_frequency_hits' => $topFrequencyHits,
            'top_delay_hits' => $topDelayHits,
            // 'classification' => $this->classifyScore((int) $weightedScore),
            // 'profile' => $this->inferProfile($strategy, $topFrequencyHits, $topDelayHits, $pattern),
            // 'reason' => $this->buildReason($strategy, $topFrequencyHits, $topDelayHits, $baseScore, $patternQuality),
            'pattern' => [
                'sum' => $pattern['sum'],
                'even_count' => $pattern['even_count'],
                'odd_count' => $pattern['odd_count'],
                'range' => $pattern['range'],
                'consecutive_count' => $pattern['consecutive_count'],
                'range_distribution' => $pattern['range_distribution'],
            ],
            // 'historical_comparison' => $historical['comparison'] ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $candidate
     * @return array<string, mixed>
     */
    protected function enrichCandidatePayload(LotteryModality $modality, array $candidate): array
    {
        $numbers = $candidate['numbers'];
        $pattern = $candidate['pattern'];

        $historical = $this->historicalProfileComparisonService->compare($modality, $numbers);
        $profileComparison = $this->profileComparisonAgentService->compare($modality, $numbers);

        $alignmentScore = max(
            0.0,
            min(100.0, (float) data_get($profileComparison, 'left.historical_alignment_score', $candidate['historical_alignment_score'] ?? 0.0))
        );

        $candidate['historical_alignment_score'] = round($alignmentScore, 2);
        $candidate['classification'] = $this->classifyScore((int) ($candidate['weighted_score'] ?? 0));
        $candidate['profile'] = $this->inferProfile(
            (string) ($candidate['strategy'] ?? 'balanced'),
            (int) ($candidate['top_frequency_hits'] ?? 0),
            (int) ($candidate['top_delay_hits'] ?? 0),
            $pattern,
        );
        $candidate['reason'] = $this->buildReason(
            (string) ($candidate['strategy'] ?? 'balanced'),
            (int) ($candidate['top_frequency_hits'] ?? 0),
            (int) ($candidate['top_delay_hits'] ?? 0),
            $alignmentScore,
            $this->calculatePatternQuality($pattern),
        );
        $candidate['historical_comparison'] = $historical['comparison'] ?? [];

        return $candidate;
    }

    protected function normalizeValue(float $value, float $min, float $max): int
    {
        if ($max <= $min) {
            return 50;
        }

        return (int) round((($value - $min) / ($max - $min)) * 100);
    }

    /**
     * @param array<string, mixed> $pattern
     */
    protected function calculatePatternQuality(array $pattern): int
    {
        $score = 100;
        $evenCount = (int) ($pattern['even_count'] ?? 0);
        $consecutiveCount = (int) ($pattern['consecutive_count'] ?? 0);
        $distribution = array_values($pattern['range_distribution'] ?? []);
        $maxBucket = $distribution !== [] ? max($distribution) : 0;

        $totalSelected = max(1, $evenCount + ((int) ($pattern['odd_count'] ?? 0)));
        $minEven = max(1, (int) floor($totalSelected * 0.20));
        $maxEven = min($totalSelected - 1, (int) ceil($totalSelected * 0.80));

        if ($evenCount < $minEven || $evenCount > $maxEven) {
            $score -= 25;
        }

        if ($consecutiveCount >= max(2, (int) ceil($totalSelected / 6))) {
            $score -= 15;
        }

        if ($maxBucket > ((int) ceil($totalSelected / 5) + 2)) {
            $score -= 20;
        } elseif ($maxBucket > ((int) ceil($totalSelected / 5) + 1)) {
            $score -= 8;
        }

        return max(0, $score);
    }

    protected function classifyScore(int $score): string
    {
        return match (true) {
            $score >= 85 => 'Excelente',
            $score >= 72 => 'Boa',
            $score >= 58 => 'Razoável',
            default => 'Arriscada',
        };
    }

    /**
     * @param array<string, mixed> $pattern
     */
    protected function inferProfile(string $strategy, int $topFrequencyHits, int $topDelayHits, array $pattern): string
    {
        if ($strategy === 'hot' || $topFrequencyHits >= 3) {
            return 'Quente';
        }

        if ($topDelayHits >= 2) {
            return 'Agressivo';
        }

        if ((int) ($pattern['consecutive_count'] ?? 0) === 0) {
            return 'Equilibrado';
        }

        return 'Misto';
    }

    protected function buildReason(
        string $strategy,
        int $topFrequencyHits,
        int $topDelayHits,
        float $historicalAlignmentScore,
        int $patternQuality
    ): string {
        $parts = [];

        if ($strategy === 'hot') {
            $parts[] = 'Priorizou números mais fortes em frequência.';
        } else {
            $parts[] = 'Buscou equilíbrio entre frequência, atraso e padrão estrutural.';
        }

        $parts[] = sprintf('Alinhamento histórico %.2f.', $historicalAlignmentScore);
        $parts[] = sprintf('Acertos no grupo quente: %d.', $topFrequencyHits);

        if ($topDelayHits > 0) {
            $parts[] = sprintf('Presença controlada de atrasados: %d.', $topDelayHits);
        }

        if ($patternQuality >= 85) {
            $parts[] = 'Padrão estrutural consistente.';
        }

        return implode(' ', $parts);
    }
}
