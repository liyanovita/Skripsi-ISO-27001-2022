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
    Schema::create('assessment_results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('session_id')->constrained('assessment_sessions')->onDelete('cascade');
        $table->foreignId('iso_standard_id')->constrained('iso_standards');

        $table->json('answers')->nullable();
        $table->integer('maturity_rating')->default(0);
        $table->string('status')->default('not_started'); // not_started, completed
        $table->text('notes')->nullable();
        $table->string('evidence_file')->nullable();
        $table->text('ai_recommendation')->nullable();
        $table->text('corrective_action_plan')->nullable();
        $table->string('risk_priority')->nullable();
        $table->text('control_insight')->nullable();
        $table->text('evidence_validation')->nullable();

        // Statement of Applicability (SoA)
        $table->boolean('is_applicable')->default(true);
        $table->text('soa_justification')->nullable();
        $table->string('implementation_status')->default('not_started');
        // Values: not_started | planned | in_progress | implemented | reviewed

        // Risk Treatment Plan
        $table->date('treatment_due_date')->nullable();
        $table->string('treatment_pic')->nullable();
        $table->string('treatment_status')->default('open'); // open, in_progress, closed

        $table->timestamps();

        // Add indexes for query optimization
        $table->index('session_id');
        $table->index('iso_standard_id');
        $table->index('status');
        $table->index('maturity_rating');
        $table->index('created_at');
        $table->index(['session_id', 'status']);
        $table->index(['session_id', 'maturity_rating']);
        $table->index(['iso_standard_id', 'maturity_rating']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_results');
    }
};
