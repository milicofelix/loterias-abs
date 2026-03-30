<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use InvalidArgumentException;

class CombinationGeneratorService
{
    public function generate(LotteryModality $modality, array $options = []): array
    {
        $count = $options['count'] ?? $modality->bet_min_count;
        $minEven = $options['min_even'] ?? 0;
        $maxEven = $options['max_even'] ?? $count;
        $avoidConsecutiveRunGreaterThan = $options['avoid_consecutive_run_greater_than'] ?? null;

        $this->validateOptions($modality, $count, $minEven, $maxEven);

        $attempts = 0;
        $maxAttempts = 500;

        while ($attempts < $maxAttempts) {
            $attempts++;

            $numbers = $this->generateCandidate($modality, $count);
            sort($numbers);

            if (!$this->matchesEvenConstraints($numbers, $minEven, $maxEven)) {
                continue;
            }

            if (
                $avoidConsecutiveRunGreaterThan !== null &&
                $this->longestConsecutiveRun($numbers) > $avoidConsecutiveRunGreaterThan
            ) {
                continue;
            }

            return $numbers;
        }

        throw new InvalidArgumentException('Não foi possível gerar uma combinação válida com as restrições informadas.');
    }

    protected function validateOptions(
        LotteryModality $modality,
        int $count,
        int $minEven,
        int $maxEven
    ): void {
        if ($count < $modality->bet_min_count || $count > $modality->bet_max_count) {
            throw new InvalidArgumentException(
                "A quantidade deve estar entre {$modality->bet_min_count} e {$modality->bet_max_count}."
            );
        }

        if ($minEven < 0 || $maxEven < 0 || $minEven > $maxEven || $maxEven > $count) {
            throw new InvalidArgumentException('As restrições de números pares são inválidas.');
        }
    }

    protected function generateCandidate(LotteryModality $modality, int $count): array
    {
        $pool = range($modality->min_number, $modality->max_number);
        shuffle($pool);

        $selected = array_slice($pool, 0, $count);

        if (!$modality->allows_repetition) {
            $selected = array_values(array_unique($selected));
        }

        while (count($selected) < $count) {
            $candidate = random_int($modality->min_number, $modality->max_number);

            if (!in_array($candidate, $selected, true)) {
                $selected[] = $candidate;
            }
        }

        sort($selected);

        return $selected;
    }

    protected function matchesEvenConstraints(array $numbers, int $minEven, int $maxEven): bool
    {
        $evenCount = count(array_filter($numbers, fn (int $number) => $number % 2 === 0));

        return $evenCount >= $minEven && $evenCount <= $maxEven;
    }

    protected function longestConsecutiveRun(array $numbers): int
    {
        if (count($numbers) <= 1) {
            return count($numbers);
        }

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
}