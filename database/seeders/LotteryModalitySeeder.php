<?php

namespace Database\Seeders;

use App\Models\LotteryModality;
use Illuminate\Database\Seeder;

class LotteryModalitySeeder extends Seeder
{
    public function run(): void
    {
        $modalities = [
            [
                'code' => 'quina',
                'name' => 'Quina',
                'min_number' => 1,
                'max_number' => 80,
                'draw_count' => 5,
                'bet_min_count' => 5,
                'bet_max_count' => 15,
                'allows_repetition' => false,
                'order_matters' => false,
                'is_active' => true,
            ],
            [
                'code' => 'mega_sena',
                'name' => 'Mega-Sena',
                'min_number' => 1,
                'max_number' => 60,
                'draw_count' => 6,
                'bet_min_count' => 6,
                'bet_max_count' => 20,
                'allows_repetition' => false,
                'order_matters' => false,
                'is_active' => true,
            ],
            [
                'code' => 'lotofacil',
                'name' => 'Lotofácil',
                'min_number' => 1,
                'max_number' => 25,
                'draw_count' => 15,
                'bet_min_count' => 15,
                'bet_max_count' => 20,
                'allows_repetition' => false,
                'order_matters' => false,
                'is_active' => true,
            ],
            [
                'code' => 'lotomania',
                'name' => 'Lotomania',
                'min_number' => 0,
                'max_number' => 99,
                'draw_count' => 20,
                'bet_min_count' => 50,
                'bet_max_count' => 50,
                'allows_repetition' => false,
                'order_matters' => false,
                'is_active' => true,
            ],
        ];

        foreach ($modalities as $data) {
            LotteryModality::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}