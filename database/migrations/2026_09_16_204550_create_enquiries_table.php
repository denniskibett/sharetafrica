<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('audience', [
                'merchant', 'individual', 'trader', 'developer',
                'institution', 'press', 'career', 'other',
            ])->default('other');

            // Context the user provided in the form (does NOT duplicate users columns)
            $table->string('business')->nullable();
            $table->string('role')->nullable();
            $table->text('brief')->nullable();
            $table->string('reference_url')->nullable();
            $table->json('meta')->nullable();

            $table->enum('status', [
                'new', 'read', 'responded', 'archived', 'spam',
            ])->default('new');

            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('audience');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};