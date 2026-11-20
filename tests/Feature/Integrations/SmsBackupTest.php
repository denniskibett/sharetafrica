<?php

namespace Tests\Feature\Integrations;

use App\Integrations\Sms\Sms;
use App\Models\Company;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmsBackupTest extends TestCase
{
    use RefreshDatabase;

    private const KENYA = 'kenyasms.com/*';
    private const AT = 'api.africastalking.com/*';

    private function kenyaSms(): void
    {
        Integration::create(['company_id' => null, 'provider' => 'kenyasms', 'enabled' => true,
            'credentials' => ['api_url' => 'https://kenyasms.com/api/v1', 'api_key' => 'ks_live_x', 'sender_id' => 'SHARETENT']]);
    }

    private function africasTalking(): void
    {
        Integration::create(['company_id' => null, 'provider' => 'africastalking', 'enabled' => true,
            'credentials' => ['username' => 'sharet', 'api_key' => 'at_key', 'sender_id' => 'SHARETENT']]);
    }

    private function atAccepted(): array
    {
        return ['SMSMessageData' => ['Message' => 'Sent to 1/1', 'Recipients' => [['statusCode' => 101, 'number' => '+254717492048', 'status' => 'Success']]]];
    }

    public function test_kenyasms_sends_and_the_backup_stays_quiet(): void
    {
        $this->kenyaSms();
        $this->africasTalking();
        Http::fake([self::KENYA => Http::response(['success' => true]), self::AT => Http::response($this->atAccepted(), 201)]);

        $this->assertSame('kenyasms', Sms::send('254717492048', 'Hello', 'k1'));
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'kenyasms.com') && $r['sender_id'] === 'SHARETENT' && $r['idempotency_key'] === 'k1');
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'africastalking'));
    }

    public function test_africas_talking_takes_over_when_kenyasms_is_off_or_refuses(): void
    {
        $this->africasTalking();
        Http::fake([self::KENYA => Http::response(['success' => false, 'error' => ['code' => 'insufficient_balance']], 402), self::AT => Http::response($this->atAccepted(), 201)]);

        $this->assertSame('africastalking', Sms::send('254717492048', 'Off')); // KenyaSMS not set up
        Http::assertSent(fn (Request $r) => $r->url() === 'https://api.africastalking.com/version1/messaging'
            && $r->header('apiKey')[0] === 'at_key' && $r['to'] === '+254717492048' && $r['from'] === 'SHARETENT' && $r['username'] === 'sharet');

        $this->kenyaSms();
        $this->assertSame('africastalking', Sms::send('254717492048', 'Refused'));
    }

    public function test_no_backup_when_kenyasms_may_have_sent_it(): void
    {
        $this->kenyaSms();
        $this->africasTalking();
        Http::fake([self::KENYA => Http::response('upstream error', 502), self::AT => Http::response($this->atAccepted(), 201)]);

        $this->assertNull(Sms::send('254717492048', 'Maybe sent'));
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'africastalking')); // never a doubled money SMS
    }

    public function test_a_rejected_africas_talking_message_is_not_counted_as_sent(): void
    {
        $this->africasTalking();
        Http::fake([self::AT => Http::response(['SMSMessageData' => ['Recipients' => [['statusCode' => 403, 'status' => 'InvalidPhoneNumber']]]], 201)]);

        $this->assertNull(Sms::send('254717492048', 'Hi'));
    }

    public function test_test_sms_always_goes_to_the_test_number_from_platform_settings_only(): void
    {
        $this->kenyaSms();
        $this->africasTalking();
        Http::fake([self::KENYA => Http::response(['success' => true]), self::AT => Http::response($this->atAccepted(), 201)]);
        $admin = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254700000001']);
        $admin->assignRole(Role::findOrCreate('super_admin', 'web'));

        $this->actingAs($admin)->postJson('/integrations/kenyasms/send-test-sms?company=platform')
            ->assertOk()->assertJsonPath('message', 'Test SMS sent to 0717492048.');
        $this->postJson('/integrations/africastalking/send-test-sms?company=platform')->assertOk();

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'kenyasms.com') && $r['recipient'] === '254717492048');
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'africastalking') && $r['to'] === '+254717492048');
        Http::assertSentCount(2);

        $company = Company::create(['name' => 'Acme']);
        $this->postJson("/integrations/kenyasms/send-test-sms?company={$company->id}")->assertNotFound();
    }

    public function test_msisdn_accepts_kenyan_mobiles_only(): void
    {
        $this->assertSame('254717492048', Sms::msisdn('0717492048'));
        $this->assertSame('254110000000', Sms::msisdn('+254 110 000 000'));
        $this->assertNull(Sms::msisdn('020123456'));
    }
}
