<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corridors', function (Blueprint $table) {
            $table->id();

            // Display
            $table->string('label');                    // "Kenya ↔ Tanzania"
            $table->string('code')->unique();           // "KE-TZ"
            $table->string('group')->nullable();        // "East Africa", "Asia", "Europe", ...

            // Behaviour
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);

            // Optional metadata for reporting
            $table->string('origin_country', 2)->nullable();
            $table->string('destination_country', 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('group');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corridors');
    }
};