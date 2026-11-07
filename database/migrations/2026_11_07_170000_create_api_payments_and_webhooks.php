<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API v1 step 3 (docs/v2/INTEGRATION.md §4, §8, §10): payments made with an API key, and webhooks.
 * Live payments point at the ramp order that moves the money; sandbox payments never touch the ledger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_payments', function (Blueprint $t) {
            $t->id();
            $t->string('public_id', 40)->unique();              // pay_… (live) / pay_test_… (sandbox)
            $t->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('environment', 10);
            $t->foreignId('ramp_order_id')->nullable()->constrained()->nullOnDelete();
            $t->string('status', 20);                            // paying, paid, failed, refunded
            $t->unsignedBigInteger('amount');
            $t->unsignedBigInteger('fee')->default(0);
            $t->unsignedBigInteger('total');
            $t->json('to');
            $t->string('reference', 140)->nullable();
            $t->string('failure')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'environment', 'created_at']);
        });

        Schema::create('api_webhooks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('environment', 10);
            $t->string('url', 500);
            $t->json('events');
            $t->text('secret');                                  // encrypted at rest; signs every delivery
            $t->boolean('enabled')->default(true);
            $t->timestamps();
        });

        Schema::create('api_webhook_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('api_webhook_id')->constrained()->cascadeOnDelete();
            $t->string('event', 60);
            $t->string('event_id', 40)->unique();                // evt_… so receivers can ignore repeats
            $t->json('payload');
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->string('status', 20)->default('pending');        // pending, delivered, failed
            $t->unsignedSmallInteger('last_status')->nullable();
            $t->timestamp('next_attempt_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamps();
            $t->index(['status', 'next_attempt_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_webhook_deliveries');
        Schema::dropIfExists('api_webhooks');
        Schema::dropIfExists('api_payments');
    }
};
