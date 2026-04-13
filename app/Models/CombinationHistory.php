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
        'user_id',
        'numbers',
        'source',
        'analysis_snapshot',
        'bet_contest_number',
        'bet_registered_at',
    ];

    protected $casts = [
        'numbers' => 'array',
        'analysis_snapshot' => 'array',
        'bet_registered_at' => 'datetime',
    ];

    public function modality(): BelongsTo
    {
        return $this->belongsTo(LotteryModality::class, 'lottery_modality_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
