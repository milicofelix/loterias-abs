<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Draw extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_modality_id',
        'contest_number',
        'draw_date',
    ];

    protected $casts = [
        'draw_date' => 'date',
    ];

    public function modality(): BelongsTo
    {
        return $this->belongsTo(LotteryModality::class, 'lottery_modality_id');
    }

    public function numbers(): HasMany
    {
        return $this->hasMany(DrawNumber::class);
    }
}