<?php

namespace App\Services\Lottery\Importers;

use App\Models\Draw;
use App\Models\DrawNumber;
use App\Models\LotteryModality;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class QuinaSpreadsheetImporter
{
    public function import(string $filePath, LotteryModality $modality): array
    {
        $spreadsheet = IOFactory::load($filePath);

        $sheet = $spreadsheet->getSheetByName('QUINA');

        if (!$sheet) {
            throw new InvalidArgumentException('A aba QUINA não foi encontrada no arquivo.');
        }

        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 1) {
            return ['imported' => 0, 'existing' => 0, 'skipped' => 0];
        }

        $headers = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $rows[0]);

        $requiredColumns = [
            'Concurso',
            'Data Sorteio',
            'Bola1',
            'Bola2',
            'Bola3',
            'Bola4',
            'Bola5',
        ];

        foreach ($requiredColumns as $column) {
            if (!in_array($column, $headers, true)) {
                throw new InvalidArgumentException("Coluna obrigatória ausente: {$column}");
            }
        }

        $imported = 0;
        $existing = 0;
        $skipped = 0;

        foreach (array_slice($rows, 1) as $row) {
            $mapped = $this->mapRow($headers, $row);

            if ($this->isEmptyRow($mapped)) {
                $skipped++;
                continue;
            }

            $contestNumber = (int) $mapped['Concurso'];
            $drawDate = $this->parseDate($mapped['Data Sorteio']);
            $numbers = [
                (int) $mapped['Bola1'],
                (int) $mapped['Bola2'],
                (int) $mapped['Bola3'],
                (int) $mapped['Bola4'],
                (int) $mapped['Bola5'],
            ];

            $this->validateNumbers($numbers, $modality);

            $metadata = $mapped;
            unset(
                $metadata['Concurso'],
                $metadata['Data Sorteio'],
                $metadata['Bola1'],
                $metadata['Bola2'],
                $metadata['Bola3'],
                $metadata['Bola4'],
                $metadata['Bola5'],
            );

            DB::transaction(function () use (
                $modality,
                $contestNumber,
                $drawDate,
                $metadata,
                $numbers,
                &$imported,
                &$existing
            ) {
                $alreadyExists = Draw::query()
                    ->where('lottery_modality_id', $modality->id)
                    ->where('contest_number', $contestNumber)
                    ->exists();

                if ($alreadyExists) {
                    $existing++;
                    return;
                }

                $draw = Draw::create([
                    'lottery_modality_id' => $modality->id,
                    'contest_number' => $contestNumber,
                    'draw_date' => $drawDate->format('Y-m-d'),
                    'metadata' => $metadata,
                ]);

                foreach ($numbers as $number) {
                    DrawNumber::create([
                        'draw_id' => $draw->id,
                        'number' => $number,
                    ]);
                }

                $imported++;
            });
        }

        return [
            'imported' => $imported,
            'existing' => $existing,
            'skipped' => $skipped,
        ];
    }

    protected function mapRow(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            if ($header === null || $header === '') {
                continue;
            }

            $mapped[$header] = $row[$index] ?? null;
        }

        return $mapped;
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    protected function parseDate(mixed $value): Carbon
    {
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
        }

        if (is_string($value)) {
            return Carbon::createFromFormat('d/m/Y', trim($value));
        }

        throw new InvalidArgumentException('Data Sorteio inválida.');
    }

    protected function validateNumbers(array $numbers, LotteryModality $modality): void
    {
        if (count($numbers) !== 5) {
            throw new InvalidArgumentException('A linha da Quina deve conter 5 bolas.');
        }

        if (count(array_unique($numbers)) !== 5) {
            throw new InvalidArgumentException('A linha possui bolas repetidas.');
        }

        foreach ($numbers as $number) {
            if ($number < $modality->min_number || $number > $modality->max_number) {
                throw new InvalidArgumentException(
                    "Número fora do intervalo permitido para {$modality->name}."
                );
            }
        }
    }
}