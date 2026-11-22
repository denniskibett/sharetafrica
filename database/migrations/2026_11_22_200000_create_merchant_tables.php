<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Merchant and cashier (Phase 2, canvas "Mobile app · Merchant and cashier"). A shop is a Company with its own Sharet
 * wallet (Company::walletUser). Each till has one cashier with a cashier PIN, used on a shop phone linked to that till.
 * Every sale is a payment request of kind "merchant" (same pay page, M-PESA prompt or Sharet wallet), or money paid to
 * the shop's account number. Refunds are asked by a cashier and approved by the owner with the PIN. End of day closes
 * each till (cashier, owner, or automatically at the shop's settlement time).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_tills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('number');                     // Till 1, Till 2 …
            $t->string('cashier_name', 120);
            $t->string('cashier_phone', 20);
            $t->string('status', 10)->default('invited');          // invited | active | paused | removed
            $t->string('pin_hash')->nullable();
            $t->unsignedTinyInteger('pin_attempts')->default(0);
            $t->dateTime('pin_locked_until')->nullable();
            $t->string('invite_hash')->nullable();
            $t->dateTime('invite_expires_at')->nullable();
            $t->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('last_seen_at')->nullable();
            $t->timestamps();
            $t->unique(['company_id', 'number']);
        });

        Schema::create('merchant_till_devices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('till_id')->constrained('merchant_tills')->cascadeOnDelete();
            $t->string('device_id', 100);
            $t->char('token_hash', 64)->unique();
            $t->dateTime('unlocked_until')->nullable();            // the cashier PIN opens the till until then
            $t->dateTime('last_used_at')->nullable();
            $t->dateTime('revoked_at')->nullable();
            $t->timestamps();
        });

        Schema::create('merchant_closes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('till_id')->nullable()->constrained('merchant_tills')->nullOnDelete();
            $t->date('day');
            $t->unsignedInteger('sales')->default(0);
            $t->bigInteger('gross')->default(0);
            $t->bigInteger('fees')->default(0);
            $t->bigInteger('refunds')->default(0);
            $t->string('closed_by', 10);                          // cashier | owner | auto
            $t->timestamps();
            $t->index(['company_id', 'day']);
        });

        Schema::create('merchant_sales', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('till_id')->nullable()->constrained('merchant_tills')->nullOnDelete();
            $t->string('method', 10);                              // mpesa (prompt) | qr | link | account
            $t->foreignId('payment_request_id')->nullable()->unique()->constrained('api_payment_requests')->nullOnDelete();
            $t->foreignId('ramp_order_id')->nullable()->unique()->constrained('ramp_orders')->nullOnDelete();
            $t->bigInteger('amount');
            $t->bigInteger('fee')->default(0);                     // Sharet's merchant fee (excise inside)
            $t->bigInteger('refunded')->default(0);
            $t->string('status', 10)->default('pending');          // pending | paid | failed | cancelled
            $t->string('paid_via', 20)->nullable();
            $t->string('description', 140)->nullable();
            $t->dateTime('paid_at')->nullable();
            $t->foreignId('close_id')->nullable()->constrained('merchant_closes')->nullOnDelete();
            $t->timestamps();
            $t->index(['company_id', 'paid_at']);
        });

        Schema::create('merchant_refunds', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('sale_id')->constrained('merchant_sales')->cascadeOnDelete();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('till_id')->nullable()->constrained('merchant_tills')->nullOnDelete();   // asked by this till's cashier
            $t->bigInteger('amount');
            $t->string('reason', 140)->nullable();
            $t->string('status', 10)->default('asked');            // asked | approved | declined | paid | failed
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('decided_at')->nullable();
            $t->foreignId('ramp_order_id')->nullable()->constrained('ramp_orders')->nullOnDelete();
            $t->string('failure', 190)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_refunds');
        Schema::dropIfExists('merchant_sales');
        Schema::dropIfExists('merchant_closes');
        Schema::dropIfExists('merchant_till_devices');
        Schema::dropIfExists('merchant_tills');
    }
};
