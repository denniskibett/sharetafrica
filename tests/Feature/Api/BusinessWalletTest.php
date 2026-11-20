<?php

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Business wallets: a company's own Sharet account that its API keys act for (docs/v2/INTEGRATION.md §1, §2). */
class BusinessWalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LedgerAccountSeeder::class);
    }

    private function key(User $u): string
    {
        $k = new ApiKey(['user_id' => $u->id, 'company_id' => $u->company_id, 'name' => 'k', 'environment' => 'test', 'scopes' => ['read'], 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        return $plain;
    }

    public function test_a_company_key_acts_for_the_company_wallet_with_the_company_sharet_id(): void
    {
        $c = Company::create(['name' => 'Mwangi Hardware']);
        $owner = User::factory()->create(['name' => 'Peter Kamau', 'company_id' => $c->id]);

        $this->getJson('/api/v1/accounts/me', ['Authorization' => 'Bearer '.$this->key($owner)])->assertOk()
            ->assertJsonPath('sharet_id', $c->sharet_id)
            ->assertJsonPath('handle', '@mwangi.hardware')
            ->assertJsonPath('name', 'Mwangi Hardware')
            ->assertJsonPath('verification', 'none');

        $wallet = $c->fresh()->walletUser();
        $this->assertTrue($wallet->is_business_wallet);
        $this->assertSame($wallet->id, $c->fresh()->walletUser()->id, 'one wallet per company');
        $this->assertNotSame($owner->sharet_id, $wallet->sharet_id, 'the owner keeps their own personal account');
    }

    public function test_the_wallet_follows_the_company_verification(): void
    {
        $c = Company::create(['name' => 'scharge']);
        $wallet = $c->walletUser();
        $this->assertSame('none', $wallet->kyc_level);

        $c->update(['verified_at' => now()]);
        $this->assertSame('business', $wallet->fresh()->kyc_level);
        $c->update(['verified_at' => null]);
        $this->assertSame('none', $wallet->fresh()->kyc_level);
    }

    public function test_nobody_can_sign_in_as_a_business_wallet(): void
    {
        $wallet = Company::create(['name' => 'scharge'])->walletUser();
        $wallet->forceFill(['password' => bcrypt('Known-pass-1')])->save();   // even if someone learned a password

        $this->post('/login', ['email' => $wallet->email, 'password' => 'Known-pass-1'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->postJson('/api/mobile/v1/auth/login', ['identifier' => $wallet->email, 'password' => 'Known-pass-1', 'device_id' => 'device-test-0001', 'platform' => 'ios'])
            ->assertStatus(422);
    }

    public function test_a_personal_key_still_acts_for_its_owner(): void
    {
        $me = User::factory()->create(['name' => 'Juma Jones']);
        $this->getJson('/api/v1/accounts/me', ['Authorization' => 'Bearer '.$this->key($me)])->assertOk()->assertJsonPath('sharet_id', $me->sharet_id);
    }
}
