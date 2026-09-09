<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->string('ai_summary_locale', 5)->nullable()->after('ai_summary_hash');
            $table->text('ai_summary_translations')->nullable()->after('ai_summary_locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->dropColumn(['ai_summary_locale', 'ai_summary_translations']);
        });
    }
};
