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
        'analysis_snapshot',
    ];

    protected $casts = [
        'numbers' => 'array',
        'analysis_snapshot' => 'array',
    ];

    public function modality(): BelongsTo
    {
        return $this->belongsTo(LotteryModality::class, 'lottery_modality_id');
    }
}