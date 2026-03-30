<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_id',
        'number',
    ];

    public function draw(): BelongsTo
    {
        return $this->belongsTo(Draw::class);
    }
}