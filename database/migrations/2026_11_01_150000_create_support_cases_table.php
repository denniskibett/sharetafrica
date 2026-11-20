<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The support desk's cases (design: DashSupport / JourneySupport). Additive only. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_cases', function (Blueprint $table) {
            $table->id();
            $table->string('number', 12)->nullable()->unique(); // CS-10001
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // the customer
            $table->foreignId('ramp_order_id')->nullable()->constrained('ramp_orders')->nullOnDelete();
            $table->string('channel', 12)->default('system'); // system | whatsapp | phone | email
            $table->string('reason', 32); // LATE_CONFIRMATION, PAYOUT_UNKNOWN, STK_NOT_RECEIVED, ...
            $table->string('summary', 255);
            $table->string('status', 12)->default('new'); // new | open | with_ops | waiting | closed
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sla_due_at')->nullable();
            $table->text('close_note')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['ramp_order_id', 'reason']); // one case per payment and reason
            $table->index(['status', 'sla_due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_cases');
    }
};
