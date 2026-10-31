<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('interest', [
                'sandbox', 'production_keys', 'white_label',
                'licensing', 'bespoke_rail',
            ])->default('sandbox');

            $table->text('brief')->nullable();

            $table->string('api_key')->nullable()->unique();
            $table->string('sandbox_key')->nullable()->unique();

            $table->enum('status', [
                'new', 'reviewing', 'approved', 'rejected', 'active',
            ])->default('new');

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('interest');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_applications');
    }
};