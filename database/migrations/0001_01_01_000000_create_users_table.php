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

            // Identity
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();

            // Profile
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();

            // Location
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();

            // Business / KYC
            $table->string('tax_id')->nullable();
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            // Onboarding / waiting list
            $table->enum('intent', [
                'undecided',
                'merchant',
                'business',
                'techie',
                'individual',
            ])->default('undecided');

            $table->enum('onboarding_status', [
                'waiting_list',
                'invited',
                'in_progress',
                'active',
                'rejected',
                'suspended',
            ])->default('waiting_list');

            $table->timestamp('invited_at')->nullable();
            $table->timestamp('onboarded_at')->nullable();

            // Self-references
            $table->foreignId('invited_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Status flags
            $table->boolean('status')->default(true);

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Social
            $table->json('social')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('intent');
            $table->index('onboarding_status');
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};