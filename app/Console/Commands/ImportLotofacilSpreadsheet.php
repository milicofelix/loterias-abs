<?php

namespace App\Console\Commands;

use App\Models\LotteryModality;
use App\Services\Lottery\Importers\CaixaSpreadsheetImporter;
use Illuminate\Console\Command;

class ImportLotofacilSpreadsheet extends Command
{
    protected $signature = 'lottery:import-lotofacil {file : Caminho do arquivo XLSX da Lotofácil}';

    protected $description = 'Importa resultados históricos da Lotofácil a partir de um arquivo XLSX';

    public function handle(CaixaSpreadsheetImporter $importer): int
    {
        $modality = LotteryModality::query()->where('code', 'lotofacil')->first();

        if (! $modality) {
            $this->error('Modalidade Lotofácil não encontrada. Rode os seeders primeiro.');
            return self::FAILURE;
        }

        $result = $importer->import((string) $this->argument('file'), $modality);

        $this->info(sprintf(
            'Importação concluída: %d novos, %d existentes, %d ignorados.',
            $result['imported'] ?? 0,
            $result['existing'] ?? 0,
            $result['skipped'] ?? 0,
        ));

        return self::SUCCESS;
    }
}
