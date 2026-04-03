<?php

namespace App\Console\Commands;

use App\Models\LotteryModality;
use App\Services\Lottery\Importers\QuinaSpreadsheetImporter;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ImportQuinaSpreadsheet extends Command
{
    protected $signature = 'lottery:import-quina {file : Caminho do arquivo XLSX da Quina}';

    protected $description = 'Importa resultados históricos da Quina a partir de um arquivo XLSX';

    public function handle(QuinaSpreadsheetImporter $importer): int
    {
        $file = $this->argument('file');

        if (!is_string($file) || !file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return self::FAILURE;
        }

        $modality = LotteryModality::query()
            ->where('code', 'quina')
            ->first();

        if (!$modality) {
            $this->error('Modalidade Quina não encontrada. Rode os seeders primeiro.');
            return self::FAILURE;
        }

        try {
            $result = $importer->import($file, $modality);

            $this->info('Importação concluída com sucesso.');
            $this->newLine();

            $this->table(
                ['Importados', 'Já existentes', 'Ignorados'],
                [[
                    $result['imported'] ?? 0,
                    $result['existing'] ?? 0,
                    $result['skipped'] ?? 0,
                ]]
            );

            return self::SUCCESS;
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Erro inesperado ao importar a planilha.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}