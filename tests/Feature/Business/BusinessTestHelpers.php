<?php

namespace Tests\Feature\Business;

use App\Models\BusinessMember;
use App\Models\Company;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use App\Support\Handle;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;

/** People, verified businesses with funded wallets, and signed-in phones for the Business and Trade API tests. */
trait BusinessTestHelpers
{
    private int $phones = 0;

    protected function bootBusiness(): void
    {
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0, 'aml.enabled' => true,
            'security.lockdown_file' => sys_get_temp_dir().'/business-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed(LedgerAccountSeeder::class);
    }

    protected function person(string $name, string $balance = '0', string $kyc = 'basic'): User
    {
        $u = User::factory()->create(['phone' => '07'.str_pad((string) (11000000 + ++$this->phones * 7919), 8, '0', STR_PAD_LEFT), 'name' => $name, 'password' => bcrypt('Secret-pass-1')]);
        $u->forceFill(['kyc_level' => $kyc])->save();
        TransactionPin::set($u, '4826');
        $balance !== '0' && $this->settle(app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($u, $balance)));

        return $u->fresh();
    }

    /** A verified company owned by $owner (who registered it), its wallet funded with $balance. */
    protected function business(User $owner, string $name, string $balance = '0', bool $verified = true): Company
    {
        $c = Company::create(['name' => $name, 'status' => true]);
        $owner->update(['company_id' => $c->id]);
        $verified && $c->verify();
        $wallet = $c->fresh()->walletUser();
        if ($balance !== '0') {
            config(['pricing.limits.standard.per_order' => 1_000_000_000, 'pricing.limits.standard.per_day' => 10_000_000_000]);
            $funder = $this->person('Funder '.$name, $balance, 'standard');
            config(['aml.enabled' => false]);   // funding the test wallet is not what is under test
            app(RampOrders::class)->transfer($funder->fresh(), Handle::for($wallet), $balance);
            config(['aml.enabled' => true]);
        }

        return $c->fresh();
    }

    protected function member(Company $c, User $u, string $role, ?int $limitCents = null): BusinessMember
    {
        \App\Business\Team::ensureOwner($c);

        return BusinessMember::create(['company_id' => $c->id, 'user_id' => $u->id, 'role' => $role, 'approval_limit' => $limitCents]);
    }

    protected function settle($o)
    {
        for ($i = 0; $i < 10 && ! $o->final(); $i++) {
            $o = app(RampOrders::class)->refresh($o);
        }

        return $o;
    }

    protected function phone(User $u): array
    {
        $device = 'phone-'.$u->id.'-0001';
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => $u->email, 'password' => 'Secret-pass-1', 'device_id' => $device])->assertCreated()->json('token');

        return ['Authorization' => "Bearer $token", 'X-Device-Id' => $device];
    }

    protected function key(array $h, string $key): array
    {
        return $h + ['Idempotency-Key' => $key];
    }
}
