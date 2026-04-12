<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CombinationHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lottery_modality_id',
        'numbers',
        'source',
        'bet_contest_number',
        'bet_registered_at',
        'analysis_snapshot',
        'bet_result_snapshot',
        'bet_checked_at',
    ];

    protected $casts = [
        'numbers' => 'array',
        'analysis_snapshot' => 'array',
        'bet_result_snapshot' => 'array',
        'bet_registered_at' => 'datetime',
        'bet_checked_at' => 'datetime',
    ];

    public function modality(): BelongsTo
    {
        return $this->belongsTo(LotteryModality::class, 'lottery_modality_id');
    }
}