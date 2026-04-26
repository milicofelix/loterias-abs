<?php

namespace App\Console\Commands;

use App\Services\Lottery\CaixaResultsSyncService;
use Illuminate\Console\Command;

class SyncMegaSenaResultsFromCaixaCommand extends Command
{
    protected $signature = 'lottery:sync-mega-sena';

    protected $description = 'Sincroniza os resultados da Mega-Sena a partir da CAIXA';

    public function handle(CaixaResultsSyncService $syncService): int
    {
        try {
            $result = $syncService->syncMegaSena();

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
            $this->error('Erro ao sincronizar resultados da Mega-Sena pela CAIXA.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
