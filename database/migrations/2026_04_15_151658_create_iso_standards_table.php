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
    Schema::create('iso_standards', function (Blueprint $table) {
        $table->id();
        // Mendukung hirarki (Induk -> Sub)
        $table->foreignId('parent_id')->nullable()->constrained('iso_standards')->onDelete('cascade');
        
        $table->string('type'); // 'clausa' atau 'control'
        $table->string('level'); // 'clause', 'sub_clause', atau 'requirement'
        $table->string('code'); // 4, 4.1, A.5.1
        $table->string('title');
        $table->text('description')->nullable();
        
        // Kolom krusial untuk Multi-Pertanyaan & AI
        $table->json('questions')->nullable(); 
        $table->text('implementation_guidance')->nullable(); 
        
        $table->timestamps();

        // Add indexes for query optimization
        $table->index('code');
        $table->index('type');
        $table->index(['code', 'type']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iso_standards');
    }
};
