<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unfreeze with a selfie: a frozen person asks to unfreeze with their PIN and a fresh selfie, and waits for a person in
 * Administration › Verifications to compare it with their verification selfie. Set while that request waits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dateTime('unfreeze_requested_at')->nullable()->after('frozen_at'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('unfreeze_requested_at'));
    }
};
