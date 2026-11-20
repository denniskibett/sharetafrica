<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** v2 reconciliation: provider statement lines matched to the ledger, and a daily two-person sign-off. New tables only. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statement_lines', function (Blueprint $t) {
            $t->id();
            $t->string('rail', 32);
            $t->date('day');
            $t->string('line_ref', 191);                  // the provider's own reference for the line
            $t->string('direction', 3);                   // in | out
            $t->unsignedBigInteger('amount');             // cents
            $t->char('currency', 3);
            $t->string('party', 64)->nullable();
            $t->string('receipt', 64)->nullable();
            $t->dateTime('occurred_at');
            $t->json('raw')->nullable();
            // matched | missed (on statement, not booked) | amount (amounts differ) | unknown (no request of ours) | fixed | parked
            $t->string('status', 16)->default('unknown');
            $t->unsignedBigInteger('rail_request_id')->nullable();
            $t->unsignedBigInteger('journal_id')->nullable();
            $t->string('note')->nullable();
            $t->unsignedBigInteger('resolved_by')->nullable();
            $t->dateTime('resolved_at')->nullable();
            $t->timestamps();
            $t->unique(['rail', 'line_ref']);
            $t->index(['rail', 'day', 'status']);
        });

        Schema::create('recon_days', function (Blueprint $t) {
            $t->id();
            $t->string('rail', 32);
            $t->date('day');
            $t->string('status', 16)->default('open');    // open | prepared | signed_off
            $t->json('summary')->nullable();
            $t->dateTime('last_run_at')->nullable();
            $t->unsignedBigInteger('prepared_by')->nullable();
            $t->dateTime('prepared_at')->nullable();
            $t->unsignedBigInteger('signed_off_by')->nullable();
            $t->dateTime('signed_off_at')->nullable();
            $t->timestamps();
            $t->unique(['rail', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recon_days');
        Schema::dropIfExists('statement_lines');
    }
};
