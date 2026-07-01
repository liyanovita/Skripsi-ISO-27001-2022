<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds ai_data_hash to assessment_results (hash of data sent to n8n per control)
     * and ai_summary_hash to assessment_sessions (hash of all results data for summary generation).
     */
    public function up(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->string('ai_data_hash', 64)->nullable()->after('impact_interpretation')
                  ->comment('SHA-256 hash of assessment data (maturity_rating|is_applicable|notes|answers) at the time of last AI generation');
        });

        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->string('ai_summary_hash', 64)->nullable()->after('ai_summary')
                  ->comment('SHA-256 hash of aggregated session results data at the time of last AI summary generation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->dropColumn('ai_data_hash');
        });

        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->dropColumn('ai_summary_hash');
        });
    }
};
