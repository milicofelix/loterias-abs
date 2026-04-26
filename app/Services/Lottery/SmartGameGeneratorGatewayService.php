<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use InvalidArgumentException;

class SmartGameGeneratorGatewayService
{
    protected LotteryEngineClient $client;
    protected StatisticsService $statisticsService;
    protected DelayAnalysisService $delayAnalysisService;
    protected HistoricalProfileComparisonService $historicalProfileComparisonService;
    protected LotteryRulesService $rulesService;

    public function __construct(
        LotteryEngineClient $client,
        StatisticsService $statisticsService,
        DelayAnalysisService $delayAnalysisService,
        HistoricalProfileComparisonService $historicalProfileComparisonService,
        LotteryRulesService $rulesService
    ) {
        $this->client = $client;
        $this->statisticsService = $statisticsService;
        $this->delayAnalysisService = $delayAnalysisService;
        $this->historicalProfileComparisonService = $historicalProfileComparisonService;
        $this->rulesService = $rulesService;
    }

    /**
     * @return array{games: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function generateWithMeta(LotteryModality $modality, array $options = []): array
    {
        $strategy = (string) ($options['strategy'] ?? 'balanced');
        $games = (int) ($options['games'] ?? 5);
        $modalityCode = str_replace('-', '_', strtolower(trim((string) $modality->code)));
        $candidatePool = (int) ($options['candidate_pool'] ?? match ($modalityCode) {
            'lotofacil' => 4000,
            'mega_sena' => 3500,
            'quina' => 2000,
            default => 2000,
        });
        $minScore = (int) ($options['min_score'] ?? 0);

        $this->validateOptions($modality, $strategy, $games, $candidatePool, $minScore);

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
                'code' => $modalityCode,
                'min_number' => (int) $modality->min_number,
                'max_number' => (int) $modality->max_number,
                'draw_count' => (int) $modality->draw_count,
            ],
            'strategy' => $strategy,
            'games' => $games,
            'candidate_pool' => $candidatePool,
            'min_score' => $minScore,
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

        return [
            'games' => is_array($response['games'] ?? null) ? $response['games'] : [],
            'meta' => is_array($response['meta'] ?? null) ? $response['meta'] : null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function generate(LotteryModality $modality, array $options = []): array
    {
        $result = $this->generateWithMeta($modality, $options);

        return $result['games'];
    }

    protected function validateOptions(
        LotteryModality $modality,
        string $strategy,
        int $games,
        int $candidatePool,
        int $minScore
    ): void {
        if (! $this->rulesService->usesExternalSmartEngine($modality)) {
            throw new InvalidArgumentException("A engine externa não está habilitada para {$modality->name}.");
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

        if ($minScore < 0 || $minScore > 100) {
            throw new InvalidArgumentException('O score mínimo deve estar entre 0 e 100.');
        }
    }
}