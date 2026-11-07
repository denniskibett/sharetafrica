<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Connected accounts (docs/v2/INTEGRATION.md §6): a platform (scharge, Breve…) acts for a person's own Sharet account,
 * only within the scopes that person approved with their PIN, until either side disconnects.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_connections', function (Blueprint $t) {
            $t->id();
            $t->string('public_id', 40)->unique();                                   // con_… / con_test_…
            $t->string('token', 48)->unique();                                       // the consent link
            $t->foreignId('platform_user_id')->constrained('users')->cascadeOnDelete();   // the platform's account (its business wallet)
            $t->foreignId('api_key_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('account_user_id')->nullable()->constrained('users')->cascadeOnDelete(); // who connected
            $t->string('environment', 10);
            $t->json('scopes');                                                      // collect, payout
            $t->string('status', 20)->default('pending');                            // pending, active, declined, revoked
            $t->string('reference', 140)->nullable();                                // the platform's own id for this person
            $t->string('return_url', 500)->nullable();
            $t->timestamp('expires_at');                                             // for the link, while pending
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
            $t->index(['platform_user_id', 'account_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_connections');
    }
};
