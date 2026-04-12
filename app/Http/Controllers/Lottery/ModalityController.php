<?php

namespace App\Http\Controllers\Lottery;

use App\Http\Controllers\Controller;
use App\Models\LotteryModality;
use App\Services\Lottery\Agents\CombinationAnalysisAgentService;
use App\Services\Lottery\Agents\DashboardNarratorAgentService;
use App\Services\Lottery\Agents\DrawExplainerAgentService;
use App\Services\Lottery\CaixaResultsSyncService;
use App\Services\Lottery\CombinationGeneratorService;
use App\Services\Lottery\CombinationHistoryService;
use App\Services\Lottery\DelayAnalysisService;
use App\Services\Lottery\StatisticsService;
use App\Services\Lottery\Importers\QuinaSpreadsheetImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use App\Services\Lottery\SmartGameGeneratorGatewayService;

class ModalityController extends Controller
{
    public function index()
    {
        return Inertia::render('Lottery/Modalities/Index', [
            'modalities' => LotteryModality::query()
                ->where('is_active', true)
                ->get(),
        ]);
    }

    public function show(
        LotteryModality $modality,
        StatisticsService $stats,
        DelayAnalysisService $delay,
        DashboardNarratorAgentService $dashboardNarratorAgent,
        DrawExplainerAgentService $drawExplainerAgent
    ) {
        $recentWindow = 20;

        $recentDraws = $modality->draws()
            ->with('numbers')
            ->orderByDesc('contest_number')
            ->limit(20)
            ->get()
            ->map(function ($draw) {
                return [
                    'id' => $draw->id,
                    'contest_number' => $draw->contest_number,
                    'draw_date' => $draw->draw_date?->format('d/m/Y'),
                    'numbers' => $draw->numbers
                        ->pluck('number')
                        ->sort()
                        ->values()
                        ->all(),
                    'metadata' => $draw->metadata,
                ];
            })
            ->values();

        $totalDraws = $modality->draws()->count();

        $latestDraw = $modality->draws()
            ->orderByDesc('contest_number')
            ->first();

        $latestDrawForExplanation = $modality->draws()
            ->with('numbers')
            ->orderByDesc('contest_number')
            ->first();

        $latestDrawExplanation = $latestDrawForExplanation
            ? $drawExplainerAgent->explain($modality, $latestDrawForExplanation)
            : null;

        $frequencies = $stats->numberFrequencies($modality);
        $delays = $delay->numberDelays($modality);

        $frequenciesLast10 = $stats->numberFrequencies($modality, lastDraws: 10);
        $frequenciesLast20 = $stats->numberFrequencies($modality, lastDraws: 20);
        $frequenciesLast50 = $stats->numberFrequencies($modality, lastDraws: 50);

        $delaysLast10 = $delay->numberDelays($modality, lastDraws: 10);
        $delaysLast20 = $delay->numberDelays($modality, lastDraws: 20);
        $delaysLast50 = $delay->numberDelays($modality, lastDraws: 50);

        $topRecentNumbers = collect($frequenciesLast20)
            ->sortDesc()
            ->keys()
            ->take(5)
            ->map(fn ($number) => (int) $number)
            ->values()
            ->all();

        $topDelayedNumbers = collect($delays)
            ->sortDesc()
            ->keys()
            ->take(5)
            ->map(fn ($number) => (int) $number)
            ->values()
            ->all();

        $historicalAverageSum = $modality->draws()
            ->with('numbers')
            ->get()
            ->avg(function ($draw) {
                return $draw->numbers->sum('number');
            });

        $recentAverageSum = $modality->draws()
            ->with('numbers')
            ->orderByDesc('contest_number')
            ->limit($recentWindow)
            ->get()
            ->avg(function ($draw) {
                return $draw->numbers->sum('number');
            });

        $dashboardNarrative = $dashboardNarratorAgent->narrate($modality, [
            'window' => $recentWindow,
            'top_recent_numbers' => $topRecentNumbers,
            'top_delayed_numbers' => $topDelayedNumbers,
            'recent_average_sum' => $recentAverageSum,
            'historical_average_sum' => $historicalAverageSum,
        ]);

        return Inertia::render('Lottery/Dashboard', [
            'modality' => $modality,
            'frequencies' => $frequencies,
            'delays' => $delays,
            'frequenciesLast10' => $frequenciesLast10,
            'frequenciesLast20' => $frequenciesLast20,
            'frequenciesLast50' => $frequenciesLast50,
            'delaysLast10' => $delaysLast10,
            'delaysLast20' => $delaysLast20,
            'delaysLast50' => $delaysLast50,
            'recentDraws' => $recentDraws,
            'totalDraws' => $totalDraws,
            'latestContestNumber' => $latestDraw?->contest_number,
            'dashboardNarrative' => $dashboardNarrative,
            'latestDrawExplanation' => $latestDrawExplanation,
        ]);
    }

