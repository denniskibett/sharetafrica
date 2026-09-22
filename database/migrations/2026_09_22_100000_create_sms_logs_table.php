<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient', 20)->index();
            $table->text('message');
            $table->string('message_type', 30)->default('transactional');
            $table->string('provider', 30)->default('kenyasms');
            $table->string('status', 20)->index();
            $table->string('provider_message_id', 100)->nullable()->index();
            $table->json('provider_response')->nullable();
            $table->text('error')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->string('context', 50)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['recipient', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['context', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};