<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** v2: requests sent to payment rails, and ramp orders (collect from the payer, pay the recipient). New tables only. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rail_requests', function (Blueprint $t) {
            $t->id();
            $t->string('rail', 32);
            $t->string('kind', 16);                       // collect | payout
            $t->string('reference', 191)->unique();       // ours: also the idempotency key sent to the rail
            $t->string('provider_reference', 191)->nullable();
            $t->unsignedBigInteger('amount');             // cents
            $t->char('currency', 3);
            $t->string('party', 64);                      // the phone, paybill, till or bank account
            $t->json('meta')->nullable();
            $t->string('status', 16)->default('pending'); // pending | succeeded | failed | unknown
            $t->string('receipt', 64)->nullable();
            $t->string('failure')->nullable();
            $t->unsignedInteger('checks')->default(0);
            $t->dateTime('last_checked_at')->nullable();
            $t->dateTime('completed_at')->nullable();
            $t->timestamps();
            $t->index(['rail', 'status']);
            $t->unique(['rail', 'provider_reference']);
        });

        Schema::create('ramp_orders', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->string('rail', 32);
            $t->string('destination_type', 16);           // phone | paybill | till | bank
            $t->json('destination');
            $t->string('payer_phone', 16);
            $t->unsignedBigInteger('amount');             // to the recipient, cents (whole shillings)
            $t->unsignedBigInteger('fee');
            $t->unsignedBigInteger('excise');
            $t->unsignedBigInteger('rounding');
            $t->unsignedBigInteger('total');              // collected from the payer, cents (whole shillings)
            $t->char('currency', 3);
            $t->string('note', 140)->nullable();
            $t->string('status', 16)->default('quoted');
            $t->string('failure')->nullable();
            $t->dateTime('quote_expires_at');
            $t->unsignedBigInteger('collect_request_id')->nullable();
            $t->unsignedBigInteger('payout_request_id')->nullable();
            $t->unsignedBigInteger('refund_request_id')->nullable();
            $t->json('timeline')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'created_at']);
            $t->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ramp_orders');
        Schema::dropIfExists('rail_requests');
    }
};
