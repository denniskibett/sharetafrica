<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Search: landing pages, sitemap, robots.txt, structured data, sister links and the analytics snippet/conversions. */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    public static function landings(): array
    {
        return [
            'mpesa api' => ['/mpesa-api', 'M-PESA API for Kenya', 'The M-PESA API for Kenyan developers.'],
            'gateway' => ['/payment-gateway-kenya', 'Payment gateway for Kenya', 'Accept M-PESA payments online.'],
            'bulk' => ['/bulk-payments-kenya', 'Bulk payments and B2C payouts in Kenya', 'Bulk payments to M-PESA and banks, in one call.'],
        ];
    }

    /** All JSON-LD blocks on a page, decoded (fails if any is not valid JSON). */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $this->assertNotEmpty($m[1], 'no JSON-LD on the page');

        return array_map(fn ($json) => json_decode($json, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    private function nodes(string $html): array
    {
        return collect($this->jsonLd($html))->flatMap(fn ($b) => $b['@graph'] ?? [$b])->all();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('landings')]
    public function test_landing_page_has_its_own_title_canonical_h1_faq_and_schema(string $url, string $title, string $h1): void
    {
        $html = $this->get($url)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<title>'.preg_quote($title, '#').'[^<]*</title>#', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.url($url).'" />', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString(e($h1), $html);
        $this->assertStringContainsString('index,follow', $html);
        $this->assertStringContainsString('href="'.route('docs'), $html);

        $nodes = collect($this->nodes($html))->keyBy(fn ($n) => is_array($n['@type']) ? $n['@type'][0] : $n['@type']);
        $this->assertTrue($nodes->has('FAQPage') && $nodes->has('BreadcrumbList') && $nodes->has('WebAPI') && $nodes->has('Organization'));

        // Every FAQ answer in the JSON-LD is also on the page
        foreach ($nodes['FAQPage']['mainEntity'] as $q) {
            $this->assertStringContainsString(e($q['name']), $html);
            $this->assertStringContainsString(e($q['acceptedAnswer']['text']), $html);
        }
        // No card acceptance or licence claims beyond the footer's own wording
        $this->assertStringNotContainsStringIgnoringCase('accept card', $html);
        // No link points nowhere
        $this->assertDoesNotMatchRegularExpression('/<a(?![^>]*(href|tabindex))[^>]*>/', $html);
    }

    public function test_sitemap_lists_public_pages_landings_and_docs_but_nothing_private(): void
    {
        $res = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($res->getContent());
        $this->assertNotFalse($xml);
        $locs = [];
        foreach ($xml->url as $u) {
            $locs[] = (string) $u->loc;
        }
        $this->assertGreaterThan(10, count($locs));

        foreach (['/', '/developers', '/docs', '/mpesa-api', '/payment-gateway-kenya', '/bulk-payments-kenya', '/trade', '/merchants'] as $path) {
            $this->assertContains(rtrim(url($path), '/') ?: url('/'), array_map(fn ($l) => rtrim($l, '/'), $locs), "$path missing");
        }
        foreach ($locs as $loc) {
            $this->assertDoesNotMatchRegularExpression('#/(dashboard|admin|api|login|register|r/|connect/)#', parse_url($loc, PHP_URL_PATH) ?? '/');
            $path = parse_url($loc, PHP_URL_PATH) ?: '/';
            $this->get($path)->assertOk();
        }
    }

    public function test_robots_txt_keeps_public_pages_crawlable_and_private_ones_out(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        $rules = collect(explode("\n", explode('User-agent: GPTBot', $robots)[0]))
            ->filter(fn ($l) => str_starts_with($l, 'Disallow: '))->map(fn ($l) => trim(substr($l, 10)))->values();

        $blocked = function (string $path) use ($rules) {
            foreach ($rules as $rule) {
                $exact = str_ends_with($rule, '$');
                $r = rtrim($rule, '$');
                if ($exact ? $path === $r : str_starts_with($path, $r)) {
                    return true;
                }
            }

            return false;
        };

        // Every sitemap page (incl. /trade, which an old "Disallow: /t" blocked) stays crawlable; JS assets too
        foreach (['/', '/trade', '/personal', '/merchants', '/developers', '/docs', '/docs/errors', '/mpesa-api', '/payment-gateway-kenya',
            '/bulk-payments-kenya', '/company', '/contact', '/blog', '/privacy', '/companies/breve', '/js/sharet-analytics.js', '/css/site.css'] as $public) {
            $this->assertFalse($blocked($public), "$public is blocked");
        }
        foreach (['/dashboard', '/admin/security', '/api/v1/payments', '/api-keys', '/t', '/r/abc', '/connect/abc', '/settlements', '/wallet', '/pay', '/pay/12'] as $private) {
            $this->assertTrue($blocked($private), "$private is crawlable");
        }
        $this->assertStringContainsString('Sitemap: https://sharet.africa/sitemap.xml', $robots);
    }

    public function test_organization_is_sharet_africa_ltd_with_the_sister_products(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $org = collect($this->nodes($html))->first(fn ($n) => in_array('Organization', (array) $n['@type']));

        $this->assertSame('Sharet Africa Ltd', $org['legalName']);
        $hosts = collect($org['subOrganization'])->map(fn ($o) => parse_url($o['url'], PHP_URL_HOST))->all();
        foreach (['breve.sharet.africa', 'aiyotickets.com', 'eserviz.aiyotickets.com', 'scharge.sharet.africa', 'loans.sharet.africa', 'shoope.africa'] as $host) {
            $this->assertContains($host, $hosts);
        }
        $this->assertSame($org['@id'], $org['subOrganization'][0]['parentOrganization']['@id']);
    }

    public function test_footer_links_the_sister_sites_and_landing_pages_on_every_public_layout(): void
    {
        foreach (['/', '/developers', '/docs', '/mpesa-api', '/companies/breve'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('More from Sharet', $html, $url);
            foreach (['https://breve.sharet.africa', 'https://aiyotickets.com', 'https://eserviz.aiyotickets.com', 'https://scharge.sharet.africa', 'https://loans.sharet.africa', 'https://shoope.africa'] as $site) {
                $this->assertStringContainsString('href="'.$site, $html, "$url lacks $site");
            }
            $this->assertStringContainsString('href="'.route('landing.mpesa-api').'"', $html, $url);
            $this->assertDoesNotMatchRegularExpression('#<a [^>]*href="https://(breve|aiyotickets|shoope)[^"]*"[^>]*rel="[^"]*nofollow#', $html);
        }
    }

    public function test_docs_errors_is_canonical_to_docs_and_developers_describe_the_api(): void
    {
        $this->get('/docs/errors')->assertOk()->assertSee('<link rel="canonical" href="'.route('docs').'" />', false);

        $html = $this->get('/developers')->assertOk()->getContent();
        $this->assertStringContainsString('<title>Payment API for Kenya', $html);
        $this->assertTrue(collect($this->nodes($html))->contains(fn ($n) => $n['@type'] === 'WebAPI' && str_ends_with($n['documentation'], '/docs')));
    }

    public function test_private_pages_stay_noindex(): void
    {
        $this->get('/login')->assertOk()->assertSee('noindex', false);
        $this->get('/register')->assertOk()->assertSee('noindex', false);
    }

    public function test_analytics_script_only_in_production_and_points_at_sharet(): void
    {
        $this->get('/mpesa-api')->assertOk()->assertDontSee('sharet-analytics.js', false);

        $this->app['env'] = 'production';
        config(['services.sharet_analytics.url' => 'https://sharet.africa']);
        $this->get('/mpesa-api')->assertOk()
            ->assertSee('<script defer src="https://sharet.africa/js/sharet-analytics.js" data-site="sharet"></script>', false);

        config(['services.sharet_analytics.url' => '']);
        $this->get('/mpesa-api')->assertOk()->assertDontSee('sharet-analytics.js', false);
    }

    public function test_lead_conversion_fires_on_the_form_thank_you(): void
    {
        $this->get('/contact')->assertDontSee("['conversion','lead']", false);
        $this->from('/contact')->post('/contact/send', [
            'audience' => 'individual', 'name' => 'Juma Jones', 'email' => 'juma@gmail.com', 'brief' => 'Hello.',
        ])->assertRedirect('/contact');
        $this->get('/contact')->assertSee("['conversion','lead']", false);
        $this->get('/contact')->assertDontSee("['conversion','lead']", false);
    }

    public function test_signup_conversion_fires_once_after_registering(): void
    {
        $this->post('/register', [
            'name' => 'Wanjiku Mwangi', 'email' => 'wanjiku@gmail.com', 'phone' => '0722000904',
            'password' => 'Str0ng-Passw0rd!x', 'password_confirmation' => 'Str0ng-Passw0rd!x',
        ])->assertRedirect();
        $this->assertSame('signup', session('sa_conversion'));

        $html = $this->followingRedirects()->get('/dashboard')->getContent();
        $this->assertStringContainsString('["conversion","signup"]', str_replace("'", '"', $html));
        $this->assertNull(session('sa_conversion'));
    }

    public function test_api_keys_page_pushes_the_conversion_when_a_key_is_created(): void
    {
        $page = file_get_contents(resource_path('views/api-keys/index.blade.php'));
        $this->assertStringContainsString("push(['conversion', 'api_keys'])", $page);
        $this->assertStringContainsString("@include('partials.sharet-analytics')", $page);
    }
}
