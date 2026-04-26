<?php

namespace App\Services\Lottery;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class LotteryEngineClient
{
    public function generateSmart(array $payload): array
    {
        $baseUrl = rtrim((string) config('services.lottery_engine.url'), '/');
        $timeout = (int) config('services.lottery_engine.timeout', 120);

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->post($baseUrl . '/v1/generate-smart', $payload);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'Falha ao comunicar com o motor de geração. HTTP %s: %s',
                $response->status(),
                mb_substr((string) $response->body(), 0, 1000)
            ));
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Resposta inválida do motor de geração.');
        }

        return $data;
    }

    public function repeatedCombinations(array $payload): array
    {
        $baseUrl = rtrim((string) config('services.lottery_engine.url'), '/');
        $timeout = (int) config('services.lottery_engine.timeout', 120);

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->post($baseUrl . '/v1/repeated-combinations', $payload);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'Falha ao comunicar com o motor de combinações repetidas. HTTP %s: %s',
                $response->status(),
                mb_substr((string) $response->body(), 0, 1000)
            ));
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Resposta inválida do motor de combinações repetidas.');
        }

        return $data;
    }
}