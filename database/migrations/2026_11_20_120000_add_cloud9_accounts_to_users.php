<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each person's own account number on Sharet's paybill (Cloud9 issues it: one MAIN wallet, one account number per
 * person). Anyone can pay it from the M-PESA menu and the money lands in that person's Sharet wallet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('cloud9_account_id', 20)->nullable()->unique()->after('kyc_review');
            $t->unsignedBigInteger('cloud9_account')->nullable()->index()->after('cloud9_account_id');
            $t->string('cloud9_paybill', 12)->nullable()->after('cloud9_account');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => [$t->dropUnique(['cloud9_account_id']), $t->dropIndex(['cloud9_account']), $t->dropColumn(['cloud9_account_id', 'cloud9_account', 'cloud9_paybill'])]);
    }
};
