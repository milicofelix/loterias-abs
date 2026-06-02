<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LotterySyncStatusStore
{
    protected int $ttlSeconds = 3600;

    public function create(LotteryModality $modality, int $userId): string
    {
        $id = (string) Str::uuid();

        $this->put($id, [
            'id' => $id,
            'status' => 'queued',
            'message' => 'Sincronização agendada.',
            'modality_id' => $modality->id,
            'modality_name' => $modality->name,
            'user_id' => $userId,
            'result' => null,
            'error' => null,
            'started_at' => null,
            'finished_at' => null,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ]);

        return $id;
    }

    public function markRunning(string $id): void
    {
        $this->merge($id, [
            'status' => 'running',
            'message' => 'Baixando e importando resultados da CAIXA.',
            'started_at' => now()->toISOString(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function markSucceeded(string $id, array $result): void
    {
        $this->merge($id, [
            'status' => 'succeeded',
            'message' => sprintf(
                'Sincronização concluída: %d novos, %d já existentes, %d ignorados.',
                $result['imported'] ?? 0,
                $result['existing'] ?? 0,
                $result['skipped'] ?? 0,
            ),
            'result' => $result,
            'finished_at' => now()->toISOString(),
        ]);
    }

    public function markFailed(string $id, string $message): void
    {
        $this->merge($id, [
            'status' => 'failed',
            'message' => $message,
            'error' => $message,
            'finished_at' => now()->toISOString(),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $id): ?array
    {
        $status = Cache::get($this->key($id));

        return is_array($status) ? $status : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function merge(string $id, array $payload): void
    {
        $current = $this->get($id) ?? ['id' => $id];

        $this->put($id, array_merge($current, $payload, [
            'updated_at' => now()->toISOString(),
        ]));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function put(string $id, array $payload): void
    {
        Cache::put($this->key($id), $payload, now()->addSeconds($this->ttlSeconds));
    }

    protected function key(string $id): string
    {
        return "lottery-sync:{$id}";
    }
}
