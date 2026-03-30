<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use InvalidArgumentException;

class PatternAnalyzerService
{
    public function __construct(
        protected CombinationValidator $validator
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function analyze(LotteryModality $modality, array $numbers): array
    {
        $validation = $this->validator->validate($modality, $numbers);

        if (!$validation['valid']) {
            throw new InvalidArgumentException(implode(' ', $validation['errors']));
        }

        $sortedNumbers = $numbers;
        sort($sortedNumbers);

        $evenCount = count(array_filter($sortedNumbers, fn (int $number) => $number % 2 === 0));
        $oddCount = count($sortedNumbers) - $evenCount;

        $sum = array_sum($sortedNumbers);
        $min = min($sortedNumbers);
        $max = max($sortedNumbers);
        $range = $max - $min;

        $rangeDistribution = $this->buildRangeDistribution($modality, $sortedNumbers);
        $consecutiveGroups = $this->findConsecutiveGroups($sortedNumbers);

        return [
            'sorted_numbers' => $sortedNumbers,
            'even_count' => $evenCount,
            'odd_count' => $oddCount,
            'sum' => $sum,
            'min' => $min,
            'max' => $max,
            'range' => $range,
            'range_distribution' => $rangeDistribution,
            'consecutive_groups' => $consecutiveGroups,
            'consecutive_count' => count($consecutiveGroups),
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function buildRangeDistribution(LotteryModality $modality, array $sortedNumbers): array
    {
        $bucketCount = 5;
        $totalNumbers = ($modality->max_number - $modality->min_number) + 1;
        $bucketSize = (int) ceil($totalNumbers / $bucketCount);

        $distribution = [];

        for ($i = 0; $i < $bucketCount; $i++) {
            $start = $modality->min_number + ($i * $bucketSize);
            $end = min($start + $bucketSize - 1, $modality->max_number);
            $label = "{$start}-{$end}";
            $distribution[$label] = 0;
        }

        foreach ($sortedNumbers as $number) {
            $index = (int) floor(($number - $modality->min_number) / $bucketSize);

            if ($index >= $bucketCount) {
                $index = $bucketCount - 1;
            }

            $start = $modality->min_number + ($index * $bucketSize);
            $end = min($start + $bucketSize - 1, $modality->max_number);
            $label = "{$start}-{$end}";

            $distribution[$label]++;
        }

        return $distribution;
    }

    /**
     * @return array<int, array<int, int>>
     */
    protected function findConsecutiveGroups(array $sortedNumbers): array
    {
        $groups = [];
        $currentGroup = [];

        foreach ($sortedNumbers as $index => $number) {
            if ($index === 0) {
                $currentGroup = [$number];
                continue;
            }

            $previous = $sortedNumbers[$index - 1];

            if ($number === $previous + 1) {
                $currentGroup[] = $number;
            } else {
                if (count($currentGroup) >= 2) {
                    $groups[] = $currentGroup;
                }

                $currentGroup = [$number];
            }
        }

        if (count($currentGroup) >= 2) {
            $groups[] = $currentGroup;
        }

        return $groups;
    }
}