    public function generate(
        Request $request,
        LotteryModality $modality,
        CombinationGeneratorService $generator
    ): JsonResponse {
        try {
            $numbers = $generator->generate($modality, $request->all());

            return response()->json([
                'numbers' => $numbers,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function generateSmart(
        Request $request,
        LotteryModality $modality,
        SmartGameGeneratorGatewayService $generator
    ): JsonResponse {
        try {
            $games = $generator->generate($modality, $request->all());

            return response()->json([
                'games' => $games,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Não foi possível gerar jogos inteligentes no motor externo.',
            ], 500);
        }
    }

    public function analyze(
        Request $request,
        LotteryModality $modality,
        CombinationAnalysisAgentService $analysisAgent,
        CombinationHistoryService $historyService
    ): JsonResponse {
        try {
            $numbers = $request->input('numbers', []);
            $source = $request->input('source', 'manual');

            $analysis = $analysisAgent->analyze($modality, $numbers);

            $historyItem = $historyService->store($modality, $numbers, $source, $analysis);

            return response()->json(array_merge($analysis, [
                'history_item_id' => $historyItem->id,
                'bet_contest_number' => $historyItem->bet_contest_number,
                'bet_registered_at' => $historyItem->bet_registered_at?->toIso8601String(),
            ]));
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    public function importSpreadsheet(
        Request $request,
        LotteryModality $modality,
        QuinaSpreadsheetImporter $quinaImporter
    ): RedirectResponse {
        $this->prepareLongRunningRequest();

        $validated = $request->validate([
            'spreadsheet' => ['required', 'file', 'mimes:xlsx,xls'],
        ], [
            'spreadsheet.required' => 'Selecione uma planilha para importar.',
            'spreadsheet.file' => 'O arquivo enviado é inválido.',
            'spreadsheet.mimes' => 'Envie uma planilha XLSX ou XLS.',
        ]);

        if ($modality->code !== 'quina') {
            return back()->with('error', 'A importação manual de planilha está disponível apenas para a Quina neste momento.');
        }

        $uploadedFile = $validated['spreadsheet'];

        try {
            $result = $quinaImporter->import($uploadedFile->getRealPath(), $modality);

            return back()->with([
                'success' => sprintf(
                    'Importação concluída: %d novos, %d já existentes, %d ignorados.',
                    $result['imported'] ?? 0,
                    $result['existing'] ?? ($result['updated'] ?? 0),
                    $result['skipped'] ?? 0,
                ),
                'import_result' => [
                    'imported' => $result['imported'] ?? 0,
                    'existing' => $result['existing'] ?? ($result['updated'] ?? 0),
                    'skipped' => $result['skipped'] ?? 0,
                    'filename' => $uploadedFile->getClientOriginalName(),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Não foi possível importar a planilha informada.');
        }
    }

    public function syncResults(
        LotteryModality $modality,
        CaixaResultsSyncService $syncService
    ): \Illuminate\Http\RedirectResponse {
        $this->prepareLongRunningRequest();
        try {
            $result = $syncService->sync($modality);

            return back()->with('success', sprintf(
                'Sincronização concluída: %d novos, %d já existentes, %d ignorados.',
                $result['imported'] ?? 0,
                $result['existing'] ?? 0,
                $result['skipped'] ?? 0,
            ));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Não foi possível sincronizar os resultados da CAIXA.');
        }
    }

    public function history(Request $request, LotteryModality $modality)
    {
        $q = $request->get('q');

        $draws = \App\Models\Draw::with('numbers')
            ->where('lottery_modality_id', $modality->id)
            ->when($q, fn ($query) => $query->where('contest_number', $q))
            ->orderByDesc('contest_number')
            ->paginate(15)
            ->withQueryString();

        return inertia('Lottery/History', [
            'modality' => $modality,
            'draws' => $draws,
            'filters' => [
                'q' => $q,
            ],
        ]);
    }

    public function combinationHistory(Request $request, LotteryModality $modality)
    {
        $source = $request->get('source');

        $items = \App\Models\CombinationHistory::query()
            ->where('lottery_modality_id', $modality->id)
            ->when($source, fn ($query) => $query->where('source', $source))
            ->latest()
            ->paginate(15)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'numbers' => $item->numbers,
                    'source' => $item->source,
                    'bet_contest_number' => $item->bet_contest_number,
                    'bet_registered_at' => $item->bet_registered_at?->format('d/m/Y H:i'),
                    'bet_checked_at' => $item->bet_checked_at?->format('d/m/Y H:i'),
                    'bet_result_snapshot' => $item->bet_result_snapshot,
                    'analysis_snapshot' => $item->analysis_snapshot,
                    'created_at' => $item->created_at?->format('d/m/Y H:i'),
                ];
            })
            ->withQueryString();

        $latestContestNumber = $modality->draws()->max('contest_number');

        return inertia('Lottery/CombinationHistory', [
            'modality' => $modality,
            'items' => $items,
            'latestContestNumber' => $latestContestNumber,
            'filters' => [
                'source' => $source,
            ],
        ]);
    }

    public function destroyCombinationHistory(
        LotteryModality $modality,
        \App\Models\CombinationHistory $item
    ): \Illuminate\Http\RedirectResponse {
        abort_unless($item->lottery_modality_id === $modality->id, 404);

        $item->delete();

        return back()->with('success', 'Histórico removido com sucesso.');
    }

    public function clearCombinationHistory(LotteryModality $modality): \Illuminate\Http\RedirectResponse
    {
        \App\Models\CombinationHistory::query()
            ->where('lottery_modality_id', $modality->id)
            ->delete();

        return back()->with('success', 'Histórico limpo com sucesso.');
    }


    public function registerCombinationBet(
        Request $request,
        LotteryModality $modality,
        \App\Models\CombinationHistory $item
    ) {
        abort_unless($item->lottery_modality_id === $modality->id, 404);

        $latestDraw = $modality->draws()
            ->with('numbers')
            ->orderByDesc('contest_number')
            ->first();

        if (! $latestDraw) {
            $message = 'Ainda não existe resultado oficial cadastrado para vincular a aposta.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $item->forceFill([
            'bet_contest_number' => $latestDraw->contest_number,
            'bet_registered_at' => now(),
            'bet_result_snapshot' => null,
            'bet_checked_at' => null,
        ])->save();

        $payload = [
            'message' => sprintf('Aposta vinculada ao concurso %s com sucesso.', $latestDraw->contest_number),
            'item' => [
                'id' => $item->id,
                'bet_contest_number' => $item->bet_contest_number,
                'bet_registered_at' => $item->bet_registered_at?->format('d/m/Y H:i'),
                'bet_checked_at' => $item->bet_checked_at?->format('d/m/Y H:i'),
                'bet_result_snapshot' => $item->bet_result_snapshot,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', $payload['message']);
    }

    public function checkCombinationBet(
        LotteryModality $modality,
        \App\Models\CombinationHistory $item
    ) {
        abort_unless($item->lottery_modality_id === $modality->id, 404);

        $contestNumber = $item->bet_contest_number;

        $draw = null;

        if ($contestNumber) {
            $draw = $modality->draws()
                ->with('numbers')
                ->where('contest_number', $contestNumber)
                ->first();
        }

        $officialNumbers = $draw
            ? $draw->numbers->pluck('number')->map(fn ($number) => (int) $number)->sort()->values()->all()
            : [];

        $betNumbers = collect($item->numbers)
            ->map(fn ($number) => (int) $number)
            ->sort()
            ->values()
            ->all();

        $hits = array_values(array_intersect($betNumbers, $officialNumbers));

        $resultSnapshot = null;

        if ($draw) {
            $resultSnapshot = [
                'contest_number' => $draw->contest_number,
                'draw_date' => $draw->draw_date?->format('d/m/Y'),
                'official_numbers' => $officialNumbers,
                'user_numbers' => $betNumbers,
                'hits' => array_map('intval', $hits),
                'hit_count' => count($hits),
                'hit_label' => match (count($hits)) {
                    0 => 'nenhum',
                    1 => '1 número',
                    default => count($hits) . ' números',
                },
            ];

            $item->forceFill([
                'bet_result_snapshot' => $resultSnapshot,
                'bet_checked_at' => now(),
            ])->save();
        }

        $latestContestNumber = $modality->draws()->max('contest_number');

        return inertia('Lottery/CheckBet', [
            'modality' => $modality,
            'historyItem' => [
                'id' => $item->id,
                'numbers' => $betNumbers,
                'source' => $item->source,
                'created_at' => $item->created_at?->format('d/m/Y H:i'),
                'bet_contest_number' => $item->bet_contest_number,
                'bet_registered_at' => $item->bet_registered_at?->format('d/m/Y H:i'),
                'bet_checked_at' => $item->bet_checked_at?->format('d/m/Y H:i'),
            ],
            'latestContestNumber' => $latestContestNumber,
            'officialResult' => $resultSnapshot,
        ]);
    }

    protected function prepareLongRunningRequest(int $seconds = 300, string $memory = '512M'): void
    {
         if (function_exists('set_time_limit')) {
            @set_time_limit($seconds);
        }

        if (function_exists('ini_set')) {
            @ini_set('max_execution_time', (string) $seconds);
            @ini_set('memory_limit', $memory);
        }
    }
}
