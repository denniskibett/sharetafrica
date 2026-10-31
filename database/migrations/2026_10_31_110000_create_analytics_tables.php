<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * First-party website analytics. No cookies and no IP addresses are stored: `visitor` is a
 * hash of IP + browser + date + APP_KEY, so it changes daily and cannot be traced back.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->char('visitor', 16)->index();
            $table->char('view_key', 16)->unique(); // from the browser, to attach time-on-page later
            $table->string('path', 255)->index();
            $table->string('referrer_host', 120)->nullable()->index();
            $table->string('utm_source', 80)->nullable();
            $table->string('utm_medium', 80)->nullable();
            $table->string('utm_campaign', 120)->nullable();
            $table->char('country', 2)->nullable();
            $table->string('device', 10); // mobile | tablet | desktop
            $table->string('browser', 20)->nullable();
            $table->unsignedSmallInteger('viewport_w')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedTinyInteger('scroll_pct')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->index();
        });

        Schema::create('page_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255);
            $table->string('device', 10);
            $table->unsignedSmallInteger('x_permille'); // horizontal position, 0–1000 of page width
            $table->unsignedInteger('y');                // pixels from the top of the page
            $table->unsignedInteger('page_h');
            $table->string('target', 120)->nullable();   // link/button text or element, for "most clicked"
            $table->timestamp('created_at');
            $table->index(['path', 'device', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_clicks');
        Schema::dropIfExists('page_views');
    }
};
