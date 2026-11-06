<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mobile app: device tokens (one per signed-in phone, revocable, stored hashed) and idempotency records, so a
 * request the phone retries after a dropped connection is answered from the first result, never run twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('device_id', 100);
            $t->string('name', 100)->nullable();
            $t->string('platform', 20)->nullable();
            $t->char('token_hash', 64)->unique();
            $t->timestamp('last_used_at')->nullable();
            $t->string('last_ip', 45)->nullable();
            $t->timestamp('expires_at');
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'revoked_at']);
        });

        Schema::create('mobile_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('idem_key', 80);
            $t->string('route', 80);
            $t->char('body_hash', 64);
            $t->unsignedSmallInteger('status')->nullable();      // null while running
            $t->longText('response')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'idem_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_requests');
        Schema::dropIfExists('device_tokens');
    }
};
