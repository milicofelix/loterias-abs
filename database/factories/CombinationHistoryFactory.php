<?php

namespace Database\Factories;

use App\Models\CombinationHistory;
use App\Models\LotteryModality;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CombinationHistoryFactory extends Factory
{
    protected $model = CombinationHistory::class;

    public function definition(): array
    {
        return [
            'lottery_modality_id' => LotteryModality::factory(),
            'user_id' => null,
            'numbers' => [1, 2, 3, 4, 5],
            'source' => 'manual',
            'analysis_snapshot' => [
                'sum' => 15,
                'even_count' => 2,
                'odd_count' => 3,
            ],
            'bet_contest_number' => null,
            'bet_registered_at' => null,
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(fn () => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }
}
