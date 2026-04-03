<?php

namespace App\Services\Lottery\Agents;

use App\Models\Draw;
use App\Models\LotteryModality;
use App\Services\Lottery\HistoricalProfileComparisonService;

class DrawExplainerAgentService
{
    public function __construct(
        protected HistoricalProfileComparisonService $historicalProfileComparisonService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function explain(LotteryModality $modality, Draw $draw): array
    {
        $numbers = $draw->numbers
            ->pluck('number')
            ->sort()
            ->values()
            ->all();

        $sum = array_sum($numbers);
        $range = max($numbers) - min($numbers);
        $evenCount = count(array_filter($numbers, fn (int $n) => $n % 2 === 0));
        $oddCount = count($numbers) - $evenCount;
        $consecutiveCount = $this->countConsecutivePairs($numbers);

        $comparison = $this->historicalProfileComparisonService->compare($modality, $numbers);

        return [
            'name' => 'draw-explainer',
            'version' => 1,
            'contest_number' => $draw->contest_number,
            'numbers' => $numbers,
            'summary' => $this->buildSummary($draw, $sum, $range, $consecutiveCount, $comparison),
            'highlights' => $this->buildHighlights($sum, $range, $evenCount, $oddCount, $consecutiveCount, $comparison),
            'statistics' => [
                'sum' => $sum,
                'range' => $range,
                'even_count' => $evenCount,
                'odd_count' => $oddCount,
                'consecutive_count' => $consecutiveCount,
            ],
            'comparison' => $comparison,
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
     * @param array<string, mixed> $comparison
     */
    protected function buildSummary(
        Draw $draw,
        int $sum,
        int $range,
        int $consecutiveCount,
        array $comparison
    ): string {
        $parts = [];
        $parts[] = "O concurso {$draw->contest_number} teve soma {$sum} e amplitude {$range}.";

        if ($consecutiveCount > 0) {
            $parts[] = "Foram identificadas {$consecutiveCount} sequência(s) consecutiva(s).";
        } else {
            $parts[] = 'Não houve sequência consecutiva entre os números sorteados.';
        }

        $sumDiff = (float) data_get($comparison, 'sum_diff', 0);
        $rangeDiff = (float) data_get($comparison, 'range_diff', 0);

        if (abs($sumDiff) <= 5 && abs($rangeDiff) <= 5) {
            $parts[] = 'O resultado ficou bem próximo do perfil médio histórico.';
        } elseif (abs($sumDiff) > abs($rangeDiff)) {
            $parts[] = $sumDiff > 0
                ? 'O principal desvio em relação ao histórico esteve na soma mais alta.'
                : 'O principal desvio em relação ao histórico esteve na soma mais baixa.';
        } else {
            $parts[] = $rangeDiff > 0
                ? 'O principal desvio em relação ao histórico esteve na amplitude mais aberta.'
                : 'O principal desvio em relação ao histórico esteve na amplitude mais concentrada.';
        }

        return implode(' ', $parts);
    }

    /**
     * @param array<string, mixed> $comparison
     * @return array<int, string>
     */
    protected function buildHighlights(
        int $sum,
        int $range,
        int $evenCount,
        int $oddCount,
        int $consecutiveCount,
        array $comparison
    ): array {
        $highlights = [];

        $sumDiff = (float) data_get($comparison, 'sum_diff', 0);
        $rangeDiff = (float) data_get($comparison, 'range_diff', 0);

        $highlights[] = "Distribuição entre pares e ímpares: {$evenCount} pares e {$oddCount} ímpares.";

        if ($consecutiveCount > 0) {
            $highlights[] = "O concurso apresentou {$consecutiveCount} sequência(s) consecutiva(s).";
        } else {
            $highlights[] = 'O concurso não apresentou sequência consecutiva.';
        }

        if (abs($sumDiff) <= 5) {
            $highlights[] = 'A soma ficou muito próxima da média histórica.';
        } elseif ($sumDiff > 0) {
            $highlights[] = 'A soma ficou acima da média histórica.';
        } else {
            $highlights[] = 'A soma ficou abaixo da média histórica.';
        }

        if (abs($rangeDiff) <= 5) {
            $highlights[] = 'A amplitude ficou muito próxima da média histórica.';
        } elseif ($rangeDiff > 0) {
            $highlights[] = 'A amplitude ficou acima da média histórica.';
        } else {
            $highlights[] = 'A amplitude ficou abaixo da média histórica.';
        }

        $highlights[] = "Leitura direta: soma {$sum}, amplitude {$range}.";

        return $highlights;
    }
}