<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->enum('status', ['active', 'suspended'])->default('active');
            
            $table->index(['provider', 'provider_id']);
            
            // Fokus pada profil organisasi untuk konteks AI n8n
            $table->string('organization_name')->nullable();
            $table->string('business_sector')->nullable(); 
            $table->string('organization_scale')->nullable(); // Kecil, Menengah, Besar
            $table->text('it_governance_structure')->nullable(); // Deskripsi roles & responsibilities
            $table->text('isms_scope')->nullable(); // Scope formal SMSI
            $table->text('organization_description')->nullable(); 
            
            $table->rememberToken();
            $table->timestamps();

            // Add indexes for query optimization
            $table->index('email');
            $table->index('created_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};