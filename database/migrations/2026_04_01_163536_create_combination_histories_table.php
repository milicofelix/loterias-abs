<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combination_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_modality_id')->constrained()->cascadeOnDelete();
            $table->json('numbers');
            $table->string('source', 20); // generated | manual
            $table->json('analysis_snapshot')->nullable();
            $table->timestamps();

            $table->index(['lottery_modality_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combination_histories');
    }
};