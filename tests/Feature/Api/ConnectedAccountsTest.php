<?php

namespace Tests\Feature\Api;

use App\Ledger\Ledger;
use App\Models\ApiConnection;
use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Connected accounts: a platform acts for a person's own Sharet account with their consent (INTEGRATION.md §6). */
class ConnectedAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0, 'security.lockdown_file' => sys_get_temp_dir().'/con-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed(LedgerAccountSeeder::class);
        Http::fake(['https://hooks.example.com/*' => Http::response('ok', 200)]);
    }

    private function platform(string $name = 'scharge', string $phone = '0711000111'): array
    {
        $c = Company::create(['name' => $name, 'verified_at' => now()]);
        $owner = User::factory()->create(['name' => 'Peter Kamau', 'phone' => $phone, 'company_id' => $c->id]);
        $k = new ApiKey(['user_id' => $owner->id, 'company_id' => $c->id, 'name' => $name, 'environment' => 'live', 'scopes' => ['read', 'collect', 'payout', 'connect'], 'allowed_ips' => ['127.0.0.1'], 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        return [$c, ['Authorization' => "Bearer $plain"]];
    }

    private function landlord(string $balance = '0'): User
    {
        $u = User::factory()->create(['name' => 'Amina Otieno', 'handle' => 'amina', 'phone' => '0713000222']);
        $u->forceFill(['kyc_level' => 'standard'])->save();
        TransactionPin::set($u, '4826');
        if ($balance !== '0') {
            $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($u, $balance));
            while (! $o->final()) {
                $o = app(RampOrders::class)->refresh($o);
            }
        }

        return $u;
    }

    private function connect(array $h, User $who, array $scopes = ['collect', 'payout']): ApiConnection
    {
        $link = $this->postJson('/api/v1/connect/links', ['scopes' => $scopes, 'reference' => 'landlord-17'], $h)->assertCreated()->json();
        $token = basename(parse_url($link['url'], PHP_URL_PATH));
        $this->actingAs($who)->post("/connect/$token/approve", ['pin' => '4826']);

        return ApiConnection::where('public_id', $link['id'])->first();
    }

    private function balance(User $u): int
    {
        return app(Ledger::class)->account('2100', $u)->available();
    }

    public function test_a_person_approves_with_their_pin_and_the_platform_is_told(): void
    {
        [, $h] = $this->platform();
        $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/c', 'events' => ['account.connected', 'account.disconnected']], $h)->assertCreated();
        $amina = $this->landlord();

        $link = $this->postJson('/api/v1/connect/links', ['scopes' => ['collect', 'payout'], 'return_url' => 'https://scharge.example.com/done'], $h)
            ->assertCreated()->assertJsonPath('status', 'pending')->json();
        $token = basename(parse_url($link['url'], PHP_URL_PATH));

        $this->get("/connect/$token")->assertOk()->assertSee('Connect scharge to your Sharet account')->assertSee('Sign in to continue');
        $this->actingAs($amina)->get("/connect/$token")->assertSee('Collect payments for you')->assertSee('Send money from your wallet');
        $this->actingAs($amina)->from("/connect/$token")->post("/connect/$token/approve", ['pin' => '0000'])->assertSessionHasErrors('pin');
        $this->actingAs($amina)->post("/connect/$token/approve", ['pin' => '4826'])
            ->assertRedirect('https://scharge.example.com/done?connection='.$link['id'].'&status=active');

        $this->getJson('/api/v1/connect/accounts', $h)->assertOk()->assertJsonPath('data.0.account.sharet_id', $amina->sharet_id)->assertJsonPath('data.0.status', 'active');
        Http::assertSent(fn (HttpRequest $r) => $r['type'] === 'account.connected' && $r['data']['account']['sharet_id'] === $amina->sharet_id);

        $this->actingAs($amina)->from("/connect/$token")->post("/connect/$token/approve", ['pin' => '4826'])->assertSessionHasErrors('connect');   // a link works once
    }

    public function test_the_platform_acts_for_the_connected_account_within_its_scopes(): void
    {
        [, $h] = $this->platform();
        $amina = $this->landlord('5000');
        $kevin = User::factory()->create(['name' => 'Kevin Mutua', 'handle' => 'kevin']);
        $this->connect($h, $amina);
        $as = $h + ['Sharet-Account' => $amina->sharet_id];

        $this->getJson('/api/v1/accounts/me', $as)->assertOk()->assertJsonPath('sharet_id', $amina->sharet_id);

        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '1000', 'reference' => 'Rent 4B'], $as + ['Idempotency-Key' => 'for-amina-1'])->assertCreated();
        $r->assertJsonPath('payee.sharet_id', $amina->sharet_id);

        $this->postJson('/api/v1/payments', ['amount' => '2000', 'to' => ['handle' => '@kevin']], $as + ['Idempotency-Key' => 'from-amina-1'])->assertCreated()->assertJsonPath('status', 'paid');
        $this->assertSame(300_000, $this->balance($amina), 'paid from her own wallet');
        $this->assertSame(200_000, $this->balance($kevin));
    }

    public function test_scopes_connection_and_platform_are_all_checked(): void
    {
        [, $h] = $this->platform();
        [, $other] = $this->platform('Breve', '0711000999');
        $amina = $this->landlord('5000');
        $stranger = User::factory()->create(['name' => 'Otieno Ouma']);
        $this->connect($h, $amina, ['collect']);

        $this->postJson('/api/v1/payments', ['amount' => '100', 'to' => ['type' => 'mpesa', 'phone' => '0722000904']], $h + ['Sharet-Account' => $amina->sharet_id, 'Idempotency-Key' => 'nope-1'])
            ->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#not_allowed_by_account');
        $this->getJson('/api/v1/accounts/me', $h + ['Sharet-Account' => $stranger->sharet_id])->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#not_connected');
        $this->getJson('/api/v1/accounts/me', $other + ['Sharet-Account' => $amina->sharet_id])->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#not_connected');
        $this->assertSame(500_000, $this->balance($amina));
    }

    public function test_either_side_can_disconnect_at_any_time(): void
    {
        [, $h] = $this->platform();
        $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/c', 'events' => ['account.disconnected']], $h)->assertCreated();
        $amina = $this->landlord();
        $c = $this->connect($h, $amina);

        $this->actingAs($amina)->get('/connected-apps')->assertOk()->assertSee('scharge')->assertSee('Disconnect');
        $this->actingAs($amina)->post("/connected-apps/{$c->id}/disconnect")->assertSessionHas('status');
        $this->getJson('/api/v1/accounts/me', $h + ['Sharet-Account' => $amina->sharet_id])->assertForbidden();
        Http::assertSent(fn (HttpRequest $r) => $r['type'] === 'account.disconnected');

        $c2 = $this->connect($h, $amina);
        $this->assertSame('active', $c2->status);
        $this->postJson("/api/v1/connect/accounts/{$c2->public_id}/disconnect", [], $h)->assertOk()->assertJsonPath('status', 'revoked');
        $this->getJson('/api/v1/accounts/me', $h + ['Sharet-Account' => $amina->sharet_id])->assertForbidden();

        $someoneElse = User::factory()->create(['name' => 'Kevin Mutua']);
        $this->actingAs($someoneElse)->post("/connected-apps/{$c2->id}/disconnect")->assertNotFound();
    }

    public function test_links_expire(): void
    {
        [, $h] = $this->platform();
        $amina = $this->landlord();
        $link = $this->postJson('/api/v1/connect/links', ['scopes' => ['collect']], $h)->json();
        $this->travel(8)->days();
        $token = basename(parse_url($link['url'], PHP_URL_PATH));
        $this->actingAs($amina)->get("/connect/$token")->assertSee('expired');
        $this->actingAs($amina)->from("/connect/$token")->post("/connect/$token/approve", ['pin' => '4826'])->assertSessionHasErrors('connect');
    }
}
