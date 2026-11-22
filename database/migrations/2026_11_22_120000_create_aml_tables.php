<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AML: every screening that hit a rule (held or blocked), its review, and the watch list staff keep (phones, paybills,
 * tills, bank accounts, Sharet IDs that must never be paid or pay). Orders held for review get status "held".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aml_checks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ramp_order_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('decision', 10);                     // hold | block
            $t->json('hits');                               // [{code, label}]
            $t->unsignedBigInteger('amount');
            $t->string('status', 10)->default('pending')->index();   // pending | approved | rejected | closed
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('reviewed_at')->nullable();
            $t->string('note', 300)->nullable();
            $t->timestamps();
        });
        Schema::create('aml_watchlist', function (Blueprint $t) {
            $t->id();
            $t->string('type', 12);                         // phone | paybill | till | account | sharet_id
            $t->string('value', 40);
            $t->string('reason', 200);
            $t->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aml_watchlist');
        Schema::dropIfExists('aml_checks');
    }
};
