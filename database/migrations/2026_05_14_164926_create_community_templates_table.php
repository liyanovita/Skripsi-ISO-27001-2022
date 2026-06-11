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
        Schema::create('community_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->string('author_name');
            $table->json('tags')->nullable();
            $table->decimal('base_score', 5, 2)->default(0);
            $table->json('content_data'); // actual audit data (sessions + results)
            $table->integer('downloads_count')->default(0);
            $table->integer('upvotes')->default(0);
            $table->integer('rating_sum')->default(0);   // total of all star ratings
            $table->integer('rating_count')->default(0); // number of raters
            $table->timestamps();

            // Add indexes for query optimization
            $table->index('user_id');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_templates');
    }
};
