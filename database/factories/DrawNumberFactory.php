<?php

namespace Database\Factories;

use App\Models\Draw;
use App\Models\DrawNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrawNumberFactory extends Factory
{
    protected $model = DrawNumber::class;

    public function definition(): array
    {
        return [
            'draw_id' => Draw::factory(),
            'number' => fake()->numberBetween(1, 60),
        ];
    }
}