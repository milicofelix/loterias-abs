<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use Illuminate\Support\Facades\DB;

class DelayAnalysisService
{
    /**
     * @return array<int, int>
     */
    public function numberDelays(LotteryModality $modality, ?int $lastDraws = null): array
    {
        $drawsQuery = $modality->draws()
            ->orderByDesc('draw_date')
            ->orderByDesc('contest_number');

        if ($lastDraws !== null) {
            $drawsQuery->limit($lastDraws);
        }

        $drawIds = $drawsQuery->pluck('id')->values()->all();
        $totalDraws = count($drawIds);

        $result = [];

        for ($number = $modality->min_number; $number <= $modality->max_number; $number++) {
            $result[$number] = 0;
        }

        if ($totalDraws === 0) {
            return $result;
        }

        $drawIndexMap = [];
        foreach ($drawIds as $index => $drawId) {
            $drawIndexMap[$drawId] = $index;
        }

        $rows = DB::table('draw_numbers')
            ->select('draw_id', 'number')
            ->whereIn('draw_id', $drawIds)
            ->get();

        $firstOccurrenceIndexByNumber = [];

        foreach ($rows as $row) {
            $number = (int) $row->number;
            $index = $drawIndexMap[$row->draw_id] ?? null;

            if ($index === null) {
                continue;
            }

            if (!array_key_exists($number, $firstOccurrenceIndexByNumber)) {
                $firstOccurrenceIndexByNumber[$number] = $index;
            } else {
                $firstOccurrenceIndexByNumber[$number] = min(
                    $firstOccurrenceIndexByNumber[$number],
                    $index
                );
            }
        }

        for ($number = $modality->min_number; $number <= $modality->max_number; $number++) {
            $result[$number] = $firstOccurrenceIndexByNumber[$number] ?? $totalDraws;
        }

        ksort($result);

        return $result;
    }
}