<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A phone whose own lock (fingerprint, face or passcode) approves payments from the app, set with the Sharet PIN. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_tokens', fn (Blueprint $t) => $t->boolean('phone_lock')->default(false));
    }

    public function down(): void
    {
        Schema::table('device_tokens', fn (Blueprint $t) => $t->dropColumn('phone_lock'));
    }
};
