<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('trade_type', [
                'importer', 'exporter', 'both', 'diaspora', 'intra_africa',
            ])->default('importer');

            $table->string('corridor')->nullable();
            $table->string('monthly_volume')->nullable();
            $table->string('expected_order_size')->nullable();

            $table->enum('need', [
                'payment_abroad', 'receive_from_abroad',
                'intra_africa', 'credit_line', 'all',
            ])->default('payment_abroad');

            $table->text('brief')->nullable();

            $table->enum('status', [
                'new', 'reviewing', 'kyc', 'approved', 'rejected', 'active',
            ])->default('new');

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('trade_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_applications');
    }
};