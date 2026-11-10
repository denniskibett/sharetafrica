<?php

namespace Tests\Feature\Growth;

use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Administration › Growth: access, the four tabs, the site / period filters and the owner to-do list. */
class GrowthHubTest extends TestCase
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
    }

    public function test_only_staff_with_analytics_access_see_it(): void
    {
        $this->get('/admin/growth')->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['name' => 'Juma Jones']))->get('/admin/growth')->assertForbidden();

        $analyst = User::factory()->create(['name' => 'Akinyi Analyst']);
        $analyst->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('analytics.view', 'web'));
        $this->actingAs($analyst)->get('/admin/growth')->assertOk();
    }

    public function test_every_tab_renders_empty_and_with_data(): void
    {
        foreach (['/admin/growth', '/admin/growth/conversions', '/admin/growth/seo', '/admin/growth/ads'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk()->assertSee('Growth')->assertSee('Shoope');
        }

        $this->seedVisit('aiyotickets', 'v000000000000001', ['/events', '/events/nairobi-jazz'], ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'jazz_nov']);
        $this->seedVisit('breve', 'v000000000000002', ['/'], ['referrer_host' => 'google.com']);
        $this->seedConversion('aiyotickets', 'purchase', 'v000000000000001', '/events', 'google', 'cpc', 'jazz_nov');

        $this->actingAs($this->admin)->get('/admin/growth')->assertOk()
            ->assertSee('aiyotickets')->assertSee('Never: tracker not installed')->assertSee('Organic search');
        $this->actingAs($this->admin)->get('/admin/growth/conversions')->assertOk()
            ->assertSee('Purchase (ticket order paid)')->assertSee('jazz_nov')->assertSee('/events');
        $this->actingAs($this->admin)->get('/admin/growth/ads')->assertOk()->assertSee('What to push')->assertSee('UTM link builder');
    }

    public function test_the_site_and_period_filters(): void
    {
        $this->seedVisit('aiyotickets', 'v000000000000001', ['/events-only-aiyo']);
        $this->seedVisit('breve', 'v000000000000002', ['/breve-only-page']);
        $this->seedVisit('breve', 'v000000000000003', ['/old-breve-page'], [], now()->subDays(20)->toDateTimeString());

        $this->actingAs($this->admin)->get('/admin/growth/conversions?site=breve')->assertOk()
            ->assertSee('/breve-only-page')->assertDontSee('/events-only-aiyo')->assertSee('/old-breve-page');
        $this->actingAs($this->admin)->get('/admin/growth/conversions?site=breve&days=7')->assertOk()
            ->assertSee('/breve-only-page')->assertDontSee('/old-breve-page');
        // Unknown site / period fall back to all sites / 30 days
        $this->actingAs($this->admin)->get('/admin/growth/conversions?site=nope&days=3')->assertOk()->assertSee('/events-only-aiyo')->assertSee('/old-breve-page');
    }

    public function test_seo_tab_shows_the_audit_and_saves_owner_todos(): void
    {
        DB::table('growth_seo_checks')->insert(['site' => 'loans', 'kind' => 'home', 'url' => 'https://loans.sharet.africa/', 'status' => 200, 'response_ms' => 300,
            'title' => 'Sharet Loans', 'h1_count' => 2, 'noindex' => 1, 'findings' => json_encode([['level' => 'red', 'text' => 'noindex: search engines are told not to list this page.']]), 'checked_at' => now()]);

        $this->actingAs($this->admin)->get('/admin/growth/seo?site=loans')->assertOk()
            ->assertSee('noindex: search engines are told not to list this page.')->assertSee('Search Console: domain verified')->assertDontSee('aiyotickets.com</a>', false);

        $this->actingAs($this->admin)->post('/admin/growth/todos', ['site' => 'loans', 'item' => 'search_console', 'done' => '1', 'note' => 'TXT added 10 Nov', 'back_site' => 'loans'])
            ->assertRedirect('/admin/growth/seo?site=loans');
        $row = DB::table('growth_todos')->where('site', 'loans')->where('item', 'search_console')->first();
        $this->assertSame(1, (int) $row->done);
        $this->assertSame('TXT added 10 Nov', $row->note);
        $this->assertSame($this->admin->id, (int) $row->updated_by);

        $this->actingAs($this->admin)->post('/admin/growth/todos', ['site' => 'loans', 'item' => 'search_console', 'done' => '0']);
        $this->assertSame(0, (int) DB::table('growth_todos')->where('site', 'loans')->value('done'));
        $this->assertSame(1, DB::table('growth_todos')->count());

        $this->actingAs($this->admin)->post('/admin/growth/todos', ['site' => 'evil', 'item' => 'search_console'])->assertSessionHasErrors('site');
    }

    public function test_it_explains_how_to_set_up_before_the_sql_is_applied(): void
    {
        Schema::drop('growth_todos');
        Cache::forget('growth-ready');
        $this->actingAs($this->admin)->get('/admin/growth/ads')->assertOk()->assertSee('2026_11_10_growth_hub.sql');
    }

    public function test_it_is_in_the_administration_menu(): void
    {
        $this->actingAs($this->admin)->get('/admin/growth')->assertSee(route('admin.growth.index'), false);
    }
}
