<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();

            $table->enum('category', [
                'general',
                'personal',
                'merchant',
                'business',
                'developer',
                'legal',
            ])->default('general');

            $table->string('question');
            $table->text('answer');

            $table->integer('order')->default(0);
            $table->boolean('published')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('published');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};