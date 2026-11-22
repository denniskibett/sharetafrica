<?php

namespace Tests\Feature\Mobile;

use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use App\Support\Handle;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Owner, 22 Nov 2026: sign in with the phone number and the Sharet PIN, then the phone's own lock; the PIN when there is no lock. */
class PhoneLockTest extends TestCase
{
    use RefreshDatabase;

    private const DEVICE = 'android-lock-0001';

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0, 'security.lockdown_file' => sys_get_temp_dir().'/mobile-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed(LedgerAccountSeeder::class);
    }

    private function person(string $phone, string $name, string $balance = '0', bool $pin = true): User
    {
        $u = User::factory()->create(['phone' => $phone, 'name' => $name, 'password' => bcrypt('Secret-pass-1'), 'created_at' => now()->subMonth()]);
        $u->forceFill(['kyc_level' => 'basic'])->save();
        if ($pin) {
            TransactionPin::set($u, '4826');
        }
        if ($balance !== '0') {
            $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($u, $balance));
            while (! $o->final()) {
                $o = app(RampOrders::class)->refresh($o);
            }
        }

        return $u;
    }

    private function h(string $token, ?string $key = null): array
    {
        return array_filter(['Authorization' => "Bearer $token", 'X-Device-Id' => self::DEVICE, 'Idempotency-Key' => $key]);
    }

    public function test_sign_in_with_the_phone_number_and_pin_then_the_phone_lock_approves_payments(): void
    {
        $this->person('0712345611', 'Juma Otieno', '5000');
        $akinyi = $this->person('0722410511', 'Akinyi Wanjiru');

        $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712 345 611', 'pin' => '1357', 'device_id' => self::DEVICE])->assertJsonValidationErrors('pin');
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712 345 611', 'pin' => '4826', 'device_id' => self::DEVICE, 'phone_lock' => true])
            ->assertCreated()->json('token');
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('phone_lock', true);

        // the phone lock approves it, any amount up to the day's cap; past the cap, the PIN
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => Handle::for($akinyi), 'amount' => '2000', 'biometric' => true], $this->h($token, 'phone-lock-send-1'))->assertCreated();
        config(['mobile.phone_lock_daily_max' => 250000]);
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => Handle::for($akinyi), 'amount' => '600', 'biometric' => true], $this->h($token, 'phone-lock-send-2'))->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => Handle::for($akinyi), 'amount' => '600', 'pin' => '4826'], $this->h($token, 'phone-lock-send-3'))->assertCreated();

        // turned off with the PIN: every payment asks for it
        $this->postJson('/api/mobile/v1/phone-lock', ['on' => false, 'pin' => '4826'], $this->h($token))->assertOk()->assertJsonPath('phone_lock', false);
        $this->postJson('/api/mobile/v1/send/sharet', ['handle' => Handle::for($akinyi), 'amount' => '10', 'biometric' => true], $this->h($token, 'phone-lock-send-4'))->assertJsonValidationErrors('pin');
    }

    public function test_a_phone_with_no_lock_opens_with_the_pin_and_the_password_still_works(): void
    {
        $this->person('0712345612', 'Juma Otieno');
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712345612', 'password' => 'Secret-pass-1', 'device_id' => self::DEVICE])->assertCreated()->json('token');
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('phone_lock', false);
        $this->postJson('/api/mobile/v1/auth/unlock', ['pin' => '0000'], $this->h($token))->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/auth/unlock', ['pin' => '4826'], $this->h($token))->assertOk();
    }

    public function test_no_pin_yet_means_the_password_once_and_five_wrong_pins_lock_it(): void
    {
        $this->person('0712345613', 'Juma Otieno', pin: false);
        $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712345613', 'pin' => '4826', 'device_id' => self::DEVICE])
            ->assertJsonValidationErrors(['pin' => 'Sign in with your password this once']);

        $this->person('0712345614', 'Amina Otieno');
        foreach (range(1, 5) as $i) {
            $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712345614', 'pin' => '9753', 'device_id' => self::DEVICE]);
        }
        $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712345614', 'pin' => '4826', 'device_id' => self::DEVICE])->assertStatus(422);
    }
}
