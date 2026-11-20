<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** API v1: payment requests, money in (docs/v2/INTEGRATION.md §3). Paid from a Sharet wallet (PIN) or by an M-PESA prompt. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_payment_requests', function (Blueprint $t) {
            $t->id();
            $t->string('public_id', 40)->unique();              // pr_… / pr_test_…
            $t->string('token', 48)->unique();                  // the hosted pay page link
            $t->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();     // who gets paid
            $t->string('environment', 10);
            $t->unsignedBigInteger('amount');
            $t->string('reference', 140)->nullable();
            $t->foreignId('payer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('payer_phone', 20)->nullable();
            $t->json('channels');
            $t->json('metadata')->nullable();
            $t->string('status', 20)->default('open');           // open, paid, expired, cancelled
            $t->string('paid_via', 20)->nullable();             // sharet, mpesa, sandbox
            $t->foreignId('ramp_order_id')->nullable()->constrained()->nullOnDelete();   // the order that paid it (or is collecting)
            $t->timestamp('paid_at')->nullable();
            $t->dateTime('expires_at');   // dateTime: MySQL strict mode refuses a NOT NULL timestamp without a default
            $t->timestamps();
            $t->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_payment_requests');
    }
};
