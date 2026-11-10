<?php

namespace Tests\Feature\Growth;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The growth_* tables are hand-applied SQL (database/schema/2026_11_10_growth_hub.sql), not migrations, so tests
 * create the same columns here. Keep in step with the SQL file.
 */
trait GrowthTables
{
    protected function createGrowthTables(): void
    {
        Schema::create('growth_page_views', function (Blueprint $t) {
            $t->id();
            $t->string('site', 20);
            $t->char('visitor', 16);
            $t->char('view_key', 16)->unique();
            $t->string('path', 255);
            $t->string('referrer_host', 120)->nullable();
            $t->string('utm_source', 80)->nullable();
            $t->string('utm_medium', 80)->nullable();
            $t->string('utm_campaign', 120)->nullable();
            $t->char('country', 2)->nullable();
            $t->string('device', 10);
            $t->string('browser', 20)->nullable();
            $t->unsignedSmallInteger('viewport_w')->nullable();
            $t->unsignedInteger('duration_ms')->nullable();
            $t->unsignedTinyInteger('scroll_pct')->nullable();
            $t->timestamp('created_at');
        });
        Schema::create('growth_events', function (Blueprint $t) {
            $t->id();
            $t->string('site', 20);
            $t->string('name', 40);
            $t->string('label', 190)->nullable();
            $t->string('path', 255)->nullable();
            $t->char('visitor', 16)->nullable();
            $t->string('source', 120)->nullable();
            $t->string('medium', 80)->nullable();
            $t->string('campaign', 120)->nullable();
            $t->string('landing_path', 255)->nullable();
            $t->string('device', 10)->nullable();
            $t->timestamp('created_at');
        });
        Schema::create('growth_seo_checks', function (Blueprint $t) {
            $t->id();
            $t->string('site', 20);
            $t->string('kind', 10);
            $t->string('url', 500);
            $t->unsignedSmallInteger('status')->nullable();
            $t->unsignedInteger('response_ms')->nullable();
            $t->string('title', 255)->nullable();
            $t->unsignedSmallInteger('description_length')->nullable();
            $t->string('canonical', 500)->nullable();
            $t->string('og_tags', 255)->nullable();
            $t->string('jsonld_types', 255)->nullable();
            $t->unsignedSmallInteger('h1_count')->nullable();
            $t->boolean('noindex')->default(false);
            $t->unsignedInteger('sitemap_urls')->nullable();
            $t->text('findings')->nullable();
            $t->timestamp('checked_at');
        });
        Schema::create('growth_todos', function (Blueprint $t) {
            $t->id();
            $t->string('site', 20);
            $t->string('item', 40);
            $t->boolean('done')->default(false);
            $t->string('note', 255)->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamp('updated_at')->nullable();
            $t->unique(['site', 'item']);
        });
        Cache::forget('growth-ready');
    }

    /** A visit: one page view per path, same visitor, with optional utm / referrer on the first one. */
    protected function seedVisit(string $site, string $visitor, array $paths, array $first = [], ?string $at = null): void
    {
        foreach (array_values($paths) as $i => $path) {
            DB::table('growth_page_views')->insert(($i === 0 ? $first : []) + [
                'site' => $site, 'visitor' => $visitor, 'view_key' => substr(md5($site.$visitor.$path.$i.($at ?? '').microtime()), 0, 16),
                'path' => $path, 'device' => 'mobile', 'duration_ms' => 20000, 'created_at' => $at ?? now()->subHours(2)->addSeconds($i),
            ]);
        }
    }

    protected function seedConversion(string $site, string $name, string $visitor, string $landing, string $source = 'direct', ?string $medium = null, ?string $campaign = null): void
    {
        DB::table('growth_events')->insert([
            'site' => $site, 'name' => 'conversion', 'label' => $name, 'path' => '/thanks', 'visitor' => $visitor,
            'source' => $source, 'medium' => $medium, 'campaign' => $campaign, 'landing_path' => $landing, 'device' => 'mobile', 'created_at' => now()->subHour(),
        ]);
    }
}
