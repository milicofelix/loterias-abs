<?php

namespace App\Services\Lottery\Agents;

use App\Models\LotteryModality;

class DashboardNarratorAgentService
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function narrate(LotteryModality $modality, array $context): array
    {
        return [
            'name' => 'dashboard-narrator',
            'version' => 1,
            'headline' => $this->buildHeadline($modality, $context),
            'highlights' => $this->buildHighlights($context),
            'summary' => $this->buildSummary($modality, $context),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function buildHeadline(LotteryModality $modality, array $context): string
    {
        $window = (int) ($context['window'] ?? 0);
        $topRecentNumbers = (array) ($context['top_recent_numbers'] ?? []);
        $topDelayedNumbers = (array) ($context['top_delayed_numbers'] ?? []);

        if (! empty($topRecentNumbers) && ! empty($topDelayedNumbers)) {
            return "Nos últimos {$window} concursos da {$modality->name}, há destaques simultâneos de frequência recente e atraso acumulado.";
        }

        if (! empty($topRecentNumbers)) {
            return "Nos últimos {$window} concursos da {$modality->name}, alguns números se destacaram em frequência recente.";
        }

        if (! empty($topDelayedNumbers)) {
            return "Nos últimos {$window} concursos da {$modality->name}, os maiores destaques atuais estão nos atrasos.";
        }

        return "Resumo analítico recente da {$modality->name}.";
    }

    /**
     * @param array<string, mixed> $context
     * @return array<int, string>
     */
    protected function buildHighlights(array $context): array
    {
        $highlights = [];

        $window = (int) ($context['window'] ?? 0);
        $topRecentNumbers = array_values((array) ($context['top_recent_numbers'] ?? []));
        $topDelayedNumbers = array_values((array) ($context['top_delayed_numbers'] ?? []));
        $recentAverageSum = $context['recent_average_sum'] ?? null;
        $historicalAverageSum = $context['historical_average_sum'] ?? null;

        if (! empty($topRecentNumbers)) {
            $numbers = implode(', ', array_slice($topRecentNumbers, 0, 3));
            $highlights[] = "Na janela recente de {$window} concursos, os números {$numbers} aparecem entre os principais destaques de frequência.";
        }

        if (! empty($topDelayedNumbers)) {
            $numbers = implode(', ', array_slice($topDelayedNumbers, 0, 3));
            $highlights[] = "Os atrasos mais altos do momento se concentram em {$numbers}.";
        }

        if ($recentAverageSum !== null && $historicalAverageSum !== null) {
            $diff = (float) $recentAverageSum - (float) $historicalAverageSum;

            if (abs($diff) <= 5) {
                $highlights[] = 'A soma média recente está muito próxima da média histórica.';
            } elseif ($diff > 0) {
                $highlights[] = 'A soma média recente está acima da média histórica.';
            } else {
                $highlights[] = 'A soma média recente está abaixo da média histórica.';
            }
        }

        return $highlights;
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function buildSummary(LotteryModality $modality, array $context): string
    {
        $window = (int) ($context['window'] ?? 0);
        $topRecentNumbers = array_values((array) ($context['top_recent_numbers'] ?? []));
        $topDelayedNumbers = array_values((array) ($context['top_delayed_numbers'] ?? []));

        $parts = [];
        $parts[] = "Leitura automática da {$modality->name} considerando os últimos {$window} concursos.";

        if (! empty($topRecentNumbers)) {
            $parts[] = 'Frequência recente em destaque: ' . implode(', ', array_slice($topRecentNumbers, 0, 5)) . '.';
        }

        if (! empty($topDelayedNumbers)) {
            $parts[] = 'Atrasos mais relevantes: ' . implode(', ', array_slice($topDelayedNumbers, 0, 5)) . '.';
        }

        return implode(' ', $parts);
    }
}