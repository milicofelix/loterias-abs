<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LotteryModality;
use App\Services\Lottery\RepeatedCombinationGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LotteryApiController extends Controller
{
    public function modalities(): JsonResponse
    {
        return response()->json([
            'data' => LotteryModality::query()
                ->where('is_active', true)
                ->withCount('draws')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function modality(LotteryModality $modality): JsonResponse
    {
        $latestDraw = $modality->draws()
            ->with('numbers')
            ->orderByDesc('contest_number')
            ->first();

        return response()->json([
            'data' => [
                'modality' => $modality,
                'draws_count' => $modality->draws()->count(),
                'latest_draw' => $latestDraw,
            ],
        ]);
    }

    public function history(Request $request, LotteryModality $modality): JsonResponse
    {
        $draws = $modality->draws()
            ->with('numbers')
            ->orderByDesc('contest_number')
            ->paginate($request->integer('per_page', 15));

        return response()->json($draws);
    }

    public function repeatedCombinations(
        LotteryModality $modality,
        RepeatedCombinationGatewayService $service
    ): JsonResponse {
        $result = $service->findRepeated($modality);

        return response()->json([
            'data' => $result['items'] ?? [],
            'meta' => $result['meta'] ?? null,
        ]);
    }
}