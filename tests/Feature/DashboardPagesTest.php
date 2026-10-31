<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\TeamMember;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Dashboard pages render, and their JSON endpoints return what the Alpine tables and charts expect. */
class DashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $user = User::factory()->create(['onboarding_status' => 'active', 'intent' => 'individual', 'phone' => '+254712000001']);
        $user->assignRole(Role::findOrCreate('super_admin', 'web'));

        return $user;
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($admin = $this->admin());

        foreach (['/dashboard', '/users', '/reports', '/admin/waiting-list', '/admin/roles', '/admin/team', '/admin/blog', '/admin/blog/new', '/profile', '/profile/edit', '/settings/business', '/admin/enquiries'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_tables_and_charts_return_json(): void
    {
        $this->actingAs($this->admin());
        User::factory()->count(3)->create(['onboarding_status' => 'waiting_list', 'intent' => 'merchant']);

        $this->getJson('/users?lane=merchant')->assertOk()->assertJsonPath('meta.total', 3)->assertJsonStructure(['data' => [['id', 'person' => ['name', 'initials'], 'status' => ['label', 'tone']]], 'stats', 'extra']);
        $this->getJson('/admin/waiting-list')->assertOk()->assertJsonPath('stats.waiting', 3);
        $this->getJson('/dashboard/data')->assertOk()->assertJsonStructure(['stats' => ['people', 'waiting', 'active', 'companies']]);
        foreach (['signups', 'lanes', 'funnel', 'money'] as $chart) {
            $this->getJson("/dashboard/chart/$chart")->assertOk()->assertJsonStructure(['type', 'series']);
        }
        $this->getJson('/admin/roles')->assertOk()->assertJsonStructure(['roles', 'groups', 'stats']);
    }

    public function test_verification_sets_the_account_level(): void
    {
        $this->actingAs($user = $this->admin());

        $this->putJson('/profile/verification', ['id_type' => 'national_id', 'id_number' => '12345678', 'date_of_birth' => '1990-05-01'])->assertOk();
        $this->assertSame('basic', $user->fresh()->kyc_level);
        $this->assertStringNotContainsString('12345678', (string) $user->fresh()->getRawOriginal('kyc'));

        $this->putJson('/profile/verification', ['id_type' => 'national_id', 'id_number' => '12345678', 'date_of_birth' => '2015-01-01'])
            ->assertStatus(422)->assertJsonValidationErrors('date_of_birth');
        $this->getJson('/profile/data')->assertOk()->assertJsonPath('level.key', 'basic')->assertJsonPath('kyc.id_number', '12345678');
    }

    public function test_business_settings_create_a_company(): void
    {
        $this->actingAs($user = User::factory()->create(['onboarding_status' => 'active', 'intent' => 'merchant', 'phone' => null]));
        $this->putJson('/settings/business', ['name' => 'Duka Ltd'])->assertStatus(422); // needs a personal account first
        $user->update(['phone' => '+254711000222']);

        $this->putJson('/settings/business', ['name' => 'Duka Ltd', 'tax_id' => 'p051234567x', 'directors' => [['name' => 'Jane Wanjiku', 'id_number' => '22334455']], 'settlement' => ['method' => 'bank', 'account_number' => '0123456789']])->assertOk();
        $company = $user->fresh()->company;
        $this->assertSame('Duka Ltd', $company->name);
        $this->assertSame('P051234567X', $company->tax_id);
        $this->getJson('/settings/business')->assertOk()->assertJsonPath('company.directors.0.name', $user->name)->assertJsonPath('company.directors.0.self', true)->assertJsonPath('company.directors.1.name', 'Jane Wanjiku')->assertJsonPath('company.settlement.account_number', '0123456789');
    }

    public function test_team_shows_on_the_company_page_and_blog_publishes(): void
    {
        $this->actingAs($this->admin());

        $this->postJson('/admin/team', ['name' => 'Paul Pennuel', 'role' => 'CTO', 'published' => 1])->assertOk();
        $this->get('/company')->assertOk()->assertSee('Paul Pennuel')->assertSee('CTO');

        $this->postJson('/admin/blog', ['title' => 'Paying suppliers in Guangzhou', 'status' => 'published', 'body' => '<h2>Why</h2><p>Hello <script>alert(1)</script><a href="javascript:x">bad</a></p>'])->assertOk();
        $post = Post::firstOrFail();
        $this->assertSame('paying-suppliers-in-guangzhou', $post->slug);
        $this->assertStringNotContainsString('script', $post->body);
        $this->assertStringNotContainsString('javascript:', $post->body);
        $this->get('/blog')->assertOk()->assertSee('Paying suppliers in Guangzhou');
        $this->get('/blog/'.$post->slug)->assertOk()->assertSee('<h2>Why</h2>', false);
    }

    public function test_each_post_has_its_own_reading_statistics(): void
    {
        $this->actingAs($this->admin());
        $post = \App\Models\Post::create(['title' => 'Stats', 'slug' => 'stats', 'body' => str_repeat('word ', 440), 'status' => 'published', 'published_at' => now()]);
        foreach ([[90000, 90], [60000, 80], [30000, 10]] as $i => [$ms, $scroll]) {
            \Illuminate\Support\Facades\DB::table('page_views')->insert(['visitor' => "v$i", 'view_key' => "k$i", 'path' => '/blog/stats', 'duration_ms' => $ms, 'scroll_pct' => $scroll, 'device' => 'mobile', 'created_at' => now()]);
        }

        $this->getJson("/admin/blog/{$post->id}/stats")->assertOk()
            ->assertJsonPath('stats.views', 3)->assertJsonPath('stats.readers', 3)->assertJsonPath('stats.read_through', '67%')
            ->assertJsonPath('stats.verdict', 'People are reading it');
        $this->getJson("/admin/blog/{$post->id}/stats?chart=1")->assertOk()->assertJsonPath('type', 'bar');
        $this->getJson('/admin/blog')->assertOk()->assertJsonPath('data.0.views', 3);
    }

    public function test_rich_text_keeps_safe_formatting_only(): void
    {
        $clean = RichText::clean('<p style="color:red" onclick="x()">Hi <strong>there</strong> <a href="https://sharet.africa">site</a></p><iframe src="x"></iframe><img src="https://evil.test/a.png">');
        $this->assertSame('<p>Hi <strong>there</strong> <a href="https://sharet.africa" rel="noopener" target="_blank">site</a></p>', $clean);
    }

    public function test_customers_get_the_sharet_app_and_staff_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['onboarding_status' => 'active', 'intent' => 'merchant']));
        $this->get('/dashboard')->assertOk()->assertSee('class="sharet-app', false)->assertSee('TOTAL BALANCE')->assertDontSee('Admin overview');
        $this->get('/profile')->assertOk()->assertSee('class="sharet-app', false);

        $this->actingAs($this->admin());
        $this->get('/dashboard')->assertOk()->assertSee('Admin overview')->assertDontSee('class="sharet-app', false);
    }

    public function test_people_lists_need_permission(): void
    {
        $this->actingAs(User::factory()->create(['onboarding_status' => 'active', 'intent' => 'individual']));
        $this->getJson('/users')->assertForbidden();
        $this->get('/reports')->assertForbidden();
        $this->get('/admin/team')->assertForbidden();
    }
}
