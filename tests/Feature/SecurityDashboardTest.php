<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Administration › Security (all domains, defense agent report) and the security kit's own-site page. */
class SecurityDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
        $this->admin = User::factory()->create(['name' => 'Dennis Kibett']);
        $this->admin->assignRole('super_admin');

        $this->dir = sys_get_temp_dir().'/sharet-security-test';
        @mkdir($this->dir);
        $finding = fn ($scope, $sev, $title, $check = 'laravel') => ['scope' => $scope, 'check' => $check, 'severity' => $sev, 'title' => $title, 'fix' => 'Fix it.', 'detail' => null];
        file_put_contents("$this->dir/latest.json", json_encode([
            'generated_at' => now()->toIso8601String(), 'server' => '185.113.249.146', 'took_seconds' => 300,
            'summary' => ['critical' => 1, 'high' => 1, 'medium' => 0, 'low' => 0],
            'sites' => [
                'sharet.africa' => ['docroot' => '/home/sharet.africa/public_html/public', 'mail_host' => false,
                    'tls' => ['verified' => true, 'days_left' => 80, 'issuer' => "Let's Encrypt"], 'http' => ['status' => 200, 'http_redirect' => true]],
                'loans.sharet.africa' => ['docroot' => '/home/loans.sharet.africa/public_html', 'mail_host' => false,
                    'tls' => ['verified' => true, 'days_left' => 40, 'issuer' => "Let's Encrypt"], 'http' => ['status' => 200, 'http_redirect' => false]],
            ],
            'domains' => ['sharet.africa' => ['a' => ['185.113.249.146'], 'spf' => 'v=spf1', 'dkim' => 'ok', 'dmarc' => 'v=DMARC1'],
                'loans.sharet.africa' => ['a' => ['185.113.249.146']]],
            'laravel' => [['path' => '/home/loans.sharet.africa/laravel', 'live' => true, 'app_debug' => 'true']],
            'server_checks' => ['disk_used_pct' => 74, 'ssh_failed_24h' => 500, 'ssh_top_ips' => [['160.155.16.199', 23]], 'public_ports' => [22, 443]],
            'files' => ['files' => 1000],
            'findings' => [$finding('loans.sharet.africa', 'critical', 'APP_DEBUG=true in loans'), $finding('server', 'high', 'SSH accepts passwords', 'ssh')],
        ]));
        file_put_contents("$this->dir/history.json", json_encode([['at' => '2026-11-08T03:00:00+00:00', 'summary' => ['critical' => 2]], ['at' => '2026-11-09T03:00:00+00:00', 'summary' => ['critical' => 1]]]));
        putenv("SECURITY_REPORT_DIR=$this->dir");
    }

    protected function tearDown(): void
    {
        putenv('SECURITY_REPORT_DIR');
        parent::tearDown();
    }

    public function test_only_system_staff_see_every_site_with_tabs(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Juma Jones']))->get('/admin/security')->assertForbidden();

        $this->actingAs($this->admin)->get('/admin/security')->assertOk()
            ->assertSee('What to fix')->assertSee('Sign-in attempts')->assertSee('Server')
            ->assertSee('loans.sharet.africa')->assertSee('APP_DEBUG=true in loans')->assertSee('debug on')
            ->assertSee('https://loans.sharet.africa/admin/security-report', false)
            ->assertSee('160.155.16.199');
    }

    public function test_it_says_when_there_is_no_report_yet(): void
    {
        putenv('SECURITY_REPORT_DIR=/nonexistent');
        $this->actingAs($this->admin)->get('/admin/security')->assertOk()->assertSee('No report yet');
    }

    public function test_sign_in_attempts_are_logged_and_the_kit_page_is_for_super_admins(): void
    {
        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'wrong-password']);
        $this->assertStringContainsString('login.fail', file_get_contents(\App\SecurityKit\KycLog::path()));

        $this->actingAs(User::factory()->create())->get('/admin/security-report')->assertForbidden();
        $this->actingAs($this->admin)->get('/admin/security-report')->assertOk()->assertSee('Sign-in attempts');
    }
}
