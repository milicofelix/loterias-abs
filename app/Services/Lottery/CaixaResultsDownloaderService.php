<?php

namespace App\Services\Lottery;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CaixaResultsDownloaderService
{
    public const PARAMS_URL = 'https://loterias.caixa.gov.br/Style%20Library/json/params.txt';

    protected function makeRequest(int $timeout): PendingRequest
    {
        $request = Http::timeout($timeout);

        if (app()->environment('local')) {
            $request = $request->withOptions([
                'verify' => false,
            ]);
        }

        return $request;
    }

    public function resolveApiBaseUrl(): string
    {
        $response = $this->makeRequest(30)->get(self::PARAMS_URL);

        if (! $response->successful()) {
            throw new RuntimeException('Não foi possível obter os parâmetros da CAIXA.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            $payload = json_decode($response->body(), true);
        }

        $urlApi = is_array($payload) ? ($payload['urlapiloterias'] ?? null) : null;

        if (! is_string($urlApi) || trim($urlApi) === '') {
            throw new RuntimeException('Parâmetro urlapiloterias não encontrado na resposta da CAIXA.');
        }

        return rtrim(trim($urlApi), '/');
    }

    public function downloadSpreadsheet(string $modalidade): string
    {
        $urlApi = $this->resolveApiBaseUrl();

        $response = $this->makeRequest(60)
            ->accept('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->get($urlApi . '/api/resultados/download', [
                'modalidade' => $modalidade,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("Não foi possível baixar a planilha da modalidade {$modalidade}.");
        }

        $directory = storage_path('app/tmp/lottery-sync');

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = sprintf('%s-%s.xlsx', str($modalidade)->slug()->value(), now()->format('YmdHis'));
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($path, $response->body());

        return $path;
    }
}