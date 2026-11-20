<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal requests and splits reuse payment requests (same pay page /r/{token}, M-PESA prompt or Sharet wallet + PIN):
 * no API key, kind "personal", a split is several requests sharing one `group`. Declined is a status of its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_payment_requests', function (Blueprint $t) {
            $t->foreignId('api_key_id')->nullable()->change();
            $t->string('kind', 10)->default('api')->after('public_id');      // api | personal
            $t->uuid('group')->nullable()->index()->after('kind');           // a split: one request per person
            $t->dateTime('reminded_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('api_payment_requests', fn (Blueprint $t) => $t->dropColumn(['kind', 'group', 'reminded_at']));
    }
};
