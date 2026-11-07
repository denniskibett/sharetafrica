<?php

namespace Tests\Feature\Api;

use App\Api\Webhooks;
use App\Ledger\Ledger;
use App\Models\ApiKey;
use App\Models\ApiWebhook;
use App\Models\Company;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** API v1 step 3: payments out (sandbox and live) and signed webhooks (docs/v2/INTEGRATION.md §4, §8, §10). */
class V1PaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0,
            'security.lockdown_file' => sys_get_temp_dir().'/api-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed(LedgerAccountSeeder::class);
        Http::fake(['https://hooks.example.com/*' => Http::response('ok', 200)]);
    }

    private function business(string $balance = '0', bool $verified = true): User
    {
        $c = Company::create(['name' => 'scharge', 'verified_at' => $verified ? now() : null]);
        $u = User::factory()->create(['name' => 'Juma Jones', 'phone' => '0712345678', 'company_id' => $c->id]);
        $u->forceFill(['kyc_level' => 'standard'])->save();
        if ($balance !== '0') {   // the company's own wallet holds the money; the owner pays it in by M-PESA
            $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($c->walletUser(), $balance, '0712345678'));
            while (! $o->final()) {
                $o = app(RampOrders::class)->refresh($o);
            }
        }

        return $u;
    }

    private function key(User $u, string $env = 'test', array $scopes = ['read', 'payout']): array
    {
        $k = new ApiKey(['user_id' => $u->id, 'company_id' => $u->company_id, 'name' => 'scharge', 'environment' => $env, 'scopes' => $scopes, 'allowed_ips' => ['127.0.0.1'], 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        return ['Authorization' => "Bearer $plain"];
    }

    private function balance(User $u): int
    {
        $u = $u->company_id && ! $u->is_business_wallet ? Company::find($u->company_id)->walletUser() : $u;   // a business's money is in its wallet

        return app(Ledger::class)->account('2100', $u)->available();
    }

    public function test_sandbox_payments_never_touch_the_ledger_and_replay_on_retry(): void
    {
        $u = $this->business();
        $h = $this->key($u) + ['Idempotency-Key' => 'rent-nov-unit-4b'];

        $first = $this->postJson('/api/v1/payments', ['amount' => '1500.00', 'to' => ['type' => 'mpesa', 'phone' => '0722 000 904'], 'reference' => 'Rent'], $h)
            ->assertCreated()->assertJsonPath('status', 'paid')->assertJsonPath('environment', 'test')->assertJsonPath('to.phone', '254722000904');
        $this->assertStringStartsWith('pay_test_', $first->json('id'));

        $this->postJson('/api/v1/payments', ['amount' => '1500.00', 'to' => ['type' => 'mpesa', 'phone' => '0722 000 904'], 'reference' => 'Rent'], $h)
            ->assertCreated()->assertHeader('Idempotent-Replay', 'true')->assertJsonPath('id', $first->json('id'));

        $this->postJson('/api/v1/payments', ['amount' => '100', 'to' => ['type' => 'mpesa', 'phone' => '0700 000 002']], $this->key($u) + ['Idempotency-Key' => 'always-fails-1'])
            ->assertCreated()->assertJsonPath('status', 'failed');

        $this->assertDatabaseCount('ramp_orders', 0);
        $this->assertSame(0, $this->balance($u));
        $this->getJson('/api/v1/payments/'.$first->json('id'), $this->key($u))->assertOk()->assertJsonPath('status', 'paid');
    }

    public function test_payments_need_the_payout_scope_an_idempotency_key_and_a_verified_business_for_live(): void
    {
        $u = $this->business('0', verified: false);
        $body = ['amount' => '100', 'to' => ['type' => 'mpesa', 'phone' => '0722000904']];

        $this->postJson('/api/v1/payments', $body, $this->key($u, 'test', ['read']) + ['Idempotency-Key' => 'scope-check-1'])->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#missing_scope');
        $this->postJson('/api/v1/payments', $body, $this->key($u))->assertStatus(428);
        $this->postJson('/api/v1/payments', $body, $this->key($u, 'live') + ['Idempotency-Key' => 'live-unverified-1'])->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#business_not_verified');
    }

    public function test_live_sharet_to_sharet_is_free_instant_and_tied_to_the_sharet_id(): void
    {
        $u = $this->business('10000');
        $landlord = User::factory()->create(['name' => 'Amina Otieno', 'handle' => 'amina']);

        $this->postJson('/api/v1/payments', ['amount' => '9000', 'to' => ['sharet_id' => $landlord->sharet_id], 'reference' => 'Rent Nov · 4B'], $this->key($u, 'live') + ['Idempotency-Key' => 'rent-4b-landlord'])
            ->assertCreated()->assertJsonPath('status', 'paid')->assertJsonPath('fee', '0.00')->assertJsonPath('to.sharet_id', $landlord->sharet_id);

        $this->assertSame(100_000, $this->balance($u));
        $this->assertSame(900_000, $this->balance($landlord));
    }

    public function test_live_payout_follows_the_order_and_signed_webhooks_fire(): void
    {
        $u = $this->business('10000');
        $hook = $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/sharet', 'events' => ['payment.paid', 'payment.failed']], $this->key($u, 'live'))
            ->assertCreated()->json();
        $this->assertStringStartsWith('whsec_', $hook['secret']);

        $p = $this->postJson('/api/v1/payments', ['amount' => '2500', 'to' => ['type' => 'mpesa', 'phone' => '0722000904']], $this->key($u, 'live') + ['Idempotency-Key' => 'payout-agent-1'])
            ->assertCreated()->json();
        $order = \App\Models\ApiPayment::where('public_id', $p['id'])->firstOrFail()->order;
        for ($i = 0; $i < 5 && ! $order->fresh()->final(); $i++) {
            app(RampOrders::class)->refresh($order->fresh());
        }
        $this->getJson('/api/v1/payments/'.$p['id'], $this->key($u, 'live'))->assertOk()->assertJsonPath('status', 'paid');
        $this->assertGreaterThan(0, (float) $p['fee']);

        Http::assertSent(function (HttpRequest $r) use ($hook, $p) {
            if ($r->url() !== 'https://hooks.example.com/sharet') {
                return false;
            }
            preg_match('/^t=(\d+),v1=([a-f0-9]{64})$/', $r->header('Sharet-Signature')[0] ?? '', $m);

            return $m && hash_equals(Webhooks::sign($hook['secret'], (int) $m[1], $r->body()), $m[2])
                && $r['type'] === 'payment.paid' && $r['data']['id'] === $p['id'];
        });
        $this->assertSame('delivered', ApiWebhook::first()->deliveries()->first()->status);
    }

    public function test_webhook_urls_must_be_public_https_and_the_ledger_freeze_stops_payments(): void
    {
        $u = $this->business('5000');
        foreach (['http://hooks.example.com/x', 'https://localhost/x', 'https://10.0.0.5/x', 'https://192.168.1.2/x'] as $bad) {
            $this->postJson('/api/v1/webhooks', ['url' => $bad, 'events' => ['payment.paid']], $this->key($u))->assertStatus(422)->assertJsonPath('type', 'https://sharet.africa/docs/errors#invalid_webhook_url');
        }

        Lockdown::set('ledger', true, 'test', 'tests');
        $this->postJson('/api/v1/payments', ['amount' => '100', 'to' => ['type' => 'mpesa', 'phone' => '0722000904']], $this->key($u, 'live') + ['Idempotency-Key' => 'frozen-1'])->assertStatus(423);
        Lockdown::set('ledger', false, null, 'tests');
    }
}
