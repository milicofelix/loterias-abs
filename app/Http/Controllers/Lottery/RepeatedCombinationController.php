<?php

namespace App\Http\Controllers\Lottery;

use App\Http\Controllers\Controller;
use App\Models\LotteryModality;
use App\Services\Lottery\RepeatedCombinationGatewayService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RepeatedCombinationController extends Controller
{
    public function index(
        Request $request,
        LotteryModality $modality,
        RepeatedCombinationGatewayService $service
    ): Response {
        $result = $service->findRepeated($modality);

        return Inertia::render('Lottery/RepeatedCombinations', [
            'modality' => [
                'id' => $modality->id,
                'name' => $modality->name,
                'code' => $modality->code,
                'draw_count' => $modality->draw_count,
                'min_number' => $modality->min_number,
                'max_number' => $modality->max_number,
            ],
            'items' => $result['items'] ?? [],
            'meta' => $result['meta'] ?? null,
        ]);
    }
}