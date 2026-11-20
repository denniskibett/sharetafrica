<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business wallets (docs/v2/INTEGRATION.md §1, §2): every company gets its own Sharet account, with the company's Sharet ID
 * and an @name, held as a special account that can never sign in. API keys that belong to a company act for it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_business_wallet')->default(false)->after('company_id');
        });
        Schema::table('companies', function (Blueprint $t) {
            $t->foreignId('wallet_user_id')->nullable()->after('sharet_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('companies', fn (Blueprint $t) => $t->dropConstrainedForeignId('wallet_user_id'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_business_wallet'));
    }
};
