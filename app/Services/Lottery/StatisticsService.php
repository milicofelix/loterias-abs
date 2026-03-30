<?php

namespace App\Services\Lottery;

use App\Models\LotteryModality;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * @return array<int, int>
     */
    public function numberFrequencies(LotteryModality $modality, ?int $lastDraws = null): array
    {
        $drawsQuery = $modality->draws()
            ->orderByDesc('draw_date')
            ->orderByDesc('contest_number');

        if ($lastDraws !== null) {
            $drawsQuery->limit($lastDraws);
        }

        $drawIds = $drawsQuery->pluck('id');

        $frequencies = DB::table('draw_numbers')
            ->select('number', DB::raw('COUNT(*) as total'))
            ->whereIn('draw_id', $drawIds)
            ->groupBy('number')
            ->pluck('total', 'number')
            ->map(fn ($value) => (int) $value)
            ->all();

        $result = [];

        for ($number = $modality->min_number; $number <= $modality->max_number; $number++) {
            $result[$number] = $frequencies[$number] ?? 0;
        }

        ksort($result);

        return $result;
    }
}