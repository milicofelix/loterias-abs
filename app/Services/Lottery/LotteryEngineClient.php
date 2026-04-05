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
            throw new RuntimeException('Falha ao comunicar com o motor de geração.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Resposta inválida do motor de geração.');
        }

        return $data;
    }
}