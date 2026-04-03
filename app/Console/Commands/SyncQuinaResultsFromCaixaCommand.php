<?php

namespace App\Console\Commands;

use App\Services\Lottery\CaixaResultsSyncService;
use Illuminate\Console\Command;

class SyncQuinaResultsFromCaixaCommand extends Command
{
    protected $signature = 'lottery:sync-quina';

    protected $description = 'Sincroniza os resultados da Quina a partir da CAIXA';

    public function handle(CaixaResultsSyncService $syncService): int
    {
        try {
            $result = $syncService->syncQuina();

            $this->info('Sincronização concluída com sucesso.');
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
        } catch (\Throwable $e) {
            $this->error('Erro ao sincronizar resultados da Quina pela CAIXA.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}