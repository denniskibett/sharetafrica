<?php

namespace Tests\Feature\Growth;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** GET /api/v1/growth/summary: a business's own site numbers for its localized Growth tab, with its Sharet API key. */
class GrowthApiTest extends TestCase
{
    use GrowthTables, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LedgerAccountSeeder::class);
        $this->createGrowthTables();
    }

    private function key(?Company $company, array $scopes = ['read']): string
    {
        $user = User::factory()->create();
        $k = new ApiKey(['user_id' => $user->id, 'company_id' => $company?->id, 'name' => 'growth', 'environment' => 'live', 'scopes' => $scopes, 'allowed_ips' => ['127.0.0.1'], 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        return $plain;
    }

    public function test_a_business_key_reads_only_its_own_site(): void
    {
        $breve = Company::create(['name' => 'Breve']);
        config(['growth.sites.breve.sharet_id' => $breve->sharet_id]);
        $h = ['Authorization' => 'Bearer '.$this->key($breve)];

        $this->seedVisit('breve', 'b000000000000001', ['/invoicing', '/pricing'], ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'sme']);
        $this->seedVisit('breve', 'b000000000000002', ['/'], ['referrer_host' => 'google.com']);
        $this->seedConversion('breve', 'signup', 'b000000000000001', '/invoicing', 'google', 'cpc', 'sme');
        $this->seedVisit('aiyotickets', 'a000000000000001', ['/secret-aiyo-page']);
        $this->seedConversion('aiyotickets', 'purchase', 'a000000000000001', '/secret-aiyo-page');
        DB::table('growth_seo_checks')->insert(['site' => 'breve', 'kind' => 'home', 'url' => 'https://breve.sharet.africa/', 'status' => 200, 'noindex' => 0,
            'findings' => json_encode([['level' => 'amber', 'text' => 'No canonical link.'], ['level' => 'green', 'text' => 'One h1.']]), 'checked_at' => now()]);

        $res = $this->getJson('/api/v1/growth/summary', $h)->assertOk()
            ->assertJsonPath('object', 'growth.summary')->assertJsonPath('version', 1)
            ->assertJsonCount(1, 'sites')
            ->assertJsonPath('sites.0.key', 'breve')
            ->assertJsonPath('sites.0.totals.visits', 2)
            ->assertJsonPath('sites.0.totals.page_views', 3)
            ->assertJsonPath('sites.0.totals.conversions', 1)
            ->assertJsonPath('sites.0.totals.conversion_rate', 50)
            ->assertJsonPath('sites.0.conversions.0.name', 'signup')
            ->assertJsonPath('sites.0.landing_pages.0.path', '/invoicing')
            ->assertJsonPath('sites.0.seo.counts.amber', 1)
            ->assertJsonPath('sites.0.seo.fix_first.0.text', 'No canonical link.')
            ->assertJsonCount(30, 'sites.0.daily')
            ->assertJsonStructure(['from', 'to', 'generated_at', 'sites' => [['name', 'url', 'last_data_at', 'channels' => [['channel', 'label', 'visits', 'share', 'conversions', 'conversion_rate']], 'sources']]]);
        $this->assertStringNotContainsString('secret-aiyo-page', $res->getContent());
        $this->assertStringNotContainsString('purchase', $res->getContent());

        $this->getJson('/api/v1/growth/summary?from='.now()->subDays(6)->toDateString().'&to='.now()->toDateString(), $h)->assertOk()->assertJsonCount(7, 'sites.0.daily');
        $this->getJson('/api/v1/growth/summary?site=aiyotickets', $h)->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#growth_other_site');
        $this->getJson('/api/v1/growth/summary?from=2026-01-01&to=2025-01-01', $h)->assertUnprocessable();
        $this->getJson('/api/v1/growth/summary?from=yesterday', $h)->assertUnprocessable();
    }

    public function test_keys_without_a_linked_site_or_without_read_scope_are_refused(): void
    {
        $this->getJson('/api/v1/growth/summary')->assertUnauthorized();
        $this->getJson('/api/v1/growth/summary', ['Authorization' => 'Bearer '.$this->key(Company::create(['name' => 'Mwangi Hardware']))])
            ->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#growth_no_site');
        $this->getJson('/api/v1/growth/summary', ['Authorization' => 'Bearer '.$this->key(null)])->assertForbidden();

        $loans = Company::create(['name' => 'Sharet Loans']);
        config(['growth.sites.loans.sharet_id' => $loans->sharet_id]);
        $this->getJson('/api/v1/growth/summary', ['Authorization' => 'Bearer '.$this->key($loans, ['collect'])])->assertForbidden();
        $this->getJson('/api/v1/growth/summary', ['Authorization' => 'Bearer '.$this->key($loans)])->assertOk()->assertJsonPath('sites.0.key', 'loans');
    }
}
