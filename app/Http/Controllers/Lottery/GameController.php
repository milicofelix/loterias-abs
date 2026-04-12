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
        $historyItem = null;

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

        $latestDraw = $modality->draws()
            ->with('numbers')
            ->orderByDesc('contest_number')
            ->first();

        return Inertia::render('Lottery/Play', [
            'modality' => $modality,
            'prefilledNumbers' => $prefilledNumbers,
            'historyItem' => $historyItem ? [
                'id' => $historyItem->id,
                'bet_contest_number' => $historyItem->bet_contest_number,
                'bet_registered_at' => $historyItem->bet_registered_at?->format('d/m/Y H:i'),
                'bet_checked_at' => $historyItem->bet_checked_at?->format('d/m/Y H:i'),
                'bet_result_snapshot' => $historyItem->bet_result_snapshot,
            ] : null,
            'latestDraw' => $latestDraw ? [
                'contest_number' => $latestDraw->contest_number,
                'draw_date' => $latestDraw->draw_date?->format('d/m/Y'),
                'numbers' => $latestDraw->numbers
                    ->pluck('number')
                    ->map(fn ($number) => (int) $number)
                    ->sort()
                    ->values()
                    ->all(),
            ] : null,
        ]);
    }
}