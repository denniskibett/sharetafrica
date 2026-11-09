<?php

namespace Tests\Feature\Api;

use App\Api\PaymentRequests;
use App\Ledger\Ledger;
use App\Models\ApiKey;
use App\Models\ApiPaymentRequest;
use App\Models\RampOrder;
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

/** API v1: payment requests, money in (docs/v2/INTEGRATION.md §3). */
class V1PaymentRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0,
            'security.lockdown_file' => sys_get_temp_dir().'/apireq-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed(LedgerAccountSeeder::class);
        Http::fake(['https://hooks.example.com/*' => Http::response('ok', 200)]);
    }

    private function scharge(bool $verified = true, string $phone = '0711000111'): User
    {
        $c = Company::create(['name' => 'scharge', 'verified_at' => $verified ? now() : null]);
        $u = User::factory()->create(['name' => 'Peter Kamau', 'phone' => $phone, 'company_id' => $c->id]);
        $u->forceFill(['kyc_level' => 'standard'])->save();

        return $u;
    }

    private function tenant(string $handle, string $balance = '0'): User
    {
        $u = User::factory()->create(['name' => 'Akinyi Wekesa', 'handle' => $handle, 'phone' => '0712345678']);
        $u->forceFill(['kyc_level' => 'basic'])->save();
        TransactionPin::set($u, '4826');
        if ($balance !== '0') {
            $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($u, $balance));
            while (! $o->final()) {
                $o = app(RampOrders::class)->refresh($o);
            }
        }

        return $u;
    }

    private function key(User $u, string $env = 'test', array $scopes = ['read', 'collect']): array
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

    private function token(string $payUrl): string
    {
        return basename(parse_url($payUrl, PHP_URL_PATH));
    }

    public function test_sandbox_request_is_paid_on_the_hosted_page_without_touching_the_ledger(): void
    {
        $biz = $this->scharge();
        $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/in', 'events' => ['payment_request.paid']], $this->key($biz))->assertCreated();

        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '35000', 'reference' => 'Rent Nov · 4B', 'payer' => ['phone' => '0712 345 678']],
            $this->key($biz) + ['Idempotency-Key' => 'rent-4b-nov'])->assertCreated()->assertJsonPath('status', 'open')->assertJsonPath('payee.sharet_id', Company::find($biz->company_id)->sharet_id)->json();
        $this->assertStringStartsWith('pr_test_', $r['id']);

        $token = $this->token($r['pay_url']);
        $this->get("/r/$token")->assertOk()->assertSee('Pay scharge')->assertSee('KES 35,000.00')->assertSee('Sandbox: no real money moves');
        $this->post("/r/$token/sandbox", ['outcome' => 'pay'])->assertRedirect("/r/$token");

        $this->getJson('/api/v1/payment-requests/'.$r['id'], $this->key($biz))->assertOk()->assertJsonPath('status', 'paid')->assertJsonPath('paid_via', 'sandbox');
        $this->assertDatabaseCount('ramp_orders', 0);
        Http::assertSent(fn (HttpRequest $q) => $q['type'] === 'payment_request.paid' && $q['data']['id'] === $r['id']);
    }

    public function test_live_request_paid_from_a_sharet_wallet_needs_sign_in_and_the_pin(): void
    {
        $biz = $this->scharge();
        $akinyi = $this->tenant('akinyi', '50000');
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '35000', 'payer' => ['handle' => '@akinyi'], 'channels' => ['sharet']],
            $this->key($biz, 'live') + ['Idempotency-Key' => 'rent-4b-live'])->assertCreated()->json();
        $token = $this->token($r['pay_url']);

        $this->get("/r/$token")->assertOk()->assertSee('Sign in to pay with Sharet');
        $this->post("/r/$token/sharet", ['pin' => '4826'])->assertRedirect('/login');

        $this->actingAs($akinyi)->from("/r/$token")->post("/r/$token/sharet", ['pin' => '0000'])->assertSessionHasErrors('pin');
        $this->assertSame('open', ApiPaymentRequest::first()->status);

        $this->actingAs($akinyi)->post("/r/$token/sharet", ['pin' => '4826'])->assertRedirect("/r/$token");
        $this->assertSame(1_500_000, $this->balance($akinyi));
        $this->assertSame(3_500_000, $this->balance($biz));
        $this->getJson('/api/v1/payment-requests/'.$r['id'], $this->key($biz, 'live'))->assertJsonPath('status', 'paid')->assertJsonPath('paid_via', 'sharet');

        $this->actingAs($akinyi)->from("/r/$token")->post("/r/$token/sharet", ['pin' => '4826'])->assertSessionHasErrors('pay');
        $this->assertSame(1_500_000, $this->balance($akinyi), 'never paid twice');
    }

    public function test_a_payer_without_a_pin_creates_one_on_the_pay_page_then_approves_with_it(): void
    {
        $biz = $this->scharge();
        $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/in', 'events' => ['payment_request.paid']], $this->key($biz, 'live'))->assertCreated();
        $akinyi = $this->tenant('akinyi', '50000');
        $akinyi->forceFill(['transaction_pin' => null])->save();   // has money, never set a PIN
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10', 'channels' => ['sharet', 'mpesa']],
            $this->key($biz, 'live') + ['Idempotency-Key' => 'inv-1578'])->assertCreated()->json();
        $token = $this->token($r['pay_url']);

        $this->actingAs($akinyi)->get("/r/$token")->assertOk()->assertSee('Create your Sharet PIN first')->assertDontSee('Your Sharet PIN<');
        $this->post("/r/$token/pin", ['pin' => '5937', 'pin_confirmation' => '5938'])->assertSessionHasErrors('pin_confirmation');
        $this->post("/r/$token/pin", ['pin' => '1234', 'pin_confirmation' => '1234'])->assertSessionHasErrors('pin');
        $this->assertFalse(TransactionPin::isSet($akinyi->fresh()));

        $this->post("/r/$token/pin", ['pin' => '5937', 'pin_confirmation' => '5937'])->assertRedirect("/r/$token");
        $this->assertTrue(TransactionPin::isSet($akinyi->fresh()));
        $this->assertSame('open', ApiPaymentRequest::first()->status, 'creating the PIN pays nothing');
        $this->assertSame(5_000_000, $this->balance($akinyi));
        $this->actingAs($akinyi->fresh())->withSession(['pin_created' => true])->get("/r/$token")->assertSee('Your Sharet PIN is set')->assertSee('Pay KES 10.00');
        $this->actingAs($akinyi->fresh())->post("/r/$token/pin", ['pin' => '8642', 'pin_confirmation' => '8642'])->assertStatus(422);

        $this->actingAs($akinyi->fresh())->post("/r/$token/sharet", ['pin' => '5937'])->assertRedirect("/r/$token");
        $this->assertSame('paid', ApiPaymentRequest::first()->status);
        $this->assertSame(4_999_000, $this->balance($akinyi), 'no fee on a wallet payment');
        Http::assertSent(fn (HttpRequest $q) => $q['type'] === 'payment_request.paid' && $q['data']['id'] === $r['id'] && $q['data']['paid_via'] === 'sharet');
    }

    public function test_on_production_a_live_request_is_never_paid_by_the_simulator_and_a_named_owner_gets_the_real_rail(): void
    {
        $biz = $this->scharge();
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10', 'channels' => ['mpesa']],
            $this->key($biz, 'live') + ['Idempotency-Key' => 'inv-1577'])->assertCreated()->json();
        $token = $this->token($r['pay_url']);
        $wallet = Company::find($biz->company_id)->walletUser();

        // the live server: real collections only for named people, simulator allowed for demos
        config(['rails.default' => 'simulator', 'rails.collect' => 'daraja', 'rails.collect_only_for' => ['someone@else.test'], 'rails.simulator.allow_in_production' => true]);
        $this->app['env'] = 'production';
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class, \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->from("/r/$token")->post("/r/$token/mpesa", ['phone' => '0717 492 048'])->assertSessionHasErrors('phone');
        $this->assertSame('open', ApiPaymentRequest::first()->status);
        $this->assertDatabaseCount('ramp_orders', 0);
        $this->assertSame('simulator', app(\App\Integrations\Rails::class)->collecting($wallet));

        // the business's owner is named: its wallet collects on the real rail
        config(['rails.collect_only_for' => [strtolower($biz->email)]]);
        $this->assertSame('daraja', app(\App\Integrations\Rails::class)->collecting($wallet->fresh()));
        $this->app['env'] = 'testing';
    }

    public function test_an_unverified_payer_gets_a_verify_now_link_that_comes_back_to_the_pay_page(): void
    {
        $biz = $this->scharge();
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10'], $this->key($biz, 'live') + ['Idempotency-Key' => 'inv-1578b'])->assertCreated()->json();
        $token = $this->token($r['pay_url']);
        $admin = User::factory()->create(['onboarding_status' => 'active']);
        $admin->forceFill(['kyc_level' => 'none'])->save();

        $this->actingAs($admin)->get("/r/$token")->assertOk()->assertSee('Verify now')
            ->assertSee(e(route('profile.edit', ['return' => "/r/$token"])), false)->assertDontSee('Create PIN');
        $this->actingAs($admin)->get('/profile/edit?return='.urlencode("/r/$token"))->assertOk()->assertSee('Back to my payment')->assertSee(url("/r/$token"));
        $this->actingAs($admin)->get('/profile/edit?return='.urlencode('https://evil.example/r/x'))->assertOk();
        $this->flushSession();
        $this->actingAs($admin)->get('/profile/edit?return='.urlencode('https://evil.example'))->assertOk()->assertDontSee('Back to my payment');
    }

    public function test_guests_choose_between_their_sharet_account_and_mpesa_without_one(): void
    {
        $biz = $this->scharge();
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10'], $this->key($biz, 'live') + ['Idempotency-Key' => 'choice-guest-page'])->assertCreated()->json();
        $this->get('/r/'.$this->token($r['pay_url']))->assertOk()->assertSee('How would you like to pay?')->assertSee('I have a Sharet account')
            ->assertSee('no account needed')->assertDontSee('Top up by M-PESA');
    }

    public function test_signed_in_top_up_by_mpesa_lands_in_my_wallet_then_moves_on_to_the_payee(): void
    {
        $biz = $this->scharge();
        $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/in', 'events' => ['payment_request.paid']], $this->key($biz, 'live'))->assertCreated();
        $akinyi = $this->tenant('akinyi');   // empty wallet
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10', 'reference' => 'Invoice #1577'], $this->key($biz, 'live') + ['Idempotency-Key' => 'inv-1577-topup'])->assertCreated()->json();
        $token = $this->token($r['pay_url']);

        $this->actingAs($akinyi)->get("/r/$token")->assertOk()->assertSee('Or top up by M-PESA and pay')->assertSee('From my wallet');
        $this->actingAs($akinyi)->post("/r/$token/top-up", ['phone' => '0712 345 678'])->assertRedirect("/r/$token");

        $this->actingAs($akinyi)->getJson("/r/$token/status")->assertOk();   // the page polls; the prompt is answered
        $req = ApiPaymentRequest::first();
        $this->assertSame("paid", $req->status);
        $this->assertSame('mpesa_via_wallet', $req->paid_via);
        $this->assertSame(0, $this->balance($akinyi), 'in and straight out');
        $this->assertSame(1_000, $this->balance($biz));
        $mine = RampOrder::where('user_id', $akinyi->id)->orderBy('id')->get();
        $this->assertSame(['wallet', 'paid'], [$mine[0]->destination_type, $mine[0]->status], 'the top-up is in my history');
        $this->assertSame($req->ramp_order_id, $mine[1]->id, 'and so is the payment');
        Http::assertSent(fn (HttpRequest $q) => $q['type'] === 'payment_request.paid' && $q['data']['paid_via'] === 'mpesa_via_wallet');

        $this->actingAs($akinyi)->from("/r/$token")->post("/r/$token/top-up", ['phone' => '0712 345 678'])->assertSessionHasErrors('pay');
        $this->assertSame(0, $this->balance($akinyi), 'never collected twice');
    }

    public function test_if_paying_on_fails_after_the_top_up_the_money_stays_in_my_wallet_and_the_request_stays_open(): void
    {
        $biz = $this->scharge();
        $akinyi = $this->tenant('akinyi');
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10'], $this->key($biz, 'live') + ['Idempotency-Key' => 'inv-onward-fail'])->assertCreated()->json();
        $token = $this->token($r['pay_url']);

        // the prompt is slow to answer (simulator: numbers ending 9999); meanwhile the onward payment becomes impossible
        $this->actingAs($akinyi)->post("/r/$token/top-up", ['phone' => '0712 349 999'])->assertRedirect("/r/$token");
        $this->assertSame('open', ApiPaymentRequest::first()->status);
        $this->actingAs($akinyi)->get("/r/$token")->assertSee('Check your phone');
        $akinyi->forceFill(['kyc_level' => 'none'])->save();   // e.g. limits changed: the wallet-to-wallet leg is refused

        $o = RampOrder::where('user_id', $akinyi->id)->first();
        for ($i = 0; $i < 5 && ! $o->final(); $i++) {
            $o = app(RampOrders::class)->refresh($o);
        }
        $this->assertSame('paid', $o->status);
        $this->assertSame('failed', $o->fresh()->destination['onward']);
        $this->assertSame('open', ApiPaymentRequest::first()->status);
        $this->assertSame(1_000, $this->balance($akinyi), 'the top-up stays in my wallet');
        $this->assertSame(0, $this->balance($biz));

        $akinyi->forceFill(['kyc_level' => 'basic'])->save();
        $this->actingAs($akinyi->fresh())->get("/r/$token")->assertSee('is in your Sharet wallet, but paying')->assertSee('Pay from your wallet below');
        $this->actingAs($akinyi->fresh())->post("/r/$token/sharet", ['pin' => '4826'])->assertRedirect("/r/$token");
        $this->assertSame('paid', ApiPaymentRequest::first()->status);
        $this->assertSame(0, $this->balance($akinyi));
    }

    public function test_only_the_named_payer_can_pay_from_their_wallet(): void
    {
        $biz = $this->scharge();
        $this->tenant('akinyi', '50000');
        $other = User::factory()->create(['name' => 'Otieno Ouma', 'handle' => 'otieno']);
        $other->forceFill(['kyc_level' => 'basic'])->save();
        TransactionPin::set($other, '1357');

        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '1000', 'payer' => ['handle' => '@akinyi']], $this->key($biz, 'live') + ['Idempotency-Key' => 'named-payer-1'])->json();
        $token = $this->token($r['pay_url']);
        $this->actingAs($other)->from("/r/$token")->post("/r/$token/sharet", ['pin' => '1357'])->assertSessionHasErrors('pay');
        $this->assertSame('open', ApiPaymentRequest::first()->status);
    }

    public function test_live_request_paid_by_mpesa_prompt_lands_in_the_business_wallet(): void
    {
        $biz = $this->scharge();
        $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/in', 'events' => ['payment_request.paid']], $this->key($biz, 'live'))->assertCreated();
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '2000'], $this->key($biz, 'live') + ['Idempotency-Key' => 'mpesa-req-1'])->json();
        $token = $this->token($r['pay_url']);

        $this->post("/r/$token/mpesa", ['phone' => '0722 000 904'])->assertRedirect("/r/$token");
        $req = ApiPaymentRequest::first();
        $this->assertSame('254722000904', $req->order->payer_phone);
        for ($i = 0; $i < 5 && ! $req->fresh()->order->final(); $i++) {
            app(RampOrders::class)->refresh($req->fresh()->order);
        }
        $this->getJson("/r/$token/status")->assertOk()->assertJsonPath('status', 'paid');
        $this->assertSame(200_000, $this->balance($biz));
        $this->assertSame('mpesa', $req->fresh()->paid_via);
        Http::assertSent(fn (HttpRequest $q) => $q['type'] === 'payment_request.paid');
    }

    public function test_a_signed_in_payers_direct_mpesa_payment_shows_in_their_history_without_touching_their_wallet(): void
    {
        $biz = $this->scharge();
        $akinyi = $this->tenant('akinyi', '500');
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10', 'reference' => 'Invoice #1577'], $this->key($biz, 'live') + ['Idempotency-Key' => 'inv-1577-direct'])->assertCreated()->json();
        $token = $this->token($r['pay_url']);
        $wallet = app(Ledger::class)->account('2100', $akinyi);
        $postings = \Illuminate\Support\Facades\DB::table('ledger_postings')->where('account_id', $wallet->id)->count();

        $this->actingAs($akinyi)->get("/r/$token")->assertOk()->assertSee('straight to scharge');
        $this->actingAs($akinyi)->post("/r/$token/mpesa", ['phone' => '0717 492 048'])->assertRedirect("/r/$token");
        $order = ApiPaymentRequest::first()->order;
        for ($i = 0; $i < 5 && ! $order->final(); $i++) {
            $order = app(RampOrders::class)->refresh($order);
        }
        $this->assertSame('paid', ApiPaymentRequest::first()->status);
        $this->assertSame('mpesa', ApiPaymentRequest::first()->paid_via);
        $this->assertSame(1_000, $this->balance($biz), 'the money went straight to the business');

        // the payer's wallet and ledger are exactly as before
        $this->assertSame(50_000, $this->balance($akinyi));
        $this->assertSame($postings, \Illuminate\Support\Facades\DB::table('ledger_postings')->where('account_id', $wallet->id)->count());

        // ...but it is in their history, as their payment, with the M-PESA receipt
        $row = collect($this->actingAs($akinyi)->getJson('/pay')->assertOk()->json('data'))->firstWhere('uuid', $order->uuid);
        $this->assertNotNull($row);
        $this->assertSame('Paid scharge', $row['title']);
        $this->assertSame('M-PESA 254717492048', $row['from']);
        $this->assertFalse($row['in']);
        $this->assertTrue($row['paid_direct']);
        $receipt = $this->actingAs($akinyi)->getJson("/pay/{$order->uuid}")->assertOk()->json('order.receipt');
        $this->assertSame($order->sharetReference(), $receipt['Transaction ID']);
        $this->assertSame($order->reference(), $receipt['M-PESA receipt']);
        $this->assertSame('scharge', $receipt['Recipient']);
        $this->actingAs($akinyi)->get("/pay/{$order->uuid}/receipt")->assertOk();

        // nobody else sees it, and a guest's payment is linked to nobody
        $other = User::factory()->create();
        $this->actingAs($other)->getJson("/pay/{$order->uuid}")->assertNotFound();
        $this->assertNull(collect($this->actingAs($other)->getJson('/pay')->json('data'))->firstWhere('uuid', $order->uuid));
    }

    public function test_a_guests_direct_mpesa_payment_is_linked_to_nobody(): void
    {
        $biz = $this->scharge();
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10'], $this->key($biz, 'live') + ['Idempotency-Key' => 'inv-guest-direct'])->assertCreated()->json();
        $this->post('/r/'.$this->token($r['pay_url']).'/mpesa', ['phone' => '0717 492 048'])->assertRedirect();
        $this->assertArrayNotHasKey('paid_by', ApiPaymentRequest::first()->order->destination);
    }

    /** A guest pays by M-PESA (simulator in tests); the order's collection then carries a Safaricom callback like live. */
    private function guestPays(User $biz, string $phone = '0717 492 048'): RampOrder
    {
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '10', 'reference' => 'Invoice #1577'], $this->key($biz, 'live') + ['Idempotency-Key' => 'guest-'.uniqid()])->assertCreated()->json();
        $this->post('/r/'.$this->token($r['pay_url']).'/mpesa', ['phone' => $phone])->assertRedirect();
        $order = ApiPaymentRequest::where('public_id', $r['id'])->first()->order;
        for ($i = 0; $i < 5 && ! $order->final(); $i++) {
            $order = app(RampOrders::class)->refresh($order);
        }
        $req = \App\Models\RailRequest::find($order->collect_request_id);
        $req->forceFill(['rail' => 'daraja', 'provider_reference' => 'ws_CO_TEST', 'receipt' => 'UK96O15681', 'meta' => ($req->meta ?? []) + ['callbacks' => [['at' => now()->toIso8601String(), 'body' => [
            'MerchantRequestID' => '1234-5678', 'CheckoutRequestID' => 'ws_CO_TEST', 'ResultCode' => 0, 'ResultDesc' => 'The service request is processed successfully.',
            'CallbackMetadata' => ['Item' => [['Name' => 'Amount', 'Value' => 10], ['Name' => 'MpesaReceiptNumber', 'Value' => 'UK96O15681'],
                ['Name' => 'TransactionDate', 'Value' => 20261109145126], ['Name' => 'PhoneNumber', 'Value' => 254717492048]]]]]]]])->save();

        return $order->fresh();
    }

    public function test_a_guest_mpesa_payment_is_recorded_with_its_mpesa_payload_and_a_sharet_transaction_id(): void
    {
        $biz = $this->scharge();
        $order = $this->guestPays($biz);
        $p = $order->mpesaPayload();
        $this->assertSame('UK96O15681', $p['receipt']);
        $this->assertSame('254717492048', $p['phone']);
        $this->assertSame(0, $p['result_code']);
        $this->assertSame('ws_CO_TEST', $p['checkout_request_id']);
        $this->assertSame('1234-5678', $p['merchant_request_id']);
        $this->assertSame(20261109145126, $p['transaction_date']);
        $this->assertSame('ws_CO_TEST', $p['raw']['CheckoutRequestID']);
        $this->assertMatchesRegularExpression('/^SH[0-9A-F]{8}$/', $order->sharetReference());
        $this->assertArrayNotHasKey('paid_by', $order->destination, 'a guest is linked to nobody yet');
    }

    public function test_the_payee_gets_the_sharet_transaction_id_but_never_the_mpesa_receipt_or_number(): void
    {
        $biz = $this->scharge();
        $this->postJson('/api/v1/webhooks', ['url' => 'https://hooks.example.com/in', 'events' => ['payment_request.paid']], $this->key($biz, 'live'))->assertCreated();
        $order = $this->guestPays($biz);
        $req = ApiPaymentRequest::first();
        $api = $this->getJson('/api/v1/payment-requests/'.$req->public_id, $this->key($biz, 'live'))->assertOk()
            ->assertJsonPath('status', 'paid')->assertJsonPath('transaction_id', $order->sharetReference());
        $this->assertStringNotContainsString('717492048', $api->getContent());
        $this->assertStringNotContainsString('UK96O15681', $api->getContent());
        Http::assertSent(fn (HttpRequest $q) => $q['type'] === 'payment_request.paid' && $q['data']['transaction_id'] === $order->sharetReference()
            && ! str_contains($q->body(), '717492048'));
    }

    public function test_a_later_verified_account_with_that_mpesa_number_claims_the_payment_without_touching_its_wallet(): void
    {
        $biz = $this->scharge();
        $order = $this->guestPays($biz);

        // joins later, number typed but not verified: nothing is claimed
        $dennis = User::factory()->create(['name' => 'Dennis Kibet', 'phone' => '0717492048', 'onboarding_status' => 'active']);
        $this->assertNull(collect($this->actingAs($dennis)->getJson('/pay')->json('data'))->firstWhere('uuid', $order->uuid));
        $this->actingAs($dennis)->getJson("/pay/{$order->uuid}")->assertNotFound();

        // verified (basic KYC): the payment joins their history, as theirs, with the receipt
        $dennis->forceFill(['kyc_level' => 'basic'])->save();
        $row = collect($this->actingAs($dennis)->getJson('/pay')->json('data'))->firstWhere('uuid', $order->uuid);
        $this->assertSame('Paid scharge', $row['title']);
        $this->assertSame($order->sharetReference(), $row['transaction_id']);
        $this->assertSame('UK96O15681', $row['mpesa']['receipt']);
        $receipt = $this->actingAs($dennis)->getJson("/pay/{$order->uuid}")->assertOk()->json('order.receipt');
        $this->assertSame($order->sharetReference(), $receipt['Transaction ID']);
        $this->assertSame('UK96O15681', $receipt['M-PESA receipt']);

        // display only: no wallet money and no ledger postings for them
        $this->assertSame(0, app(Ledger::class)->account('2100', $dennis)->available());
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('ledger_postings')->where('account_id', app(Ledger::class)->account('2100', $dennis)->id)->count());

        // someone verified with a different number claims nothing
        $other = User::factory()->create(['phone' => '0722000111', 'onboarding_status' => 'active']);
        $other->forceFill(['kyc_level' => 'basic'])->save();
        $this->assertNull(collect($this->actingAs($other)->getJson('/pay')->json('data'))->firstWhere('uuid', $order->uuid));
    }

    public function test_the_backfill_command_reports_then_links_past_payments_to_verified_accounts(): void
    {
        $biz = $this->scharge();
        $order = $this->guestPays($biz);
        $dennis = User::factory()->create(['phone' => '0717492048', 'onboarding_status' => 'active']);
        \Illuminate\Support\Facades\DB::table('users')->where('id', $dennis->id)->update(['kyc_level' => 'standard']);   // verified before claims existed

        $this->artisan('sharet:claim-payments')->expectsOutputToContain('UK96O15681')->assertSuccessful();
        $this->assertArrayNotHasKey('paid_by', $order->fresh()->destination);
        $this->artisan('sharet:claim-payments --apply')->expectsOutputToContain('Linked 1 payment')->assertSuccessful();
        $this->assertSame($dennis->id, $order->fresh()->destination['paid_by']);
    }

    public function test_requests_need_collect_scope_split_only_to_sharet_and_expire(): void
    {
        $biz = $this->scharge();
        $this->postJson('/api/v1/payment-requests', ['amount' => '1000'], $this->key($biz, 'test', ['read']) + ['Idempotency-Key' => 'no-scope-1'])->assertForbidden();
        $this->postJson('/api/v1/payment-requests', ['amount' => '1000', 'splits' => [['to' => ['type' => 'bank', 'bank' => 'equity', 'account' => '0123456789'], 'percent' => 100]]], $this->key($biz) + ['Idempotency-Key' => 'splits-1'])
            ->assertStatus(422)->assertJsonPath('type', 'https://sharet.africa/docs/errors#outside_split_not_available');
        $this->postJson('/api/v1/payment-requests', ['amount' => '1000'], $this->key($this->scharge(false, '0711000222'), 'live') + ['Idempotency-Key' => 'unverified-1'])->assertForbidden();

        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '1000', 'expires_in_minutes' => 5], $this->key($biz) + ['Idempotency-Key' => 'expiring-1'])->json();
        $this->travel(6)->minutes();
        $this->assertSame(1, app(PaymentRequests::class)->expireDue());
        $this->getJson('/api/v1/payment-requests/'.$r['id'], $this->key($biz))->assertJsonPath('status', 'expired');
        $this->get('/r/'.$this->token($r['pay_url']))->assertOk()->assertSee('no longer open');
    }
}
