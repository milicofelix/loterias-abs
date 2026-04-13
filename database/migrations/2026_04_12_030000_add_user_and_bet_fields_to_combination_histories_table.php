<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('combination_histories', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();

            $table->index(['user_id', 'lottery_modality_id', 'created_at'], 'ch_user_modality_created_idx');
            $table->index(['user_id', 'bet_registered_at'], 'ch_user_bet_registered_idx');
        });
    }

    public function down(): void
    {
        Schema::table('combination_histories', function (Blueprint $table) {
            $table->dropIndex('ch_user_modality_created_idx');
            $table->dropIndex('ch_user_bet_registered_idx');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
