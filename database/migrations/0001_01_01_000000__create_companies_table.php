<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();

            // Business identity
            $table->enum('type', [
                'merchant',
                'business',
                'platform',
                'bank',
                'sacco',
                'other',
            ])->default('merchant');

            // Registration
            $table->string('registration_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            // Status
            $table->boolean('status')->default(true);
            $table->timestamp('verified_at')->nullable();
            // NOTE: verified_by FK is added in a later migration
            // after the users table exists

            // Settings
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};