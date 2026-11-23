<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Onboarding funnel: the first time each person reaches each step (App\Support\Funnel), and the 1–5 rating asked
 * once after their first payment out.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('step', 32);
            $table->string('channel', 8)->nullable();         // web | app | null (a job, a callback, staff)
            $table->boolean('backfilled')->default(false);    // filled from older records by funnel:backfill (time is approximate)
            $table->timestamp('created_at')->index();
            $table->unique(['user_id', 'step']);
            $table->index('step');
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic', 32)->default('first_payment');
            $table->unsignedTinyInteger('score')->nullable(); // 1–5; null = dismissed without a score
            $table->string('comment', 1000)->nullable();
            $table->string('channel', 8)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'topic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('funnel_events');
    }
};
