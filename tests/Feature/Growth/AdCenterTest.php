<?php

namespace Tests\Feature\Growth;

use App\Models\User;
use App\Support\Growth\AdCopy;
use App\Support\Growth\AdPlanner;
use App\Support\Growth\GrowthReport;
use App\Support\Growth\Sites;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Growth › Ad center: honest "not enough data", recommendations and budget split from seeded data, ad copy limits. */
class AdCenterTest extends TestCase
{
    use GrowthTables, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
        $this->createGrowthTables();
        $this->admin = User::factory()->create(['name' => 'Dennis Kibett']);
        $this->admin->assignRole('super_admin');
        config(['growth.min_visitors' => 20, 'growth.min_conversions' => 4]);
    }

    public function test_with_no_data_it_says_so_and_uses_the_default_split(): void
    {
        $plan = new AdPlanner(new GrowthReport(30));
        $this->assertFalse($plan->totals()['enough']);
        $this->assertSame('Not enough data to rank yet', $plan->recommendations()->first()[1]);
        $this->assertSame([50.0, 35.0, 15.0], $plan->channelSplit()->pluck('share')->all());
        $this->assertSame(0.0, (float) $plan->siteSplit()->sum('share'), 'no site sends data: nothing to split');

        $this->actingAs($this->admin)->get('/admin/growth/ads?budget=60000')->assertOk()
            ->assertSee('Not enough data to rank yet')->assertSee('No tracker data yet')->assertSee(AdPlanner::RULES['fallback'])
            ->assertSee('Government Errands Done')->assertSee('Safe Payments for Shops')->assertSee('KES 30,000');
    }

    public function test_with_data_it_pushes_what_converts_and_flags_pages_that_do_not(): void
    {
        // aiyotickets: 20 visits from Google ads to /events, 8 purchases. breve: 10 Facebook visits, 1 sign-up. loans: 40 views of /apply-info, nothing.
        for ($i = 0; $i < 20; $i++) {
            $v = sprintf('a%015d', $i);
            $this->seedVisit('aiyotickets', $v, ['/events'], ['utm_source' => 'google', 'utm_medium' => 'cpc']);
            if ($i < 8) {
                $this->seedConversion('aiyotickets', 'purchase', $v, '/events', 'google', 'cpc');
            }
        }
        for ($i = 0; $i < 10; $i++) {
            $this->seedVisit('breve', sprintf('b%015d', $i), ['/'], ['referrer_host' => 'facebook.com']);
        }
        $this->seedConversion('breve', 'signup', 'b000000000000000', '/', 'facebook.com', 'referral');
        for ($i = 0; $i < 40; $i++) {
            $this->seedVisit('loans', sprintf('l%015d', $i), ['/apply-info']);
        }

        $plan = new AdPlanner(new GrowthReport(30));
        $this->assertTrue($plan->totals()['enough']);
        $recs = $plan->recommendations();
        $this->assertTrue($recs->contains(fn ($r) => $r[0] === 'push' && str_contains($r[1], 'aiyotickets · /events from google')));
        $this->assertTrue($recs->contains(fn ($r) => $r[0] === 'fix' && str_contains($r[1], '/apply-info')));
        $this->assertTrue($recs->contains(fn ($r) => $r[0] === 'fix' && str_contains($r[1], 'Shoope: no data yet')));

        $sites = $plan->siteSplit(100000)->keyBy('key');
        $this->assertEqualsWithDelta(100, $sites->sum('share'), 0.5);
        $this->assertGreaterThan($sites['breve']->share, $sites['aiyotickets']->share);
        $this->assertSame(0.0, (float) $sites['shoope']->share);
        $this->assertEqualsWithDelta(70 * 8 / 9 + 10, $sites['aiyotickets']->share, 0.1, '70% by conversions + 30% evenly over 3 live sites');

        $channels = $plan->channelSplit()->keyBy('key');
        $this->assertTrue($channels['google']->from_data);
        $this->assertEqualsWithDelta(100, $channels->sum('share'), 0.5);
        $this->assertGreaterThan($channels['meta']->share, $channels['google']->share);

        $this->actingAs($this->admin)->get('/admin/growth/ads')->assertOk()
            ->assertSee(AdPlanner::RULES['sites'])->assertSee('/apply-info')->assertSee('Best channel: Paid ads');
        $this->actingAs($this->admin)->get('/admin/growth/ads?site=breve')->assertOk()->assertSee('Invoices Paid by M-Pesa')->assertDontSee('Government Errands Done');
    }

    public function test_ad_copy_fits_google_limits_for_every_site(): void
    {
        foreach (Sites::keys() as $site) {
            $c = AdCopy::for($site);
            $this->assertNotEmpty($c['headlines'], $site);
            foreach ($c['headlines'] as $h) {
                $this->assertLessThanOrEqual(30, mb_strlen($h), "$site: $h");
            }
            foreach ($c['descriptions'] as $d) {
                $this->assertLessThanOrEqual(90, mb_strlen($d), "$site: $d");
            }
            $this->assertNotSame('', $c['meta']);
        }
    }
}
