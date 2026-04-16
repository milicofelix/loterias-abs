<?php

namespace App\Services\Lottery;

use App\Models\Draw;
use App\Models\DrawNumber;
use App\Models\LotteryModality;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ManualDrawCreationService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function create(LotteryModality $modality, array $payload): Draw
    {
        $contestNumber = (int) ($payload['contest_number'] ?? 0);
        $drawDate = (string) ($payload['draw_date'] ?? '');
        $numbers = array_map(static fn ($number) => (int) $number, (array) ($payload['numbers'] ?? []));
        $observation = trim((string) ($payload['observation'] ?? ''));

        $this->validate($modality, $contestNumber, $numbers);

        return DB::transaction(function () use ($modality, $contestNumber, $drawDate, $numbers, $observation) {
            $alreadyExists = Draw::query()
                ->where('lottery_modality_id', $modality->id)
                ->where('contest_number', $contestNumber)
                ->exists();

            if ($alreadyExists) {
                throw new InvalidArgumentException("Já existe um resultado cadastrado para o concurso {$contestNumber}.");
            }

            $metadata = array_filter([
                'source' => 'manual_dashboard',
                'created_manually' => true,
                'observation' => $observation !== '' ? $observation : null,
            ], static fn ($value) => $value !== null);

            $draw = Draw::create([
                'lottery_modality_id' => $modality->id,
                'contest_number' => $contestNumber,
                'draw_date' => $drawDate,
                'metadata' => $metadata,
            ]);

            foreach ($numbers as $number) {
                DrawNumber::create([
                    'draw_id' => $draw->id,
                    'number' => $number,
                ]);
            }

            return $draw;
        });
    }

    /**
     * @param array<int, int> $numbers
     */
    protected function validate(LotteryModality $modality, int $contestNumber, array $numbers): void
    {
        if ($contestNumber < 1) {
            throw new InvalidArgumentException('Informe um número de concurso válido.');
        }

        if (count($numbers) !== (int) $modality->draw_count) {
            throw new InvalidArgumentException(sprintf(
                '%s exige exatamente %d números no resultado oficial.',
                $modality->name,
                $modality->draw_count,
            ));
        }

        if (! $modality->allows_repetition && count(array_unique($numbers)) !== count($numbers)) {
            throw new InvalidArgumentException('O resultado informado possui números repetidos.');
        }

        foreach ($numbers as $number) {
            if ($number < (int) $modality->min_number || $number > (int) $modality->max_number) {
                throw new InvalidArgumentException(sprintf(
                    'Os números devem estar entre %d e %d para %s.',
                    $modality->min_number,
                    $modality->max_number,
                    $modality->name,
                ));
            }
        }
    }
}
