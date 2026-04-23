<?php

namespace Tests\Feature\Lottery;

use App\Models\User;
use App\Models\LotteryModality;
use App\Services\Lottery\RepeatedCombinationGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RepeatedCombinationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_repeated_combinations_page(): void
    {
        $user = User::factory()->create();

        $modality = LotteryModality::factory()->create([
            'code' => 'lotofacil',
            'name' => 'Lotofácil',
            'min_number' => 1,
            'max_number' => 25,
            'draw_count' => 15,
        ]);

        $fakeResult = [
            'items' => [
                [
                    'numbers' => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15],
                    'occurrences' => 2,
                    'draws' => [
                        ['contest_number' => 1001, 'draw_date' => '2024-01-01'],
                        ['contest_number' => 1002, 'draw_date' => '2024-01-02'],
                    ],
                ],
            ],
            'meta' => [
                'total_draws' => 2,
                'repeated_count' => 1,
                'unique_patterns' => 1,
            ],
        ];

        $this->mock(RepeatedCombinationGatewayService::class, function ($mock) use ($modality, $fakeResult) {
            $mock->shouldReceive('findRepeated')
                ->once()
                ->withArgs(function ($receivedModality) use ($modality) {
                    return $receivedModality->id === $modality->id;
                })
                ->andReturn($fakeResult);
        });

        $response = $this->actingAs($user)
            ->get(route('lottery.modalities.repeated-combinations', $modality));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Lottery/RepeatedCombinations')
            ->where('modality.id', $modality->id)
            ->where('modality.code', 'lotofacil')
            ->where('meta.repeated_count', 1)
            ->has('items', 1)
        );
    }
}