<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use InvalidArgumentException;

class SmartGameGeneratorGatewayService
{
    public function __construct(
        protected LotteryEngineClient $client,
        protected StatisticsService $statisticsService,
        protected DelayAnalysisService $delayAnalysisService,
        protected HistoricalProfileComparisonService $historicalProfileComparisonService,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function generate(LotteryModality $modality, array $options = []): array
    {
        $strategy = (string) ($options['strategy'] ?? 'balanced');
        $games = (int) ($options['games'] ?? 5);
        $candidatePool = (int) ($options['candidate_pool'] ?? 2000);

        $this->validateOptions($modality, $strategy, $games, $candidatePool);

        $frequencies = $this->statisticsService->numberFrequencies($modality);
        $delays = $this->delayAnalysisService->numberDelays($modality);

        $historical = $this->historicalProfileComparisonService->compare(
            $modality,
            range(
                (int) $modality->min_number,
                (int) $modality->min_number + (int) $modality->draw_count - 1
            )
        );

        $historicalAverages = $historical['historical_averages'] ?? [
            'sum' => 0,
            'range' => 0,
        ];

        $payload = [
            'modality' => [
                'code' => $modality->code,
                'min_number' => (int) $modality->min_number,
                'max_number' => (int) $modality->max_number,
                'draw_count' => (int) $modality->draw_count,
            ],
            'strategy' => $strategy,
            'games' => $games,
            'candidate_pool' => $candidatePool,
            'stats' => [
                'frequencies' => $frequencies,
                'delays' => $delays,
                'historical_averages' => [
                    'sum' => (float) ($historicalAverages['sum'] ?? 0),
                    'range' => (float) ($historicalAverages['range'] ?? 0),
                ],
            ],
        ];

        $response = $this->client->generateSmart($payload);

        $gamesPayload = $response['games'] ?? [];

        return is_array($gamesPayload) ? $gamesPayload : [];
    }

    protected function validateOptions(
        LotteryModality $modality,
        string $strategy,
        int $games,
        int $candidatePool
    ): void {
        if ($modality->code !== 'quina') {
            throw new InvalidArgumentException('A geração inteligente está disponível apenas para a Quina nesta etapa.');
        }

        if (! in_array($strategy, ['balanced', 'hot'], true)) {
            throw new InvalidArgumentException('Estratégia inválida. Utilize balanced ou hot.');
        }

        if ($games < 1 || $games > 20) {
            throw new InvalidArgumentException('A quantidade de jogos deve estar entre 1 e 20.');
        }

        if ($candidatePool < 40 || $candidatePool > 20000) {
            throw new InvalidArgumentException('O volume de candidatos deve estar entre 40 e 20000.');
        }
    }
}