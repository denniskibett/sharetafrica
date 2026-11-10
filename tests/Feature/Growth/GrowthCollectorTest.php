<?php

namespace Tests\Feature\Growth;

use App\Http\Controllers\GrowthCollectController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/** POST /t/x: the cross-site collector behind public/js/sharet-analytics.js (Administration › Growth). */
class GrowthCollectorTest extends TestCase
{
    use GrowthTables, RefreshDatabase;

    private const UA = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/130.0 Mobile Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createGrowthTables();
    }

    private function beacon(array|string $data, ?string $origin = 'https://breve.sharet.africa', array $headers = [])
    {
        $server = ['CONTENT_TYPE' => 'text/plain;charset=UTF-8', 'HTTP_USER_AGENT' => self::UA];
        if ($origin) {
            $server['HTTP_ORIGIN'] = $origin;
        }
        foreach ($headers as $k => $v) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $k))] = $v;
        }

        return $this->call('POST', '/t/x', [], [], [], $server, is_string($data) ? $data : json_encode($data));
    }

    private function pageView(array $over = []): array
    {
        return $over + ['type' => 'view', 'site' => 'breve', 'key' => 'AbCdEfGh12345678', 'path' => '/pricing', 'vw' => 390,
            'ref' => 'https://www.google.com/search?q=invoices', 'utm' => ['source' => null, 'medium' => null, 'campaign' => null]];
    }

    public function test_a_page_view_from_the_sites_own_origin_is_recorded_and_answered_204_with_cors(): void
    {
        $this->beacon($this->pageView())
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://breve.sharet.africa')
            ->assertHeaderMissing('Set-Cookie');

        $row = DB::table('growth_page_views')->first();
        $this->assertSame('breve', $row->site);
        $this->assertSame('/pricing', $row->path);
        $this->assertSame('google.com', $row->referrer_host);
        $this->assertSame('mobile', $row->device);
        $this->assertSame(16, strlen($row->visitor));
    }

    public function test_only_the_seven_keys_from_their_own_origins_are_accepted(): void
    {
        $this->beacon($this->pageView(), 'https://evil.example')->assertNoContent()->assertHeaderMissing('Access-Control-Allow-Origin');
        $this->beacon($this->pageView(['site' => 'aiyotickets']), 'https://breve.sharet.africa')->assertNoContent(); // wrong origin for the key
        $this->beacon($this->pageView(['site' => 'unknown']), 'https://breve.sharet.africa')->assertNoContent();
        $this->beacon($this->pageView(), null)->assertNoContent(); // no Origin and no Referer
        $this->assertSame(0, DB::table('growth_page_views')->count());

        // www. and the Referer fallback (some privacy setups drop Origin)
        $this->beacon($this->pageView(['site' => 'aiyotickets', 'key' => 'Key0000000000001']), 'https://www.aiyotickets.com')->assertNoContent();
        $this->beacon($this->pageView(['site' => 'shoope', 'key' => 'Key0000000000002']), null, ['Referer' => 'https://shoope.africa/shop/1'])->assertNoContent();
        $this->assertEqualsCanonicalizing(['aiyotickets', 'shoope'], DB::table('growth_page_views')->pluck('site')->all());
    }

    public function test_bad_input_bots_and_do_not_track_are_ignored_but_still_204(): void
    {
        $this->beacon('not json')->assertNoContent();
        $this->beacon(str_repeat('x', 5000))->assertNoContent();
        $this->beacon($this->pageView(['key' => 'short']))->assertNoContent();
        $this->beacon($this->pageView(['type' => 'nonsense']))->assertNoContent();
        $this->beacon($this->pageView(), 'https://breve.sharet.africa', ['DNT' => '1'])->assertNoContent();
        $this->beacon($this->pageView(), 'https://breve.sharet.africa', ['Sec-GPC' => '1'])->assertNoContent();
        $this->call('POST', '/t/x', [], [], [], ['HTTP_ORIGIN' => 'https://breve.sharet.africa', 'HTTP_USER_AGENT' => 'Googlebot/2.1'], json_encode($this->pageView()))->assertNoContent();
        $this->assertSame(0, DB::table('growth_page_views')->count());
        $this->assertSame(0, DB::table('growth_events')->count());
    }

    public function test_paths_are_masked_and_tags_never_carry_personal_data(): void
    {
        $this->beacon($this->pageView(['path' => '/pay/abcdefghijklmnopqrstuvwxyz123?email=a@b.c', 'utm' => ['source' => 'jane@example.com', 'medium' => 'CPC', 'campaign' => 'Call 0712 345 678']]));
        $row = DB::table('growth_page_views')->first();
        $this->assertSame('/pay/:token', $row->path);
        $this->assertNull($row->utm_source);
        $this->assertSame('cpc', $row->utm_medium);
        $this->assertNull($row->utm_campaign);
    }

    public function test_time_on_page_conversions_and_clicks(): void
    {
        $this->beacon($this->pageView());
        $this->beacon(['type' => 'leave', 'site' => 'breve', 'key' => 'AbCdEfGh12345678', 'ms' => 42000, 'scroll' => 80, 'path' => '/pricing']);
        $row = DB::table('growth_page_views')->first();
        $this->assertSame(42000, (int) $row->duration_ms);
        $this->assertSame(80, (int) $row->scroll_pct);

        $ft = ['source' => 'facebook', 'medium' => 'paid_social', 'campaign' => 'sme_nov', 'ref' => '', 'landing' => '/invoicing?x=1'];
        $this->beacon(['type' => 'conversion', 'site' => 'breve', 'name' => 'signup', 'path' => '/welcome', 'ft' => $ft])->assertNoContent();
        $this->beacon(['type' => 'conversion', 'site' => 'breve', 'name' => 'hacked_name', 'path' => '/welcome', 'ft' => $ft])->assertNoContent();
        $this->beacon(['type' => 'click', 'site' => 'breve', 'label' => 'hero_get_started', 'path' => '/'])->assertNoContent();
        $this->beacon(['type' => 'event', 'site' => 'breve', 'name' => 'whatsapp_click', 'path' => '/'])->assertNoContent();

        $conv = DB::table('growth_events')->where('name', 'conversion')->get();
        $this->assertCount(1, $conv, 'only conversion names from config/growth.php');
        $this->assertSame('signup', $conv[0]->label);
        $this->assertSame('facebook', $conv[0]->source);
        $this->assertSame('paid_social', $conv[0]->medium);
        $this->assertSame('sme_nov', $conv[0]->campaign);
        $this->assertSame('/invoicing', $conv[0]->landing_path);
        $this->assertSame('hero_get_started', DB::table('growth_events')->where('name', 'click')->value('label'));
        $this->assertSame(1, DB::table('growth_events')->where('name', 'whatsapp_click')->count());

        // No first touch: a search referrer becomes organic, nothing at all is direct
        $this->beacon(['type' => 'conversion', 'site' => 'breve', 'name' => 'lead', 'path' => '/contact', 'ft' => ['ref' => 'https://www.bing.com/', 'landing' => '/']]);
        $this->beacon(['type' => 'conversion', 'site' => 'breve', 'name' => 'lead', 'path' => '/contact']);
        $this->assertSame([['bing.com', 'organic'], ['direct', null]], DB::table('growth_events')->where('label', 'lead')->orderBy('id')->get()->map(fn ($r) => [$r->source, $r->medium])->all());
    }

    public function test_preflight_answers_allowed_origins_only(): void
    {
        $this->call('OPTIONS', '/t/x', [], [], [], ['HTTP_ORIGIN' => 'https://loans.sharet.africa', 'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST'])
            ->assertNoContent()->assertHeader('Access-Control-Allow-Origin', 'https://loans.sharet.africa')->assertHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->call('OPTIONS', '/t/x', [], [], [], ['HTTP_ORIGIN' => 'https://evil.example'])
            ->assertNoContent()->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_it_throttles_silently_and_records_nothing_before_the_tables_exist(): void
    {
        RateLimiter::increment('sa-collect:'.sha1('127.0.0.1'), 60, GrowthCollectController::PER_MINUTE);
        $this->beacon($this->pageView())->assertNoContent();
        $this->assertSame(0, DB::table('growth_page_views')->count());
        RateLimiter::clear('sa-collect:'.sha1('127.0.0.1'));

        \Illuminate\Support\Facades\Schema::drop('growth_events');
        \Illuminate\Support\Facades\Cache::forget('growth-ready');
        $this->beacon($this->pageView())->assertNoContent();
        $this->assertSame(0, DB::table('growth_page_views')->count());
    }

    public function test_the_script_is_small_cookie_free_and_honours_dnt(): void
    {
        $js = file_get_contents(public_path('js/sharet-analytics.js'));
        $this->assertLessThan(8000, strlen($js));
        $this->assertStringNotContainsString('document.cookie', $js);
        $this->assertStringContainsString('globalPrivacyControl', $js);
        $this->assertStringContainsString("'/t/x'", $js);
        $this->assertStringContainsString("credentials: 'omit'", $js);
    }
}
