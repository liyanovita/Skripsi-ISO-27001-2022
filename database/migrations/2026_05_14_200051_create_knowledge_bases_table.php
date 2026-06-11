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
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // guides, templates, sop, evidence
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->longText('content');
            $table->string('format')->nullable(); // DOCX, PDF, etc
            $table->string('size')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->integer('downloads_count')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            // Add indexes for query optimization
            $table->index('category');
            $table->index('is_system');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
