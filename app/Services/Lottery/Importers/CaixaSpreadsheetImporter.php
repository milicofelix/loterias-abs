<?php

namespace App\Services\Lottery\Importers;

use App\Models\Draw;
use App\Models\DrawNumber;
use App\Models\LotteryModality;
use App\Services\Lottery\LotteryRulesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CaixaSpreadsheetImporter
{
    public function __construct(
        protected LotteryRulesService $rulesService,
    ) {
    }

    public function import(string $filePath, LotteryModality $modality): array
    {
        if (! $this->rulesService->supportsCaixaSpreadsheet($modality)) {
            throw new InvalidArgumentException("A importação por planilha ainda não está disponível para {$modality->name}.");
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $this->resolveSheet($spreadsheet->getAllSheets(), $modality);

        if (! $sheet) {
            throw new InvalidArgumentException("A aba da modalidade {$modality->name} não foi encontrada no arquivo.");
        }

        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 1) {
            return ['imported' => 0, 'existing' => 0, 'skipped' => 0];
        }

        $headers = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $rows[0]);
        $ballColumns = $this->requiredBallColumns($modality);
        $requiredColumns = array_merge(['Concurso', 'Data Sorteio'], $ballColumns);

        foreach ($requiredColumns as $column) {
            if (! in_array($column, $headers, true)) {
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

            $contestNumber = (int) ($mapped['Concurso'] ?? 0);

            if ($contestNumber <= 0) {
                $skipped++;
                continue;
            }

            $drawDate = $this->parseDate($mapped['Data Sorteio'] ?? null);
            $numbers = array_map(static fn (string $column) => (int) ($mapped[$column] ?? 0), $ballColumns);

            $this->validateNumbers($numbers, $modality);

            $metadata = $mapped;
            unset($metadata['Concurso'], $metadata['Data Sorteio']);
            foreach ($ballColumns as $column) {
                unset($metadata[$column]);
            }

            DB::transaction(function () use (
                $modality,
                $contestNumber,
                $drawDate,
                $metadata,
                $numbers,
                &$imported,
                &$existing,
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

    /**
     * @param array<int, Worksheet> $sheets
     */
    protected function resolveSheet(array $sheets, LotteryModality $modality): ?Worksheet
    {
        $candidates = array_map([$this, 'normalizeSheetName'], $this->rulesService->caixaSheetCandidates($modality));

        foreach ($sheets as $sheet) {
            $normalized = $this->normalizeSheetName((string) $sheet->getTitle());

            if (in_array($normalized, $candidates, true)) {
                return $sheet;
            }
        }

        return null;
    }

    protected function normalizeSheetName(string $value): string
    {
        $value = trim($value);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = $ascii !== false ? $ascii : $value;

        return strtoupper(preg_replace('/\s+/', '', $ascii) ?? $ascii);
    }

    /**
     * @return array<int, string>
     */
    protected function requiredBallColumns(LotteryModality $modality): array
    {
        $columns = [];

        for ($index = 1; $index <= (int) $modality->draw_count; $index++) {
            $columns[] = 'Bola' . $index;
        }

        return $columns;
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

        if (is_string($value) && trim($value) !== '') {
            return Carbon::createFromFormat('d/m/Y', trim($value));
        }

        throw new InvalidArgumentException('Data Sorteio inválida.');
    }

    protected function validateNumbers(array $numbers, LotteryModality $modality): void
    {
        $drawCount = (int) $modality->draw_count;

        if (count($numbers) !== $drawCount) {
            throw new InvalidArgumentException("A linha de {$modality->name} deve conter {$drawCount} bolas.");
        }

        if (count(array_unique($numbers)) !== $drawCount) {
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
