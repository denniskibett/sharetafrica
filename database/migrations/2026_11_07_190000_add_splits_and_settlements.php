<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API v1 (docs/v2/INTEGRATION.md §4, §5): split settlement on payment requests, and batch settlements (one call, many
 * payments; live batches over the threshold wait for a second approver from the same business).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_payment_requests', function (Blueprint $t) {
            $t->json('splits')->nullable()->after('metadata');           // [{sharet_id, user_id, amount}] resolved at creation; "self" keeps the rest
            $t->string('split_status', 20)->nullable()->after('splits'); // settled, failed
            $t->string('split_failure')->nullable()->after('split_status');
        });

        Schema::create('api_settlements', function (Blueprint $t) {
            $t->id();
            $t->string('public_id', 40)->unique();                         // set_… / set_test_…
            $t->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('environment', 10);
            $t->string('status', 24);          // awaiting_approval, processing, completed, partially_failed, failed, rejected
            $t->json('items');                 // the validated items, kept until it runs
            $t->unsignedInteger('item_count');
            $t->unsignedBigInteger('total');
            $t->string('reference', 140)->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });

        Schema::table('api_payments', function (Blueprint $t) {
            $t->foreignId('api_settlement_id')->nullable()->after('ramp_order_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('api_payments', fn (Blueprint $t) => $t->dropConstrainedForeignId('api_settlement_id'));
        Schema::dropIfExists('api_settlements');
        Schema::table('api_payment_requests', fn (Blueprint $t) => $t->dropColumn(['splits', 'split_status', 'split_failure']));
    }
};
