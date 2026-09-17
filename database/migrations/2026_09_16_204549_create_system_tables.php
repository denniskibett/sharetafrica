<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system', function (Blueprint $table) {
            $table->id();

            // Branding
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_dark')->nullable();
            $table->string('logo_icon')->nullable();
            $table->string('favicon')->nullable();
            $table->string('slogan')->nullable();

            // Regional / Localization
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('d-m-Y');
            $table->string('time_format')->default('H:i:s');
            $table->string('currency')->default('KES');
            $table->string('currency_symbol')->default('KSh');

            // Theme
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();

            // Contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->json('location')->nullable();

            // SEO
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // System Configuration
            $table->boolean('maintenance_mode')->default(false);
            $table->integer('pagination_limit')->default(15);

            // Custom Code
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();

            // Flexible Configuration
            $table->json('settings')->nullable();
            $table->json('website_pages')->nullable();
            $table->json('social_media')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems');
    }
};