<?php

namespace App\Services\Lottery;

use App\Models\CombinationHistory;
use App\Models\LotteryModality;

class CombinationHistoryService
{
    /**
     * @param array<int,int> $numbers
     * @param array<string,mixed>|null $analysis
     */
    public function store(
        LotteryModality $modality,
        array $numbers,
        string $source,
        ?array $analysis = null
    ): CombinationHistory {
        sort($numbers);

        return CombinationHistory::create([
            'lottery_modality_id' => $modality->id,
            'numbers' => array_values($numbers),
            'source' => $source,
            'analysis_snapshot' => $analysis,
        ]);
    }
}