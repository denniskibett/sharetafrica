<?php

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Live payout keys only work from the business's listed server addresses (docs/v2/INTEGRATION.md §2). */
class IpAllowListTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0, 'security.lockdown_file' => sys_get_temp_dir().'/ip-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
        $c = Company::create(['name' => 'scharge', 'verified_at' => now()]);
        $this->owner = User::factory()->create(['name' => 'Peter Kamau', 'phone' => '0711000111', 'company_id' => $c->id]);
        $this->owner->assignRole('techie');
        $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($c->walletUser(), '5000', '0711000111'));
        while (! $o->final()) {
            $o = app(RampOrders::class)->refresh($o);
        }
        User::factory()->create(['name' => 'Amina Otieno', 'handle' => 'amina']);
    }

    private function key(string $env, ?array $ips): array
    {
        $k = new ApiKey(['user_id' => $this->owner->id, 'company_id' => $this->owner->company_id, 'name' => 'k', 'environment' => $env,
            'scopes' => ['read', 'payout'], 'allowed_ips' => $ips, 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        return ['Authorization' => "Bearer $plain"];
    }

    private function pay(array $h, string $idem)
    {
        return $this->postJson('/api/v1/payments', ['amount' => '100', 'to' => ['handle' => '@amina']], $h + ['Idempotency-Key' => $idem]);
    }

    public function test_live_payouts_need_a_list_and_a_listed_address(): void
    {
        $this->pay($this->key('live', null), 'no-list-1')->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#ip_allow_list_required');
        $this->pay($this->key('live', ['41.90.12.7']), 'wrong-ip-1')->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#ip_not_allowed')->assertJsonPath('ip', '127.0.0.1');
        $this->pay($this->key('live', ['127.0.0.0/8']), 'range-ok-1')->assertCreated()->assertJsonPath('status', 'paid');

        $this->pay($this->key('test', null), 'sandbox-1')->assertCreated();   // the sandbox needs no list
        $this->getJson('/api/v1/accounts/me', $this->key('live', null))->assertOk();   // reading needs no list either
    }

    public function test_owners_set_scopes_and_addresses_in_the_dashboard(): void
    {
        $this->actingAs($this->owner);
        $id = $this->postJson('/api-keys', ['name' => 'Server', 'environment' => 'live', 'scopes' => ['payout'], 'allowed_ips' => '41.90.12.7, 196.201.0.0/16'])
            ->assertCreated()->assertJsonPath('key.allowed_ips', ['41.90.12.7', '196.201.0.0/16'])->assertJsonPath('key.payout_blocked', false)->json('key.id');

        $this->postJson('/api-keys', ['name' => 'Bad', 'environment' => 'live', 'allowed_ips' => '41.90.12.999'])->assertStatus(422)->assertJsonValidationErrors('allowed_ips');
        $this->postJson('/api-keys', ['name' => 'Too wide', 'environment' => 'live', 'allowed_ips' => '0.0.0.0/0'])->assertStatus(422);

        $this->patchJson("/api-keys/$id/settings", ['scopes' => ['payout', 'collect'], 'allowed_ips' => ''])->assertOk()
            ->assertJsonPath('key.scopes', ['read', 'payout', 'collect'])->assertJsonPath('key.payout_blocked', true);
        $this->get('/api-keys')->assertOk()->assertSee(\App\Models\Company::first()->sharet_id);
    }
}
