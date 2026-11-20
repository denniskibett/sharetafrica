<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wallet payments. ramp_orders.funding: where the money comes from (the payer's M-PESA, collected first, or their
 * Sharet wallet 2100). recipient_user_id: the other Sharet user on a wallet-to-wallet transfer. users.handle: the
 *
 * @name people send to. Additive only (v1 keeps working).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ramp_orders', function (Blueprint $table) {
            $table->string('funding', 10)->default('mpesa')->after('payer_phone');
            $table->foreignId('recipient_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('handle', 32)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('ramp_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_user_id');
            $table->dropColumn('funding');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['handle']);
            $table->dropColumn('handle');
        });
    }
};
