<?php

namespace Tests\Feature\Integrations;

use App\Integrations\Sms\KenyaSms;
use App\Integrations\Sms\Sms;
use App\Models\Company;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Every SMS runs on KenyaSMS with the SHARETENT account: no other provider, no fallback. */
class KenyaSmsOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['sms.default' => 'kenyasms', 'sms.kenyasms.api_key' => 'ks_live_env', 'sms.kenyasms.base_url' => 'https://kenyasms.com/api/v1']);
    }

    public function test_sms_go_through_kenyasms_as_sharetent_with_the_env_key(): void
    {
        Http::fake(['kenyasms.com/*' => Http::response(['success' => true])]);
        // A card with another key and sender: .env's key wins and the sender is always SHARETENT
        Integration::create(['company_id' => null, 'provider' => 'kenyasms', 'enabled' => true,
            'credentials' => ['api_url' => 'https://kenyasms.com/api/v1', 'api_key' => 'ks_card', 'sender_id' => 'OTHERNAME']]);

        $this->assertTrue(Sms::send('254717492048', 'Hello', 'k1'));
        Http::assertSent(fn (Request $r) => $r->url() === 'https://kenyasms.com/api/v1/sms/send'
            && $r->header('Authorization')[0] === 'Bearer ks_live_env'
            && $r['sender_id'] === 'SHARETENT' && $r['recipient'] === '254717492048' && $r['idempotency_key'] === 'k1');
        Http::assertSentCount(1);
    }

    public function test_the_card_key_is_used_only_when_env_has_none(): void
    {
        config(['sms.kenyasms.api_key' => null]);
        Http::fake(['kenyasms.com/*' => Http::response(['success' => true])]);
        Integration::create(['company_id' => null, 'provider' => 'kenyasms', 'enabled' => true,
            'credentials' => ['api_url' => 'https://kenyasms.com/api/v1', 'api_key' => 'ks_card', 'sender_id' => 'SHARETENT']]);

        $this->assertTrue(Sms::send('254717492048', 'Hi'));
        Http::assertSent(fn (Request $r) => $r->header('Authorization')[0] === 'Bearer ks_card' && $r['sender_id'] === 'SHARETENT');
    }

    public function test_a_failed_send_stays_failed_and_nothing_else_is_tried(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['success' => false, 'error' => ['code' => 'insufficient_balance']], 402)
            ->push('upstream error', 502)]);

        $this->assertFalse(Sms::send('254717492048', 'Refused'));
        $this->assertFalse(Sms::send('254717492048', 'Gateway down'));
        Http::assertSentCount(2); // one try each, both to KenyaSMS
        Http::assertNotSent(fn (Request $r) => ! str_starts_with($r->url(), 'https://kenyasms.com/'));
    }

    public function test_nothing_is_sent_without_the_kenyasms_driver_and_key(): void
    {
        Http::fake();
        config(['sms.kenyasms.api_key' => null]);
        $this->assertFalse(Sms::send('254717492048', 'No key'));

        config(['sms.kenyasms.api_key' => 'ks_live_env', 'sms.default' => 'africastalking']);
        $this->assertFalse(KenyaSms::ready());
        $this->assertFalse(Sms::send('254717492048', 'Wrong driver'));
        Http::assertNothingSent();
    }

    public function test_test_sms_goes_to_the_test_number_from_platform_settings_only(): void
    {
        Http::fake(['kenyasms.com/*' => Http::response(['success' => true])]);
        Integration::create(['company_id' => null, 'provider' => 'kenyasms', 'enabled' => true,
            'credentials' => ['api_url' => 'https://kenyasms.com/api/v1', 'api_key' => 'ks_card', 'sender_id' => 'SHARETENT']]);
        $admin = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254700000001']);
        $admin->assignRole(Role::findOrCreate('super_admin', 'web'));

        $this->actingAs($admin)->postJson('/integrations/kenyasms/send-test-sms?company=platform')
            ->assertOk()->assertJsonPath('message', 'Test SMS sent to 0717492048.');
        Http::assertSent(fn (Request $r) => $r['recipient'] === '254717492048' && $r['sender_id'] === 'SHARETENT');

        $this->artisan('sharet:sms-test')->expectsOutput('Test SMS sent to 0717492048.')->assertSuccessful();

        $company = Company::create(['name' => 'Acme']);
        $this->postJson("/integrations/kenyasms/send-test-sms?company={$company->id}")->assertNotFound();
        $this->postJson('/integrations/africastalking/send-test-sms?company=platform')->assertNotFound();
        $this->assertArrayNotHasKey('africastalking', config('integrations'));
    }

    public function test_msisdn_accepts_kenyan_mobiles_only(): void
    {
        $this->assertSame('254717492048', Sms::msisdn('0717492048'));
        $this->assertSame('254110000000', Sms::msisdn('+254 110 000 000'));
        $this->assertNull(Sms::msisdn('020123456'));
    }
}
