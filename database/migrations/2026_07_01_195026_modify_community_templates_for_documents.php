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
        Schema::table('community_templates', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('description');
            $table->string('format')->nullable()->after('content');
            $table->string('size')->nullable()->after('format');
            $table->string('attachment_path')->nullable()->after('size');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime')->nullable()->after('attachment_name');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime');
            
            // Make existing JSON column nullable
            $table->json('content_data')->nullable()->change();
            
            // If base_score is required for some reason, maybe we make it nullable or default 0. It is already default 0.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_templates', function (Blueprint $table) {
            $table->dropColumn([
                'content', 'format', 'size', 'attachment_path', 
                'attachment_name', 'attachment_mime', 'attachment_size'
            ]);
            $table->json('content_data')->nullable(false)->change();
        });
    }
};
