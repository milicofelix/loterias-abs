<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_modality_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('contest_number');
            $table->date('draw_date');
            $table->timestamps();

            $table->unique(['lottery_modality_id', 'contest_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draws');
    }
};