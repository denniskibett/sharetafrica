<?php

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use App\Support\SharetId;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** API v1 steps 1–2: Sharet IDs, key scopes, accounts/me, resolve, quotes (docs/v2/INTEGRATION.md). */
class V1AccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LedgerAccountSeeder::class);
    }

    private function key(User $user, array $scopes = ['read'], string $env = 'test'): string
    {
        $k = new ApiKey(['user_id' => $user->id, 'company_id' => $user->company_id, 'name' => 'Test', 'environment' => $env, 'scopes' => $scopes, 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        return $plain;
    }

    public function test_every_new_person_and_business_gets_a_fixed_sharet_id(): void
    {
        $u = User::factory()->create(['name' => 'Juma Jones']);
        $c = Company::create(['name' => 'Mwangi Hardware']);

        $this->assertMatchesRegularExpression(SharetId::PATTERN, $u->sharet_id);
        $this->assertMatchesRegularExpression(SharetId::PATTERN, $c->sharet_id);
        $this->assertNotSame($u->sharet_id, $c->sharet_id);

        $before = $u->sharet_id;
        $u->forceFill(['handle' => 'juma.new'])->save();
        $this->assertSame($before, $u->fresh()->sharet_id, 'renaming the @name never changes the Sharet ID');

        $this->assertSame('SH-4K7Q2M', SharetId::normalise(' sh-4k7q2m '));
        $this->assertSame('SH-4K7Q2M', SharetId::normalise('4K7Q2M'));
        $this->assertNull(SharetId::normalise('SH-O0I1LU'), 'look-alike characters are never issued');
    }

    public function test_me_shows_the_sharet_id_handle_and_key_scopes(): void
    {
        $u = User::factory()->create(['name' => 'Juma Jones']);
        $this->getJson('/api/v1/accounts/me', ['Authorization' => 'Bearer '.$this->key($u, ['read', 'payout'])])
            ->assertOk()
            ->assertJsonPath('sharet_id', $u->sharet_id)
            ->assertJsonPath('handle', '@juma.jones')
            ->assertJsonPath('key.scopes', ['read', 'payout'])
            ->assertJsonPath('balance.available', '0.00');

        $this->getJson('/api/v1/accounts/me')->assertUnauthorized();
    }

    public function test_resolve_by_handle_or_sharet_id_returns_the_id_and_a_masked_name(): void
    {
        $me = User::factory()->create(['name' => 'Juma Jones']);
        $akinyi = User::factory()->create(['name' => 'Akinyi Wekesa', 'handle' => 'akinyi']);
        $h = ['Authorization' => 'Bearer '.$this->key($me)];

        $this->getJson('/api/v1/accounts/resolve?handle=@akinyi', $h)->assertOk()
            ->assertJsonPath('sharet_id', $akinyi->sharet_id)->assertJsonPath('name', 'Akinyi W.')->assertJsonPath('handle', '@akinyi');
        $this->getJson('/api/v1/accounts/resolve?sharet_id='.strtolower($akinyi->sharet_id), $h)->assertOk()->assertJsonPath('handle', '@akinyi');
        $this->getJson('/api/v1/accounts/resolve?handle=@nobody-here', $h)->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json')->assertJsonPath('type', 'https://sharet.africa/docs/errors#account_not_found');
    }

    public function test_quotes_are_free_inside_sharet_and_priced_outside_without_moving_money(): void
    {
        $me = User::factory()->create(['name' => 'Juma Jones']);
        $akinyi = User::factory()->create(['name' => 'Akinyi Wekesa', 'handle' => 'akinyi']);
        $h = ['Authorization' => 'Bearer '.$this->key($me)];

        $this->postJson('/api/v1/quotes', ['amount' => '5000.00', 'to' => ['handle' => '@akinyi']], $h)->assertOk()
            ->assertJsonPath('fee', '0.00')->assertJsonPath('total', '5000.00')->assertJsonPath('to.sharet_id', $akinyi->sharet_id);

        $outside = $this->postJson('/api/v1/quotes', ['amount' => '5000.00', 'to' => ['type' => 'mpesa', 'phone' => '0722 000 904']], $h)->assertOk();
        $this->assertGreaterThan(0, (float) $outside->json('fee'));
        $this->assertSame('254722000904', $outside->json('to.phone'));

        $this->postJson('/api/v1/quotes', ['amount' => '5000.50', 'to' => ['handle' => '@akinyi']], $h)->assertStatus(422)->assertJsonPath('type', 'https://sharet.africa/docs/errors#invalid_amount');
        $this->postJson('/api/v1/quotes', ['amount' => '100', 'to' => ['type' => 'mpesa', 'phone' => '123']], $h)->assertStatus(422)->assertJsonPath('type', 'https://sharet.africa/docs/errors#invalid_destination');
        $this->postJson('/api/v1/quotes', ['amount' => '100', 'to' => ['sharet_id' => $me->sharet_id]], $h)->assertStatus(422)->assertJsonPath('type', 'https://sharet.africa/docs/errors#own_account');
        $this->assertDatabaseCount('ramp_orders', 0);
    }

    public function test_keys_only_do_what_their_scopes_allow_and_new_keys_default_to_read(): void
    {
        $me = User::factory()->create(['name' => 'Juma Jones']);
        $k = new ApiKey(['user_id' => $me->id, 'name' => 'No scopes', 'environment' => 'test', 'scopes' => [], 'enabled' => true]);
        $plain = $k->issue();
        $k->save();
        $this->getJson('/api/v1/accounts/me', ['Authorization' => "Bearer $plain"])->assertForbidden()->assertJsonPath('type', 'https://sharet.africa/docs/errors#missing_scope');

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $me->assignRole('techie');
        $this->actingAs($me)->postJson('/api-keys', ['name' => 'Breve', 'environment' => 'test'])->assertCreated()->assertJsonPath('key.scopes', ['read']);
        $this->actingAs($me)->postJson('/api-keys', ['name' => 'scharge', 'environment' => 'test', 'scopes' => ['collect', 'payout']])->assertCreated()->assertJsonPath('key.scopes', ['read', 'collect', 'payout']);
        $this->actingAs($me)->postJson('/api-keys', ['name' => 'Bad', 'environment' => 'test', 'scopes' => ['admin']])->assertStatus(422);
    }
}
