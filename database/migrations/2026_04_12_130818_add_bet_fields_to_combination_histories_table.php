<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('combination_histories', function (Blueprint $table) {
            $table->unsignedInteger('bet_contest_number')->nullable()->after('source');
            $table->timestamp('bet_registered_at')->nullable()->after('bet_contest_number');
            $table->json('bet_result_snapshot')->nullable()->after('analysis_snapshot');
            $table->timestamp('bet_checked_at')->nullable()->after('bet_result_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('combination_histories', function (Blueprint $table) {
            $table->dropColumn([
                'bet_contest_number',
                'bet_registered_at',
                'bet_result_snapshot',
                'bet_checked_at',
            ]);
        });
    }
};
