<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiting_list_entries', function (Blueprint $table) {
            $table->id();

            // The single source of truth: the user this entry belongs to
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Intent picked on the marketing site
            $table->enum('intent', [
                'individual',
                'merchant',
                'business',
                'techie',
                'undecided',
            ])->default('undecided');

            // Where the signup came from
            $table->string('source')->nullable();   // "personal-page", "merchants-page"

            // Extra structured payload (business name, expected volume, etc.)
            $table->json('meta')->nullable();

            // Workflow
            $table->enum('status', [
                'pending',
                'invited',
                'registered',
                'rejected',
            ])->default('pending');

            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('registered_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('intent');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_list_entries');
    }
};