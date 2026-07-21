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
        Schema::create('assessment_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
        $table->string('name'); // Contoh: "Audit Internal v1"
        $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
        $table->date('deadline')->nullable();
        $table->decimal('overall_maturity_score', 5, 2)->default(0); // Hasil akhir
        $table->text('ai_summary')->nullable(); // Ringkasan global dari AI
        $table->string('ai_summary_hash', 64)->nullable()->comment('SHA-256 hash of aggregated session results data at the time of last AI summary generation');
        $table->timestamps();
        $table->softDeletes();

        // Add indexes for query optimization
        $table->index('user_id');
        $table->index('organization_id');
        $table->index('status');
        $table->index('created_at');
        $table->index('updated_at');
        $table->index(['user_id', 'status']);
        $table->index(['user_id', 'created_at']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_sessions');
    }
};
