<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;

class LotteryRulesService
{
    /**
     * @return array<int, int>
     */
    public function prizeHitRange(LotteryModality $modality): array
    {
        return match ($modality->code) {
            'quina' => [2, 3, 4, 5],
            'mega_sena' => [4, 5, 6],
            'lotofacil' => [11, 12, 13, 14, 15],
            'lotomania' => [16, 17, 18, 19, 20],
            default => range(max(2, (int) $modality->draw_count - 1), (int) $modality->draw_count),
        };
    }

    public function minimumPrizeHits(LotteryModality $modality): int
    {
        $range = $this->prizeHitRange($modality);

        return $range[0] ?? max(2, (int) $modality->draw_count);
    }

    public function isPrized(LotteryModality $modality, int $hits): bool
    {
        return in_array($hits, $this->prizeHitRange($modality), true);
    }

    public function prizeLabel(LotteryModality $modality, int $hits): ?string
    {
        if (! $this->isPrized($modality, $hits)) {
            return null;
        }

        return sprintf('%d acertos', $hits);
    }

    public function playInstruction(LotteryModality $modality): string
    {
        return match ($modality->code) {
            'lotofacil' => 'Você marca entre 15 e 20 números, dentre os 25 disponíveis, e fatura prêmio ao acertar 11, 12, 13, 14 ou 15 números.',
            'quina' => 'Escolha entre 5 e 15 números dentre os 80 disponíveis e concorra acertando 2, 3, 4 ou 5 números.',
            'mega_sena' => 'Marque entre 6 e 20 números dentre os 60 disponíveis e acompanhe a leitura histórica do seu jogo.',
            default => sprintf(
                'Marque entre %d e %d números, dentre os %d disponíveis.',
                (int) $modality->bet_min_count,
                (int) $modality->bet_max_count,
                ((int) $modality->max_number - (int) $modality->min_number) + 1
            ),
        };
    }

    public function supportsCaixaSpreadsheet(LotteryModality $modality): bool
    {
        return in_array($modality->code, ['quina', 'lotofacil'], true);
    }

    public function supportsCaixaSync(LotteryModality $modality): bool
    {
        return $this->supportsCaixaSpreadsheet($modality);
    }

    public function supportsSmartGeneration(LotteryModality $modality): bool
    {
        return in_array($modality->code, ['quina', 'lotofacil'], true);
    }

    /**
     * @return array<int, string>
     */
    public function caixaSheetCandidates(LotteryModality $modality): array
    {
        return match ($modality->code) {
            'lotofacil' => ['LOTOFACIL', 'LOTO FÁCIL', 'LOTO FACIL', 'LOTOFÁCIL'],
            'quina' => ['QUINA'],
            default => [mb_strtoupper($modality->name, 'UTF-8')],
        };
    }

    public function smartGenerationModalities(): array
    {
        return ['quina', 'lotofacil'];
    }

    public function usesExternalSmartEngine(LotteryModality $modality): bool
    {
        return in_array($modality->code, $this->smartGenerationModalities(), true);
    }
}
