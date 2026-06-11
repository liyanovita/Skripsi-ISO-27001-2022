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
        $table->string('name'); // Contoh: "Audit Internal v1"
        $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
        $table->decimal('overall_maturity_score', 5, 2)->default(0); // Hasil akhir
        $table->text('ai_summary')->nullable(); // Ringkasan global dari AI
        $table->timestamps();
        $table->softDeletes();

        // Add indexes for query optimization
        $table->index('user_id');
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
