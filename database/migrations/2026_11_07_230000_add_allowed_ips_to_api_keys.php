<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Live payout keys only work from the server addresses their business lists (docs/v2/INTEGRATION.md §2). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $t) {
            $t->json('allowed_ips')->nullable()->after('scopes');   // ["41.90.12.7", "196.201.0.0/16"]
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', fn (Blueprint $t) => $t->dropColumn('allowed_ips'));
    }
};
