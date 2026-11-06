<?php

namespace Tests\Feature\Security;

use App\Ledger\Ledger;
use App\Ledger\LedgerException;
use App\Models\User;
use App\Security\Lockdown;
use App\Support\Kyc;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LockdownAndIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['security.lockdown_file' => sys_get_temp_dir().'/sharet-lockdown-'.uniqid().'.json', 'security.system_lockdown' => false, 'security.ledger_freeze' => false]);
        Lockdown::forget();
    }

    protected function tearDown(): void
    {
        @unlink(Lockdown::path());
        Lockdown::forget();
        parent::tearDown();
    }

    private function deposit(string $key): void
    {
        $ledger = app(Ledger::class);
        $amina = User::factory()->create();
        $ledger->post($key, 'deposit', [Ledger::debit($ledger->account('1110'), 1000), Ledger::credit($ledger->account('2100', $amina), 1000)]);
    }

    public function test_frozen_ledger_refuses_every_write_and_reopens(): void
    {
        $this->seed(LedgerAccountSeeder::class);
        Lockdown::set('ledger', true, 'test', 'tests');

        try {
            $this->deposit('frozen:1');
            $this->fail('The ledger posted while frozen.');
        } catch (LedgerException $e) {
            $this->assertSame('ledger_frozen', $e->reason);
        }
        $this->assertDatabaseCount('ledger_journals', 0);

        Lockdown::set('ledger', false, null, 'tests');
        $this->deposit('open:1');
        $this->assertDatabaseCount('ledger_journals', 1);
    }

    public function test_system_lockdown_freezes_the_ledger_and_closes_pages(): void
    {
        Lockdown::set('system', true, 'test', 'tests');
        $this->assertTrue(Lockdown::ledgerFrozen());

        $this->get('/login')->assertStatus(503);
        $this->actingAs(User::factory()->create())->getJson('/dashboard/data')->assertStatus(503);
    }

    public function test_unreadable_switch_file_fails_closed(): void
    {
        file_put_contents(Lockdown::path(), 'not json');
        Lockdown::forget();
        $this->assertTrue(Lockdown::ledgerFrozen());
        $this->assertFalse(Lockdown::systemLocked());
    }

    public function test_disabled_accounts_are_signed_out(): void
    {
        $user = User::factory()->create(['status' => false]);
        $this->actingAs($user)->getJson('/dashboard/data')->assertStatus(403);
        $this->assertGuest();
    }

    public function test_hostile_urls_are_refused(): void
    {
        $this->get('/blog?q=1%20UNION%20SELECT%20password%20FROM%20users')->assertStatus(400);
        $this->get('/blog?q=<script>alert(1)</script>')->assertStatus(400);
    }

    public function test_only_super_admins_switch_with_their_password(): void
    {
        $admin = User::factory()->create(['password' => bcrypt('Correct-horse-9')]);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        \Spatie\Permission\Models\Permission::findOrCreate('admin.system', 'web');
        $admin->givePermissionTo('admin.system');
        $this->actingAs($admin)->postJson(route('system.lockdown'), ['switch' => 'ledger', 'on' => true, 'reason' => 'x', 'password' => 'Correct-horse-9'])->assertForbidden();

        $admin->assignRole(Role::findOrCreate('super_admin', 'web'));
        $this->actingAs($admin)->postJson(route('system.lockdown'), ['switch' => 'ledger', 'on' => true, 'reason' => 'x', 'password' => 'wrong'])->assertStatus(422);
        $this->actingAs($admin)->postJson(route('system.lockdown'), ['switch' => 'ledger', 'on' => true, 'reason' => 'Recon mismatch', 'password' => 'Correct-horse-9'])
            ->assertOk()->assertJsonPath('lockdown.ledger', true);
        $this->assertTrue(Lockdown::ledgerFrozen());
    }

    public function test_console_switch(): void
    {
        $this->artisan('sharet:lockdown', ['switch' => 'ledger', 'state' => 'on'])->assertExitCode(2);
        $this->artisan('sharet:lockdown', ['switch' => 'ledger', 'state' => 'on', '--reason' => 'drill'])->assertSuccessful();
        $this->assertTrue(Lockdown::ledgerFrozen());
        $this->artisan('sharet:lockdown', ['switch' => 'ledger', 'state' => 'off'])->assertSuccessful();
        $this->assertFalse(Lockdown::ledgerFrozen());
    }

    public function test_bad_names_emails_and_phones_never_make_a_user(): void
    {
        foreach ([['name' => 'x'], ['name' => 'Amina 123'], ['email' => 'amina@@x.com'], ['email' => 'a..b@x.com'], ['phone' => '+254600000000'], ['phone' => '12345']] as $bad) {
            try {
                User::factory()->create($bad);
                $this->fail('Created a user with '.json_encode($bad));
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $this->assertSame('+254712345678', User::factory()->create(['phone' => '0712 345 678'])->phone);
    }

    public function test_sign_up_records_where_it_came_from(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'KE', 'User-Agent' => 'TestBrowser/1.0', 'Accept-Language' => 'en-KE'])->post('/register', [
            'name' => 'Amina Wanjiru', 'email' => 'amina@example.com', 'phone' => '0712345678',
            'password' => 'Password-123', 'password_confirmation' => 'Password-123',
        ])->assertRedirect();

        $audit = Kyc::data(User::where('email', 'amina@example.com')->first())['audit'];
        $this->assertSame('register', $audit[0]['step']);
        $this->assertSame('KE', $audit[0]['country']);
        $this->assertSame('TestBrowser/1.0', $audit[0]['user_agent']);
        $this->assertArrayNotHasKey('password', $audit[0]);
    }
}
