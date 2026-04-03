<?php

namespace App\Http\Controllers\Lottery;

use App\Http\Controllers\Controller;
use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function play(Request $request, LotteryModality $modality)
    {
        $prefilledNumbers = [];

        $historyId = $request->integer('history_id');

        if ($historyId) {
            $historyItem = CombinationHistory::query()
                ->where('lottery_modality_id', $modality->id)
                ->find($historyId);

            if ($historyItem) {
                $prefilledNumbers = collect($historyItem->numbers)
                    ->map(fn ($value) => (int) $value)
                    ->filter(fn ($value) => $value > 0)
                    ->values()
                    ->all();
            }
        }

        if (empty($prefilledNumbers)) {
            $prefilledNumbers = collect(
                explode(',', (string) $request->get('numbers', ''))
            )
                ->map(fn ($value) => trim($value))
                ->filter(fn ($value) => $value !== '')
                ->map(fn ($value) => (int) $value)
                ->filter(fn ($value) => $value > 0)
                ->values()
                ->all();
        }

        return Inertia::render('Lottery/Play', [
            'modality' => $modality,
            'prefilledNumbers' => $prefilledNumbers,
        ]);
    }
}