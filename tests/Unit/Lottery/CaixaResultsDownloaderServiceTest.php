<?php

use App\Services\Lottery\CaixaResultsDownloaderService;
use Illuminate\Support\Facades\Http;

it('resolves api base url from caixa params file', function () {
    Http::fake([
        'https://loterias.caixa.gov.br/Style%20Library/json/params.txt' => Http::response([
            'urlapiloterias' => 'https://api.example.test',
        ]),
    ]);

    $url = app(CaixaResultsDownloaderService::class)->resolveApiBaseUrl();

    expect($url)->toBe('https://api.example.test');
});

it('downloads official spreadsheet to a temporary file', function () {
    Http::fake([
        'https://loterias.caixa.gov.br/Style%20Library/json/params.txt' => Http::response([
            'urlapiloterias' => 'https://api.example.test',
        ]),
        'https://api.example.test/api/resultados/download?modalidade=Quina' => Http::response('fake-xlsx-content', 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]),
    ]);

    $path = app(CaixaResultsDownloaderService::class)->downloadSpreadsheet('Quina');

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toBe('fake-xlsx-content');

    @unlink($path);
});
