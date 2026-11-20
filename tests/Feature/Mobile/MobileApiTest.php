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
}
