<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 · Business and Trade in the app (canvas "Phase 2 · Mobile app · Business" and "· Trade").
 *
 *  - business_members: who can act for a company's wallet, and how (owner, maker, checker, viewer), with a checker's
 *    approval limit. Separate from users.company_id so adding someone never changes their personal account.
 *  - business_batches / business_batch_lines: bulk payouts. A maker prepares, a different person approves with their
 *    PIN (two approvers above a threshold); each line is then paid as its own order from the company wallet.
 *  - trade_orders: an LPO from an importer to a verified supplier on Sharet, the supplier's invoice on it, and the
 *    payment (wallet to wallet) with the LPO and invoice numbers on it.
 *  - trade_fx_quotes: a locked FX quote for paying a supplier abroad. No payout partner abroad yet, so it stops at the
 *    quote: nothing is ever taken from the wallet for it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('role', 12);                                   // owner, maker, checker, viewer
            $t->unsignedBigInteger('approval_limit')->nullable();     // cents; null = no limit of their own (owner, or unlimited checker)
            $t->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['company_id', 'user_id']);
        });

        Schema::create('business_batches', function (Blueprint $t) {
            $t->id();
            $t->string('public_id', 32)->unique();                    // bat_…
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('wallet_user_id')->constrained('users')->cascadeOnDelete();
            $t->string('name', 80);
            $t->string('status', 24);          // awaiting_approval, processing, completed, partially_failed, failed, rejected, cancelled
            $t->unsignedInteger('item_count');
            $t->unsignedBigInteger('amount');  // what the people get
            $t->unsignedBigInteger('fees');    // fees, excise and rounding, from the business tariff
            $t->unsignedBigInteger('total');   // what leaves the wallet
            $t->unsignedTinyInteger('approvals_needed')->default(1);
            $t->json('approvals')->nullable(); // [{user_id, name, at}]
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('reject_reason', 200)->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamps();
            $t->index(['company_id', 'status']);
        });

        Schema::create('business_batch_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_batch_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('line');
            $t->string('name', 120)->nullable();
            $t->string('type', 12);            // sharet, mpesa, airtel, tkash, paybill, till, bank (API types)
            $t->json('to');                    // the destination as sent ({type, phone} …)
            $t->string('payee_key', 120);      // normalised destination, for duplicates and new payees
            $t->unsignedBigInteger('amount');
            $t->unsignedBigInteger('fee');     // fee + excise + rounding
            $t->json('flags')->nullable();     // duplicate_in_batch, paid_this_month, new_payee
            $t->string('status', 16)->default('pending');   // pending, paying, paid, failed, returned
            $t->foreignId('ramp_order_id')->nullable()->constrained()->nullOnDelete();
            $t->string('failure', 200)->nullable();
            $t->timestamps();
            $t->index(['payee_key']);
        });

        Schema::create('trade_orders', function (Blueprint $t) {
            $t->id();
            $t->string('number', 20);                                 // LPO 0047 (per importer)
            $t->foreignId('importer_company_id')->constrained('companies')->cascadeOnDelete();
            $t->foreignId('supplier_company_id')->constrained('companies')->cascadeOnDelete();
            $t->string('items', 400);
            $t->unsignedBigInteger('amount');                         // cents, KES
            $t->string('currency', 3)->default('KES');
            $t->string('terms', 20);                                  // on_delivery, 30_days, 60_days
            $t->string('status', 16);                                 // sent, invoiced, paid, declined, cancelled
            $t->string('invoice_number', 40)->nullable();
            $t->unsignedBigInteger('invoice_amount')->nullable();
            $t->date('invoice_due')->nullable();
            $t->timestamp('invoiced_at')->nullable();
            $t->foreignId('ramp_order_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('paid_at')->nullable();
            $t->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->string('decline_reason', 200)->nullable();
            $t->timestamps();
            $t->unique(['importer_company_id', 'number']);
        });

        Schema::create('trade_fx_quotes', function (Blueprint $t) {
            $t->id();
            $t->string('public_id', 32)->unique();                    // fxq_…
            $t->foreignId('company_id')->constrained()->cascadeOnDelete();
            $t->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $t->string('currency', 3);                                // what the supplier abroad is paid in
            $t->unsignedBigInteger('amount');                         // minor units of that currency
            $t->unsignedBigInteger('kes_amount');                     // what it costs from the wallet, cents
            $t->string('kes_per_unit', 24);                           // the locked customer rate
            $t->string('reference_rate', 24);
            $t->string('rate_source', 20)->nullable();
            $t->string('supplier_name', 120);
            $t->string('supplier_country', 2);
            $t->string('invoice_reference', 60);
            $t->foreignId('trade_order_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamp('expires_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_fx_quotes');
        Schema::dropIfExists('trade_orders');
        Schema::dropIfExists('business_batch_lines');
        Schema::dropIfExists('business_batches');
        Schema::dropIfExists('business_members');
    }
};
