<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quick pay: a phone the person trusts can approve small payments with a fingerprint instead of the PIN, up to a limit
 * they set (with their PIN) for that phone. Freeze: one tap stops every payment out of the wallet; money can still come in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_tokens', fn (Blueprint $t) => $t->unsignedInteger('quick_pay_limit')->nullable());   // cents; null = off
        Schema::table('users', fn (Blueprint $t) => $t->dateTime('frozen_at')->nullable()->after('kyc_review'));
    }

    public function down(): void
    {
        Schema::table('device_tokens', fn (Blueprint $t) => $t->dropColumn('quick_pay_limit'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('frozen_at'));
    }
};
