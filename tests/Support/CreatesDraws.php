<?php

namespace Tests\Support;

use App\Models\Draw;
use App\Models\DrawNumber;
use App\Models\LotteryModality;

trait CreatesDraws
{
    protected function createDrawWithNumbers(
        LotteryModality $modality,
        int $contestNumber,
        string $drawDate,
        array $numbers
    ): Draw {
        $draw = Draw::factory()->create([
            'lottery_modality_id' => $modality->id,
            'contest_number' => $contestNumber,
            'draw_date' => $drawDate,
        ]);

        foreach ($numbers as $number) {
            DrawNumber::create([
                'draw_id' => $draw->id,
                'number' => $number,
            ]);
        }

        return $draw;
    }
}