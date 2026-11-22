<?php

namespace Tests\Feature\Mobile;

use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private const DEVICE = 'iphone-test-0001';

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0,
            'security.lockdown_file' => sys_get_temp_dir().'/mobile-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed(LedgerAccountSeeder::class);
    }

    private function person(string $phone, string $name, string $balance = '0'): User
    {
        $u = User::factory()->create(['phone' => $phone, 'name' => $name, 'password' => bcrypt('Secret-pass-1')]);
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

    private function signIn(string $identifier): string
    {
        return $this->postJson('/api/mobile/v1/auth/login', ['identifier' => $identifier, 'password' => 'Secret-pass-1', 'device_id' => self::DEVICE, 'platform' => 'ios'])
            ->assertCreated()->json('token');
    }

    private function h(string $token, ?string $key = null): array
    {
        return array_filter(['Authorization' => "Bearer $token", 'X-Device-Id' => self::DEVICE, 'Idempotency-Key' => $key]);
    }

    public function test_sign_in_by_phone_or_email_is_tied_to_the_phone_and_revocable(): void
    {
        $u = $this->person('0712345678', 'Juma Otieno');
        $token = $this->signIn('0712 345 678');
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertOk()->assertJsonPath('user.first_name', 'Juma')->assertJsonPath('user.pin_set', true);

        $this->getJson('/api/mobile/v1/me', ['Authorization' => "Bearer $token", 'X-Device-Id' => 'another-phone-99'])->assertUnauthorized();
        $this->postJson('/api/mobile/v1/auth/login', ['identifier' => $u->email, 'password' => 'wrong', 'device_id' => self::DEVICE])->assertStatus(422);
        $this->signIn($u->email);   // signing in again on this phone replaces the first token
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertUnauthorized();
    }

    public function test_sharet_to_sharet_runs_once_however_often_the_phone_retries(): void
    {
        $juma = $this->person('0712345678', 'Juma Otieno', '1000');
        $akinyi = $this->person('0722118904', 'Akinyi Wekesa');
        $token = $this->signIn('0712345678');
        $handle = \App\Support\Handle::for($akinyi);

        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $handle, 'amount' => '200', 'pin' => '4826'], $this->h($token))->assertStatus(428);

        $first = $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $handle, 'amount' => '200', 'pin' => '4826'], $this->h($token, 'pay-0001-abcd'))->assertCreated();
        $again = $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $handle, 'amount' => '200', 'pin' => '4826'], $this->h($token, 'pay-0001-abcd'))->assertCreated()->assertHeader('Idempotent-Replay', 'true');
        $this->assertSame($first->json('order.uuid'), $again->json('order.uuid'));

        $orders = app(RampOrders::class);
        $this->assertSame(80000, $orders->walletBalance($juma));
        $this->assertSame(20000, $orders->walletBalance($akinyi));
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $handle, 'amount' => '999', 'pin' => '4826'], $this->h($token, 'pay-0001-abcd'))->assertStatus(409);
    }

    public function test_a_wrong_pin_moves_nothing_and_the_same_key_works_once_corrected(): void
    {
        $this->person('0712345678', 'Juma Otieno', '1000');
        $akinyi = $this->person('0722118904', 'Akinyi Wekesa');
        $token = $this->signIn('0712345678');
        $body = ['handle' => \App\Support\Handle::for($akinyi), 'amount' => '100'];

        $this->postJson('/api/mobile/v1/send/sharet', $body + ['pin' => '0000'], $this->h($token, 'pay-0002-abcd'))->assertStatus(422)->assertJsonValidationErrors('pin');
        $this->assertSame(0, app(RampOrders::class)->walletBalance($akinyi));
        $this->postJson('/api/mobile/v1/send/sharet', $body + ['pin' => '4826'], $this->h($token, 'pay-0002-abcd'))->assertCreated();
        $this->assertSame(10000, app(RampOrders::class)->walletBalance($akinyi));
    }

    public function test_sharet_to_mpesa_quote_then_confirm_with_pin(): void
    {
        $this->person('0712345678', 'Juma Otieno', '2000');
        $token = $this->signIn('0712345678');

        $q = $this->postJson('/api/mobile/v1/quote', ['phone' => '0722118904', 'amount' => '500'], $this->h($token, 'quote-0003-ab'))->assertCreated();
        $uuid = $q->json('order.uuid');
        $this->assertSame('quoted', $q->json('order.status'));

        $this->postJson("/api/mobile/v1/orders/$uuid/confirm", ['pin' => '4826'], $this->h($token, 'conf-0003-ab'))->assertOk();
        $this->postJson("/api/mobile/v1/orders/$uuid/confirm", ['pin' => '4826'], $this->h($token, 'conf-0003-ab'))->assertOk()->assertHeader('Idempotent-Replay', 'true');

        for ($i = 0; $i < 5 && ! RampOrder::where('uuid', $uuid)->first()->final(); $i++) {
            $this->getJson("/api/mobile/v1/orders/$uuid", $this->h($token))->assertOk();
        }
        $this->assertSame('paid', RampOrder::where('uuid', $uuid)->first()->status);
        $this->assertSame(1, RampOrder::where('uuid', $uuid)->count());
    }

    public function test_a_frozen_ledger_refuses_money_and_forgets_the_key(): void
    {
        $this->person('0712345678', 'Juma Otieno', '1000');
        $akinyi = $this->person('0722118904', 'Akinyi Wekesa');
        $token = $this->signIn('0712345678');
        Lockdown::set('ledger', true, 'test', 'tests');

        $body = ['handle' => \App\Support\Handle::for($akinyi), 'amount' => '100', 'pin' => '4826'];
        $this->postJson('/api/mobile/v1/send/sharet', $body, $this->h($token, 'pay-0004-abcd'))->assertStatus(423);
        $this->getJson('/api/mobile/v1/config')->assertJsonPath('payments_open', false);

        Lockdown::set('ledger', false, null, 'tests');
        $this->postJson('/api/mobile/v1/send/sharet', $body, $this->h($token, 'pay-0004-abcd'))->assertCreated();
    }

    public function test_the_app_gets_requests_account_number_and_card_top_ups(): void
    {
        $juma = $this->person('0712345678', 'Juma Otieno');
        $akinyi = $this->person('0722410556', 'Akinyi Wanjiru');
        $token = $this->signIn('0712345678');

        // Without Cloud9 connected: only the M-PESA prompt, no account number
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('add_ways', ['mpesa'])->assertJsonPath('account_number', null);

        $this->postJson('/api/mobile/v1/requests', ['to' => '@'.\App\Support\Handle::for($akinyi), 'amount' => '600', 'note' => 'Lunch'], $this->h($token))->assertCreated();
        $this->postJson('/api/mobile/v1/requests/split', ['total' => '900', 'people' => ['0733112908', '@'.\App\Support\Handle::for($akinyi)]], $this->h($token))->assertCreated();
        $sent = $this->getJson('/api/mobile/v1/requests', $this->h($token))->assertOk()->json('sent');
        $this->assertCount(3, $sent);
        $this->postJson('/api/mobile/v1/requests/'.$sent[0]['id'].'/cancel', [], $this->h($token))->assertOk();

        $theirs = $this->signIn('0722410556');
        $toPay = $this->getJson('/api/mobile/v1/requests', $this->h($theirs))->json('to_pay');
        $this->assertCount(2, $toPay);
        $open = collect($toPay)->firstWhere('status', 'open');
        $this->postJson('/api/mobile/v1/requests/'.$open['id'].'/decline', [], $this->h($theirs))->assertOk();

        // With Cloud9 connected: account number and card
        \App\Models\Integration::create(['company_id' => null, 'provider' => 'cloud9', 'enabled' => true, 'credentials' => ['environment' => 'sandbox', 'api_key' => 'tokenTestabc', 'wallet_id' => '7VXW9MJD']]);
        \Illuminate\Support\Facades\Http::fake([
            '*/paybill_accounts/' => \Illuminate\Support\Facades\Http::response(['account_id' => 'A1', 'paybill_number' => '123456', 'account' => 482915]),
            '*/checkout/sessions/' => \Illuminate\Support\Facades\Http::response(['checkout_id' => 'K7', 'checkout_url' => 'https://pay.example/k7', 'charge' => '35.00']),
        ]);
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('add_ways', ['mpesa', 'account', 'card']);
        $this->getJson('/api/mobile/v1/account-number', $this->h($token))->assertJsonPath('account_number.formatted', '48 2915');
        $this->postJson('/api/mobile/v1/add-money/card', ['amount' => '1000'], $this->h($token, 'card-top-up-1'))->assertCreated()->assertJsonPath('url', 'https://pay.example/k7');
    }

    public function test_the_app_pays_paybills_tills_and_banks_shows_full_receipts_and_pays_requests_in_app(): void
    {
        $juma = $this->person('0712345678', 'Juma Otieno', '5000');
        $akinyi = $this->person('0722410556', 'Akinyi Wanjiru');
        $token = $this->signIn('0712345678');
        $this->getJson('/api/mobile/v1/config')->assertJsonPath('banks.kcb', 'KCB Bank');

        foreach ([['type' => 'paybill', 'paybill' => '888880', 'account' => '54001234'], ['type' => 'till', 'till' => '512345'], ['type' => 'bank', 'bank' => 'kcb', 'account' => '1234567890']] as $i => $to) {
            $q = $this->postJson('/api/mobile/v1/quote', $to + ['amount' => '100'], $this->h($token, 'quote-key-'.$i))->assertCreated()->json('order');
            $this->assertSame($to['type'], $q['destination_type'] ?? $to['type']);
            $this->postJson('/api/mobile/v1/orders/'.$q['uuid'].'/confirm', ['pin' => '4826'], $this->h($token, 'confirm-key-'.$i))->assertOk();
        }
        $receipt = $this->getJson('/api/mobile/v1/orders/'.$q['uuid'], $this->h($token))->assertOk()->json('order.receipt');
        $this->assertArrayHasKey('Sharet reference', $receipt);
        $this->assertSame('KCB Bank', $receipt['Bank']);

        // Akinyi pays Juma's request inside the app with her PIN
        $this->postJson('/api/mobile/v1/requests', ['to' => '@'.\App\Support\Handle::for($akinyi), 'amount' => '50'], $this->h($token))->assertCreated();
        $this->person('0733000001', 'Spare Person', '0');
        $theirs = $this->signIn('0722410556');
        $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($akinyi, '100'));
        while (! $o->final()) {
            $o = app(RampOrders::class)->refresh($o);
        }
        $id = $this->getJson('/api/mobile/v1/requests', $this->h($theirs))->json('to_pay.0.id');
        $this->postJson("/api/mobile/v1/requests/$id/pay", ['pin' => '0000'], $this->h($theirs, 'pay-request-bad'))->assertUnprocessable();
        $this->postJson("/api/mobile/v1/requests/$id/pay", ['pin' => '4826'], $this->h($theirs, 'pay-request-ok'))->assertOk()->assertJsonPath('order.receipt.Sharet reference', $id);
        $this->assertSame('paid', $this->getJson('/api/mobile/v1/requests', $this->h($theirs))->json('to_pay.0.status'));
    }

    public function test_quick_pay_freeze_favourites_and_month_totals(): void
    {
        $juma = $this->person('0712345678', 'Juma Otieno', '20000');
        $akinyi = $this->person('0722410556', 'Akinyi Wanjiru');
        $to = '@'.\App\Support\Handle::for($akinyi);
        $token = $this->signIn('0712345678');

        // Quick pay is off until set with the PIN
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '100', 'biometric' => true], $this->h($token, 'quickpay-qp-off-1'))->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/quick-pay', ['limit' => 1000, 'pin' => '0000'], $this->h($token))->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/quick-pay', ['limit' => 1000, 'pin' => '4826'], $this->h($token))->assertOk()->assertJsonPath('quick_pay_limit', '1,000.00');
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('quick_pay_limit', '1,000.00');

        // Fingerprint within the limit; above it, the PIN; the day is capped at 3 × the limit
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '900', 'biometric' => true], $this->h($token, 'quickpay-qp-ok-1'))->assertCreated();
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '1500', 'biometric' => true], $this->h($token, 'quickpay-qp-big-1'))->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '1000', 'biometric' => true], $this->h($token, 'quickpay-qp-ok-2'))->assertCreated();
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '1000', 'biometric' => true], $this->h($token, 'quickpay-qp-ok-3'))->assertCreated();
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '200', 'biometric' => true], $this->h($token, 'quickpay-qp-cap-1'))->assertJsonValidationErrors('pin');   // 2,900 + 200 > 3,000
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '200', 'pin' => '4826'], $this->h($token, 'quickpay-qp-pin-1'))->assertCreated();

        // Pay again and this month's totals
        $this->getJson('/api/mobile/v1/favourites', $this->h($token))->assertJsonPath('favourites.0.type', 'sharet')->assertJsonPath('favourites.0.times', 4)->assertJsonPath('favourites.0.fields.handle', $to);
        $this->getJson('/api/mobile/v1/activity', $this->h($token))->assertJsonPath('month.out', '3,100.00')->assertJsonPath('month.in', '20,000.00');

        // Freeze: nothing leaves; money can still come in; unfreeze needs the PIN
        $this->postJson('/api/mobile/v1/me/freeze', [], $this->h($token))->assertOk();
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '100', 'pin' => '4826'], $this->h($token, 'quickpay-frozen-1'))->assertJsonValidationErrors('amount');
        $this->postJson('/api/mobile/v1/quote', ['phone' => '0733112908', 'amount' => '100'], $this->h($token, 'quickpay-frozen-2'))->assertJsonValidationErrors('amount');
        $this->postJson('/api/mobile/v1/add-money', ['amount' => '100'], $this->h($token, 'quickpay-frozen-3'))->assertCreated();
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('frozen', true);
        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '0000'], $this->h($token))->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '4826'], $this->h($token))->assertOk();
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => $to, 'amount' => '100', 'pin' => '4826'], $this->h($token, 'quickpay-after-1'))->assertCreated();
    }
}
