<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use App\Services\Lottery\Importers\QuinaSpreadsheetImporter;
use InvalidArgumentException;
use RuntimeException;

class CaixaResultsSyncService
{
    public function __construct(
        protected CaixaResultsDownloaderService $downloader,
        protected QuinaSpreadsheetImporter $quinaImporter,
    ) {
    }

    public function sync(LotteryModality $modality): array
    {
        if ($modality->code !== 'quina') {
            throw new InvalidArgumentException('A sincronização automática pela CAIXA está disponível apenas para a Quina nesta etapa.');
        }

        $filePath = $this->downloader->downloadSpreadsheet($modality->name);

        try {
            $result = $this->quinaImporter->import($filePath, $modality);
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
        $modality = LotteryModality::query()
            ->where('code', 'quina')
            ->first();

        if (! $modality) {
            throw new RuntimeException('Modalidade Quina não encontrada. Rode os seeders primeiro.');
        }

        return $this->sync($modality);
    }
}
