<?php

namespace Tests\Unit\Lottery;

use App\Models\Draw;
use App\Models\DrawNumber;
use App\Models\LotteryModality;
use App\Services\Lottery\LotteryEngineClient;
use App\Services\Lottery\RepeatedCombinationGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RepeatedCombinationGatewayServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_payload_and_returns_engine_response(): void
    {
        $modality = LotteryModality::factory()->create([
            'code' => 'lotofacil',
            'name' => 'Lotofácil',
            'min_number' => 1,
            'max_number' => 25,
            'draw_count' => 15,
        ]);

        $draw1 = Draw::factory()->create([
            'lottery_modality_id' => $modality->id,
            'contest_number' => 1001,
            'draw_date' => '2024-01-01',
        ]);

        $draw2 = Draw::factory()->create([
            'lottery_modality_id' => $modality->id,
            'contest_number' => 1002,
            'draw_date' => '2024-01-02',
        ]);

        foreach ([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15] as $number) {
            DrawNumber::factory()->create([
                'draw_id' => $draw1->id,
                'number' => $number,
            ]);
        }

        foreach ([15,14,13,12,11,10,9,8,7,6,5,4,3,2,1] as $number) {
            DrawNumber::factory()->create([
                'draw_id' => $draw2->id,
                'number' => $number,
            ]);
        }

        $client = Mockery::mock(LotteryEngineClient::class);

        $client->shouldReceive('repeatedCombinations')
            ->once()
            ->with(Mockery::on(function (array $payload) {
                return ($payload['modality']['code'] ?? null) === 'lotofacil'
                    && count($payload['draws'] ?? []) === 2
                    && ($payload['draws'][0]['contest_number'] ?? null) === 1001;
            }))
            ->andReturn([
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
            ]);

        $service = new RepeatedCombinationGatewayService($client);

        $result = $service->findRepeated($modality);

        $this->assertCount(1, $result['items']);
        $this->assertSame(1, $result['meta']['repeated_count']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}