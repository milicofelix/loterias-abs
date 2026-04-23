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
use App\Services\Lottery\Importers\CaixaSpreadsheetImporter;
use App\Services\Lottery\LotteryRulesService;
use App\Services\Lottery\SmartGameGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use App\Services\Lottery\SmartGameGeneratorGatewayService;
use App\Services\Lottery\HistoricalPrizeSummaryService;
use App\Services\Lottery\ManualDrawCreationService;

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
        DrawExplainerAgentService $drawExplainerAgent,
        LotteryRulesService $rulesService
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
            'rules' => [
                'play_instruction' => $rulesService->playInstruction($modality),
                'prize_hits' => $rulesService->prizeHitRange($modality),
                'supports_caixa_sync' => $rulesService->supportsCaixaSync($modality),
                'supports_caixa_import' => $rulesService->supportsCaixaSpreadsheet($modality),
                'supports_smart_generation' => $rulesService->supportsSmartGeneration($modality),
            ],
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
        SmartGameGeneratorGatewayService $gateway,
        HistoricalPrizeSummaryService $historicalPrizeSummaryService,
        SmartGameGeneratorService $localGenerator,
        LotteryRulesService $rulesService
    ): JsonResponse {
        try {
            if (! $rulesService->supportsSmartGeneration($modality)) {
                throw new InvalidArgumentException("A geração inteligente ainda não está disponível para {$modality->name}.");
            }

            $meta = null;

            try {
                if ($rulesService->usesExternalSmartEngine($modality)) {
                    $result = $gateway->generateWithMeta($modality, $request->all());
                    $games = $result['games'];
                    $meta = $result['meta'] ?? null;
                } else {
                    $games = $localGenerator->generate($modality, $request->all());
                    $meta = [
                        'engine' => 'php',
                        'fallback' => false,
                    ];
                }
            } catch (\Throwable $e) {
                // $games = $localGenerator->generate($modality, $request->all());
                // $meta = [
                //     'engine' => 'php',
                //     'fallback' => true,
                //     'fallback_reason' => $e->getMessage(),
                // ];
                \Log::error('[LOTTERY][SMART_GENERATION] engine externa falhou', [
                    'modality' => $modality->code,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }

            $gamesWithHistory = $this->attachHistoricalPrizeSummaryToGames(
                $modality,
                $games,
                $historicalPrizeSummaryService
            );

            return response()->json([
                'games' => $gamesWithHistory,
                'meta' => $meta,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Não foi possível gerar jogos inteligentes para esta modalidade.',
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

            $historyService->store($modality, $numbers, $source, $analysis, $request->user()?->id);

            return response()->json($analysis);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    /**
     * @param array<int, array<string, mixed>> $games
     * @return array<int, array<string, mixed>>
     */
    protected function attachHistoricalPrizeSummaryToGames(
        LotteryModality $modality,
        array $games,
        HistoricalPrizeSummaryService $historicalPrizeSummaryService
    ): array {
        $gamesNumbers = array_map(
            fn (array $game) => array_map(static fn ($number) => (int) $number, (array) ($game['numbers'] ?? [])),
            $games
        );

        $summaries = $historicalPrizeSummaryService->analyzeBatch($modality, $gamesNumbers);

        return array_map(function (array $game, int $index) use ($summaries) {
            return array_merge($game, [
                'historical_prize_summary' => $summaries[$index]['historical_prize_summary'] ?? null,
            ]);
        }, $games, array_keys($games));
    }


    public function importSpreadsheet(
        Request $request,
        LotteryModality $modality,
        CaixaSpreadsheetImporter $spreadsheetImporter,
        LotteryRulesService $rulesService
    ): RedirectResponse {
        $this->prepareLongRunningRequest();

        $validated = $request->validate([
            'spreadsheet' => ['required', 'file', 'mimes:xlsx,xls'],
        ], [
            'spreadsheet.required' => 'Selecione uma planilha para importar.',
            'spreadsheet.file' => 'O arquivo enviado é inválido.',
            'spreadsheet.mimes' => 'Envie uma planilha XLSX ou XLS.',
        ]);

        if (! $rulesService->supportsCaixaSpreadsheet($modality)) {
            return back()->with('error', "A importação manual de planilha ainda não está disponível para {$modality->name}.");
        }

        $uploadedFile = $validated['spreadsheet'];

        try {
            $result = $spreadsheetImporter->import($uploadedFile->getRealPath(), $modality);

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
            ->where('user_id', $request->user()->id)
            ->when($source, fn ($query) => $query->where('source', $source))
            ->latest()
            ->paginate(10)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'numbers' => $item->numbers,
                    'source' => $item->source,
                    'analysis_snapshot' => $item->analysis_snapshot,
                    'bet_contest_number' => $item->bet_contest_number,
                    'bet_registered_at' => $item->bet_registered_at?->format('d/m/Y H:i'),
                    'created_at' => $item->created_at?->format('d/m/Y H:i'),
                ];
            })
            ->withQueryString();

        return inertia('Lottery/CombinationHistory', [
            'modality' => $modality,
            'items' => $items,
            'filters' => [
                'source' => $source,
            ],
        ]);
    }

    public function registerCombinationBet(
        Request $request,
        LotteryModality $modality,
        \App\Models\CombinationHistory $item
    ): JsonResponse 
    {
        
        if (! $request->user()) {        
            return response()->json([  
                'message' => 'Faça login para registrar uma aposta.',
                ], 401);
        }

        abort_unless($item->lottery_modality_id === $modality->id, 404);

        if ($item->user_id !== null && $item->user_id !== $request->user()->id) {
            abort(404);
        }

        $latestDraw = $modality->draws()->with('numbers')->orderByDesc('contest_number')->first();

        if (! $latestDraw) {
            return response()->json([
                'message' => 'Ainda não há concurso oficial disponível para vincular a aposta.',
            ], 422);
        }

        $item->forceFill([
            'user_id' => $request->user()->id,
            'bet_contest_number' => $latestDraw->contest_number,
            'bet_registered_at' => now(),
        ])->save();

        return response()->json([
            'item' => [
                'id' => $item->id,
                'bet_contest_number' => $item->bet_contest_number,
                'bet_registered_at' => $item->bet_registered_at?->toISOString(),
            ],
        ]);
    }

    public function checkCombinationBet(
        Request $request,
        LotteryModality $modality,
        \App\Models\CombinationHistory $item,
        LotteryRulesService $rulesService
    ) {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        abort_unless($item->lottery_modality_id === $modality->id, 404);
        abort_unless($item->user_id === $request->user()->id, 404);
        abort_unless($item->bet_contest_number, 404);

        $draw = $modality->draws()
            ->with('numbers')
            ->where('contest_number', $item->bet_contest_number)
            ->firstOrFail();

        $drawNumbers = $draw->numbers->pluck('number')->map(fn ($value) => (int) $value)->sort()->values()->all();
        $userNumbers = collect($item->numbers)->map(fn ($value) => (int) $value)->sort()->values()->all();
        $hits = collect($userNumbers)->intersect($drawNumbers)->values()->all();

        return inertia('Lottery/CheckBet', [
            'modality' => $modality,
            'historyItem' => [
                'id' => $item->id,
                'numbers' => $userNumbers,
                'bet_contest_number' => $item->bet_contest_number,
                'bet_registered_at' => $item->bet_registered_at?->format('d/m/Y H:i'),
            ],
            'officialResult' => [
                'contest_number' => $draw->contest_number,
                'numbers' => $drawNumbers,
            ],
            'checkResult' => [
                'hits' => $hits,
                'hit_count' => count($hits),
                'is_prized' => $rulesService->isPrized($modality, count($hits)),
                'prize_label' => $rulesService->prizeLabel($modality, count($hits)),
                'prize_hits' => $rulesService->prizeHitRange($modality),
            ],
        ]);
    }

    public function bets(Request $request, LotteryModality $modality)
    {
        $days = (int) $request->integer('days', 30);

        if (! in_array($days, [7, 15, 30, 60, 90], true)) {
            $days = 30;
        }

        $items = \App\Models\CombinationHistory::query()
            ->where('lottery_modality_id', $modality->id)
            ->where('user_id', auth()->id())
            ->whereNotNull('bet_registered_at')
            ->where('bet_registered_at', '>=', now()->subDays($days))
            ->latest('bet_registered_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Lottery/MyBets', [
            'modality' => $modality,
            'items' => $items,
            'filters' => [
                'days' => $days,
            ],
            'dayOptions' => [7, 15, 30, 60, 90],
        ]);
    }

    public function myBets(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $days = (int) $request->integer('days', 30);
        $allowedDays = [7, 15, 30, 60, 90];
        if (! in_array($days, $allowedDays, true)) {
            $days = 30;
        }

        $items = \App\Models\CombinationHistory::query()
            ->with('modality')
            ->where('user_id', $user->id)
            ->whereNotNull('bet_registered_at')
            ->where('bet_registered_at', '>=', now()->subDays($days)->startOfDay())
            ->latest('bet_registered_at')
            ->paginate(10)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'modality' => [
                        'id' => $item->modality?->id,
                        'name' => $item->modality?->name,
                        'code' => $item->modality?->code,
                    ],
                    'numbers' => $item->numbers,
                    'source' => $item->source,
                    'bet_contest_number' => $item->bet_contest_number,
                    'bet_registered_at' => $item->bet_registered_at?->format('d/m/Y H:i'),
                ];
            })
            ->withQueryString();

        return inertia('Lottery/MyBets', [
            'items' => $items,
            'filters' => [
                'days' => $days,
            ],
            'dayOptions' => $allowedDays,
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

    public function storeManualDraw(Request $request, LotteryModality $modality, ManualDrawCreationService $manualDrawCreationService)
    {
        $validated = $request->validate([
            'contest_number' => ['required', 'integer', 'min:1'],
            'draw_date' => ['required', 'date'],
            'numbers' => ['required', 'array', 'size:' . $modality->draw_count],
            'numbers.*' => ['required', 'integer', 'min:' . $modality->min_number, 'max:' . $modality->max_number],
            'observation' => ['nullable', 'string'],
        ]);

        $manualDrawCreationService->create($modality, $validated);

        return back()->with('success', 'Resultado cadastrado com sucesso.');
    }
}
