<?php

use App\Models\LotteryModality;
use App\Models\Draw;
use App\Models\DrawNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('pode listar concursos com paginação', function () {
    $quina = LotteryModality::factory()->quina()->create();

    foreach (range(1, 30) as $contest) {
        $draw = Draw::create([
            'lottery_modality_id' => $quina->id,
            'contest_number' => $contest,
            'draw_date' => now()->subDays($contest)->toDateString(),
        ]);

        foreach ([1,2,3,4,5] as $n) {
            DrawNumber::create([
                'draw_id' => $draw->id,
                'number' => $n,
            ]);
        }
    }

    $response = $this->get("/lottery/modalities/{$quina->id}/history");

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Lottery/History')
        ->has('draws.data', 15)
    );
});

it('pode buscar concurso pelo número', function () {
    $quina = LotteryModality::factory()->quina()->create();

    Draw::create([
        'lottery_modality_id' => $quina->id,
        'contest_number' => 9999,
        'draw_date' => now(),
    ]);

    $response = $this->get("/lottery/modalities/{$quina->id}/history?q=9999");

    $response->assertInertia(fn (Assert $page) => $page
        ->where('filters.q', '9999')
        ->has('draws.data', 1)
    );
});