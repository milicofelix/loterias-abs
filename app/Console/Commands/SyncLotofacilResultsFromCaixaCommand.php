<?php

namespace App\Console\Commands;

use App\Services\Lottery\CaixaResultsSyncService;
use Illuminate\Console\Command;

class SyncLotofacilResultsFromCaixaCommand extends Command
{
    protected $signature = 'lottery:sync-lotofacil';

    protected $description = 'Sincroniza os resultados da Lotofácil a partir da CAIXA';

    public function handle(CaixaResultsSyncService $syncService): int
    {
        try {
            $result = $syncService->syncLotofacil();

            $this->info(sprintf(
                'Sincronização concluída: %d novos, %d existentes, %d ignorados.',
                $result['imported'] ?? 0,
                $result['existing'] ?? 0,
                $result['skipped'] ?? 0,
            ));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Erro ao sincronizar resultados da Lotofácil pela CAIXA.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
