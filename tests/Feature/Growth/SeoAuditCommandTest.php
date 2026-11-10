<?php

namespace Tests\Feature\Growth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** php artisan growth:seo-audit against faked sites (no real HTTP). */
class SeoAuditCommandTest extends TestCase
{
    use GrowthTables, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createGrowthTables();
    }

    private function goodPage(string $url, string $title): string
    {
        return '<!doctype html><html lang="en-KE"><head><title>'.$title.'</title>'
            .'<meta name="description" content="'.str_repeat('Sell event tickets in Nairobi with M-Pesa. ', 3).'">'
            .'<link rel="canonical" href="'.$url.'"><meta property="og:title" content="x"><meta property="og:image" content="https://aiyotickets.com/og.png">'
            .'<script type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"Organization","name":"aiyotickets"},{"@type":"Event","name":"Jazz"}]}</script>'
            .'</head><body><h1>Events in Nairobi</h1></body></html>';
    }

    public function test_it_records_status_tags_sitemap_and_findings_per_site(): void
    {
        Http::fake([
            'https://aiyotickets.com/robots.txt' => Http::response("User-agent: *\nDisallow: /admin\nSitemap: https://aiyotickets.com/sitemap.xml\n"),
            'https://aiyotickets.com/sitemap.xml' => Http::response('<?xml version="1.0"?><urlset><url><loc>https://aiyotickets.com/</loc></url><url><loc>https://aiyotickets.com/events</loc></url><url><loc>https://aiyotickets.com/hidden</loc></url><url><loc>https://aiyotickets.com/gone</loc></url></urlset>'),
            'https://aiyotickets.com/events' => Http::response($this->goodPage('https://aiyotickets.com/events', 'Events in Nairobi this weekend | aiyotickets')),
            'https://aiyotickets.com/hidden' => Http::response('<html><head><meta name="robots" content="noindex"><title>Hi</title></head><body><h1>a</h1><h1>b</h1></body></html>'),
            'https://aiyotickets.com/gone' => Http::response('Not found', 404),
            'https://aiyotickets.com/' => Http::response($this->goodPage('https://aiyotickets.com/', 'aiyotickets: events and tickets in Nairobi')),
        ]);

        $this->artisan('growth:seo-audit', ['site' => 'aiyotickets'])->assertSuccessful();

        $rows = DB::table('growth_seo_checks')->where('site', 'aiyotickets')->get()->keyBy(fn ($r) => $r->kind === 'page' ? parse_url($r->url, PHP_URL_PATH) : $r->kind);
        $this->assertSame(['home', 'robots', 'sitemap', '/events', '/hidden', '/gone'], $rows->keys()->all());
        $this->assertSame(4, (int) $rows['sitemap']->sitemap_urls);
        $this->assertSame(200, (int) $rows['home']->status);
        $this->assertSame('Organization,Event', $rows['home']->jsonld_types);
        $this->assertSame(1, (int) $rows['home']->h1_count);
        $this->assertSame('https://aiyotickets.com/', $rows['home']->canonical);
        $this->assertStringContainsString('og:image', $rows['home']->og_tags);

        $findings = fn ($k) => collect(json_decode($rows[$k]->findings, true));
        $this->assertSame(0, $findings('home')->where('level', 'red')->count());
        $this->assertTrue($findings('robots')->contains('text', 'robots.txt lists the sitemap.'));
        $this->assertSame(1, (int) $rows['/hidden']->noindex);
        $this->assertTrue($findings('/hidden')->contains(fn ($f) => $f['level'] === 'red' && str_contains($f['text'], 'noindex')));
        $this->assertTrue($findings('/hidden')->contains(fn ($f) => $f['level'] === 'red' && $f['text'] === 'No meta description.'));
        $this->assertTrue($findings('/hidden')->contains('text', '2 h1 headings (use one).'));
        $this->assertSame(404, (int) $rows['/gone']->status);

        // A second run replaces the rows instead of piling up
        $this->artisan('growth:seo-audit', ['site' => 'aiyotickets', '--sample' => 1])->assertSuccessful();
        $this->assertSame(4, DB::table('growth_seo_checks')->where('site', 'aiyotickets')->count());
        Http::assertSent(fn ($r) => $r->hasHeader('User-Agent', \App\Support\Growth\SeoAudit::UA));
    }

    public function test_a_site_that_is_down_or_blocks_everything_is_red(): void
    {
        Http::fake([
            'https://shoope.africa/robots.txt' => Http::response("User-agent: *\nDisallow: /\n"),
            'https://shoope.africa/*' => Http::response('down', 503),
        ]);
        $this->artisan('growth:seo-audit', ['site' => 'shoope'])->assertSuccessful();
        $f = DB::table('growth_seo_checks')->where('site', 'shoope')->get()->flatMap(fn ($r) => json_decode($r->findings, true));
        $this->assertTrue($f->contains('text', 'Answers 503 instead of 200.'));
        $this->assertTrue($f->contains(fn ($x) => $x['level'] === 'red' && str_contains($x['text'], 'Disallow: /')));
        $this->assertTrue($f->contains(fn ($x) => $x['level'] === 'red' && str_contains($x['text'], 'sitemap.xml answers 503')));
    }

    public function test_unknown_site_fails_and_all_sites_run_by_default(): void
    {
        Http::fake(['*' => Http::response('', 500)]);
        $this->artisan('growth:seo-audit', ['site' => 'nope'])->assertFailed();
        $this->artisan('growth:seo-audit')->assertSuccessful();
        $this->assertSame(7, DB::table('growth_seo_checks')->distinct()->count('site'));
    }
}
