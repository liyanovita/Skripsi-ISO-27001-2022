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
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->text('impact_interpretation')->nullable()->after('evidence_validation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->dropColumn('impact_interpretation');
        });
    }
};
