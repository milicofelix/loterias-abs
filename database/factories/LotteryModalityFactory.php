<?php

namespace Database\Factories;

use App\Models\LotteryModality;
use Illuminate\Database\Eloquent\Factories\Factory;

class LotteryModalityFactory extends Factory
{
    protected $model = LotteryModality::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => fake()->unique()->word(),
            'min_number' => 1,
            'max_number' => 60,
            'draw_count' => 6,
            'bet_min_count' => 6,
            'bet_max_count' => 15,
            'allows_repetition' => false,
            'order_matters' => false,
            'is_active' => true,
        ];
    }

    public function quina(): static
    {
        return $this->state(fn () => [
            'code' => 'quina',
            'name' => 'Quina',
            'min_number' => 1,
            'max_number' => 80,
            'draw_count' => 5,
            'bet_min_count' => 5,
            'bet_max_count' => 15,
        ]);
    }

    public function megaSena(): static
    {
        return $this->state(fn () => [
            'code' => 'mega_sena',
            'name' => 'Mega-Sena',
            'min_number' => 1,
            'max_number' => 60,
            'draw_count' => 6,
            'bet_min_count' => 6,
            'bet_max_count' => 20,
        ]);
    }

    public function lotofacil(): static
    {
        return $this->state(fn () => [
            'code' => 'lotofacil',
            'name' => 'Lotofácil',
            'min_number' => 1,
            'max_number' => 25,
            'draw_count' => 15,
            'bet_min_count' => 15,
            'bet_max_count' => 20,
        ]);
    }
}
