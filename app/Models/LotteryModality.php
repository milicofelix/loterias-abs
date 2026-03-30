<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LotteryModality extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'min_number',
        'max_number',
        'draw_count',
        'bet_min_count',
        'bet_max_count',
        'allows_repetition',
        'order_matters',
        'is_active',
    ];

    protected $casts = [
        'allows_repetition' => 'boolean',
        'order_matters' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function draws(): HasMany
    {
        return $this->hasMany(Draw::class);
    }
}