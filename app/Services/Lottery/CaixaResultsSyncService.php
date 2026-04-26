<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use App\Services\Lottery\Importers\CaixaSpreadsheetImporter;
use InvalidArgumentException;
use RuntimeException;

class CaixaResultsSyncService
{
    public function __construct(
        protected CaixaResultsDownloaderService $downloader,
        protected CaixaSpreadsheetImporter $spreadsheetImporter,
        protected LotteryRulesService $rulesService,
    ) {
    }

    public function sync(LotteryModality $modality): array
    {
        if (! $this->rulesService->supportsCaixaSync($modality)) {
            throw new InvalidArgumentException("A sincronização automática pela CAIXA ainda não está disponível para {$modality->name}.");
        }

        $filePath = $this->downloader->downloadSpreadsheet($modality->name);

        try {
            $result = $this->spreadsheetImporter->import($filePath, $modality);
        } finally {
            if (is_string($filePath) && file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return [
            'modality' => $modality->name,
            'source' => 'caixa',
            'imported' => $result['imported'] ?? 0,
            'existing' => $result['existing'] ?? 0,
            'skipped' => $result['skipped'] ?? 0,
        ];
    }

    public function syncQuina(): array
    {
        return $this->syncByCode('quina');
    }

    public function syncLotofacil(): array
    {
        return $this->syncByCode('lotofacil');
    }

    public function syncMegaSena(): array
    {
        return $this->syncByCode('mega_sena');
    }

    protected function syncByCode(string $code): array
    {
        $modality = LotteryModality::query()
            ->where('code', $code)
            ->first();

        if (! $modality) {
            throw new RuntimeException("Modalidade {$code} não encontrada. Rode os seeders primeiro.");
        }

        return $this->sync($modality);
    }
}
