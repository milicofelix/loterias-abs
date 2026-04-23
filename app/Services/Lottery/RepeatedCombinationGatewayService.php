<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;

class RepeatedCombinationGatewayService
{
    public function __construct(
        protected LotteryEngineClient $client
    ) {
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function findRepeated(LotteryModality $modality): array
    {
        $draws = $modality->draws()
            ->with('numbers')
            ->orderBy('contest_number')
            ->get()
            ->map(function ($draw) {
                return [
                    'contest_number' => (int) $draw->contest_number,
                    'draw_date' => $draw->draw_date?->format('Y-m-d'),
                    'numbers' => $draw->numbers
                        ->pluck('number')
                        ->map(fn ($n) => (int) $n)
                        ->sort()
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $payload = [
            'modality' => [
                'code' => $modality->code,
                'min_number' => (int) $modality->min_number,
                'max_number' => (int) $modality->max_number,
                'draw_count' => (int) $modality->draw_count,
            ],
            'draws' => $draws,
        ];

        $response = $this->client->repeatedCombinations($payload);

        return [
            'items' => is_array($response['items'] ?? null) ? $response['items'] : [],
            'meta' => is_array($response['meta'] ?? null) ? $response['meta'] : null,
        ];
    }
}