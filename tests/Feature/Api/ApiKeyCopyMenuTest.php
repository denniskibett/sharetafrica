<?php

namespace Tests\Feature\Api;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** API keys page: the row menu copies the key ID, the Sharet ID and IPs; never the secret (only its fingerprint is kept). */
class ApiKeyCopyMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_keys_list_gives_copyable_key_id_and_sharet_id_but_never_the_secret(): void
    {
        $c = Company::create(['name' => 'Scharge']);
        $admin = User::factory()->create(['onboarding_status' => 'active', 'company_id' => $c->id]);
        $admin->assignRole(Role::findOrCreate('super_admin', 'web'));
        $k = new ApiKey(['user_id' => $admin->id, 'company_id' => $c->id, 'name' => 'scharge main', 'environment' => 'live', 'scopes' => ['read', 'collect'], 'allowed_ips' => ['185.113.249.146'], 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        $res = $this->actingAs($admin)->getJson('/api-keys')->assertOk()
            ->assertJsonPath('keys.0.hint', $k->prefix.'…'.$k->last_four)
            ->assertJsonPath('keys.0.account', $c->sharet_id);
        $this->assertStringNotContainsString($plain, $res->getContent());

        $this->actingAs($admin)->get('/api-keys')->assertOk()->assertSee('Copy full key')->assertSee('Copy key ID')->assertSee('Copy Sharet ID');
    }
}
