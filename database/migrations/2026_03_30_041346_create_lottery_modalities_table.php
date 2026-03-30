<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_modalities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // quina, mega_sena, lotofacil
            $table->string('name');
            $table->unsignedSmallInteger('min_number');
            $table->unsignedSmallInteger('max_number');
            $table->unsignedSmallInteger('draw_count');
            $table->unsignedSmallInteger('bet_min_count');
            $table->unsignedSmallInteger('bet_max_count');
            $table->boolean('allows_repetition')->default(false);
            $table->boolean('order_matters')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_modalities');
    }
};