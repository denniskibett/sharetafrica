<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Website contact form. Stored first so no message is lost if email is down. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('audience', 120);
            $table->string('name', 120);
            $table->string('email', 190)->index();
            $table->string('phone', 40)->nullable();
            $table->string('business', 160)->nullable();
            $table->string('country', 60)->nullable();
            $table->text('brief');
            $table->timestamp('thanked_at')->nullable();  // thank-you email sent
            $table->timestamp('notified_at')->nullable(); // team copy sent
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
