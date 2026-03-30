<?php

namespace Database\Factories;

use App\Models\Draw;
use App\Models\LotteryModality;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrawFactory extends Factory
{
    protected $model = Draw::class;

    public function definition(): array
    {
        return [
            'lottery_modality_id' => LotteryModality::factory(),
            'contest_number' => fake()->unique()->numberBetween(1, 999999),
            'draw_date' => fake()->date(),
        ];
    }
}