<?php

namespace Tests\Feature\Integrations;

use App\Integrations\Claude\Claude;
use App\Models\Company;
use App\Models\Integration;
use App\Models\User;
use App\Support\Kyc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClaudeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => null, 'services.anthropic.model' => 'claude-sonnet-5-5']);
    }

    private function admin(): User
    {
        $user = User::factory()->create(['onboarding_status' => 'active']);
        $user->assignRole(Role::findOrCreate('super_admin', 'web'));

        return $user;
    }

    private function card(array $credentials = ['api_key' => 'sk-ant-card'], bool $enabled = true): Integration
    {
        return Integration::create(['company_id' => null, 'provider' => 'claude', 'credentials' => $credentials, 'enabled' => $enabled]);
    }

    /** A Messages API reply whose text block is $json (what structured outputs return). */
    private function reply(array $json, string $stop = 'end_turn'): array
    {
        return ['id' => 'msg_test', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-sonnet-5-5',
            'content' => $stop === 'refusal' ? [] : [['type' => 'text', 'text' => json_encode($json)]],
            'stop_reason' => $stop, 'stop_sequence' => null, 'usage' => ['input_tokens' => 12, 'output_tokens' => 8]];
    }

    /** A person who sent their photos; the automatic checks did not pass (no ID read), so they wait for staff. */
    private function pendingPerson(): User
    {
        Storage::fake('local');
        $user = User::factory()->create(['name' => 'Jane Doe', 'phone' => '+254711222333', 'onboarding_status' => 'active']);
        foreach (['id_front', 'id_back'] as $side) {
            Kyc::storeDocument($user, $side, UploadedFile::fake()->image("$side.jpg", 1600, 1000));
        }
        $png = 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('s.png', 800, 600)->getContent());
        foreach (['selfie_center', 'selfie_left', 'selfie_right'] as $type) {
            Kyc::storeDocument($user, $type, $png);
        }
        $kyc = Kyc::data($user->fresh());
        $kyc['id_number'] = '12345678';
        $kyc['date_of_birth'] = '1990-01-01';
        Kyc::save($user->fresh(), $kyc);

        return $user->fresh();
    }

    public function test_the_card_key_wins_over_env_and_the_model_defaults_to_config(): void
    {
        $this->assertFalse(Claude::enabled());

        config(['services.anthropic.key' => 'sk-ant-env']);
        $this->assertSame('sk-ant-env', Claude::apiKey());
        $this->assertSame('claude-sonnet-5-5', Claude::model());

        $card = $this->card();
        $this->assertSame('sk-ant-card', Claude::apiKey());
        $card->update(['credentials' => ['api_key' => 'sk-ant-card', 'model' => 'claude-opus-5-5']]);
        $this->assertSame('claude-opus-5-5', Claude::model());

        $card->update(['enabled' => false]);
        $this->assertSame('sk-ant-env', Claude::apiKey()); // off: back to .env

        // A company row never supplies the key: it is Sharet's
        $company = Company::create(['name' => 'Acme']);
        Integration::create(['company_id' => $company->id, 'provider' => 'claude', 'credentials' => ['api_key' => 'sk-ant-company'], 'enabled' => true]);
        $this->assertSame('sk-ant-env', Claude::apiKey());
    }

    public function test_the_key_is_stored_encrypted_and_never_sent_back(): void
    {
        $this->actingAs($this->admin())
            ->putJson('/integrations/claude?company=platform', ['values' => ['api_key' => 'sk-ant-secret', 'model' => '']])
            ->assertOk()->assertJsonMissing(['api_key' => 'sk-ant-secret']);

        $raw = \DB::table('integrations')->where('provider', 'claude')->value('credentials');
        $this->assertStringNotContainsString('sk-ant-secret', $raw);
        $this->assertSame('sk-ant-secret', Claude::apiKey());
        $this->getJson('/integrations?company=platform')->assertOk()->assertDontSee('sk-ant-secret');
    }

    public function test_the_card_exists_only_on_the_platform_settings(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $admin = $this->admin();

        $keys = collect($this->actingAs($admin)->getJson("/integrations?company={$company->id}")->assertOk()->json('providers'))->pluck('key');
        $this->assertNotContains('claude', $keys);
        $this->assertContains('claude', collect($this->getJson('/integrations?company=platform')->json('providers'))->pluck('key'));

        $this->putJson("/integrations/claude?company={$company->id}", ['values' => ['api_key' => 'sk-ant-x']])->assertNotFound();
        $this->assertDatabaseMissing('integrations', ['provider' => 'claude', 'company_id' => $company->id]);
    }

    public function test_test_connection_calls_anthropic_with_the_card_key_and_model(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->reply(['status' => 'ready']))]);
        $this->card(['api_key' => 'sk-ant-card', 'model' => 'claude-sonnet-5-5']);

        $this->actingAs($this->admin())->postJson('/integrations/claude/test?company=platform')
            ->assertOk()->assertJsonPath('ok', true)->assertJsonPath('message', 'Claude answered on claude-sonnet-5-5.');

        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://api.anthropic.com/v1/messages')
            && $r->header('Authorization')[0] === 'Bearer sk-ant-card'
            && $r['model'] === 'claude-sonnet-5-5'
            && $r['output_config']['format']['type'] === 'json_schema');
    }

    public function test_a_refused_key_is_reported_without_the_key(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['type' => 'error', 'error' => ['type' => 'authentication_error', 'message' => 'invalid x-api-key']], 401)]);
        $this->card(['api_key' => 'sk-ant-wrong']);

        $res = $this->actingAs($this->admin())->postJson('/integrations/claude/test?company=platform')->assertStatus(422);
        $this->assertSame('Anthropic refused the API key. Check it under Integrations.', $res->json('message'));
        $this->assertStringNotContainsString('sk-ant-wrong', $res->getContent());
    }

    public function test_staff_get_a_second_reading_that_never_decides(): void
    {
        $person = $this->pendingPerson();
        $this->assertSame('pending', $person->kyc_review);
        $before = Kyc::data($person);

        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([
            'full_name' => 'JANE WANJIRU DOE', 'id_number' => '12345678', 'date_of_birth' => '1991-01-01', 'sex' => 'F',
            'document_type' => 'Kenyan national ID', 'legible' => true, 'concerns' => 'Glare over the date of birth.',
        ]))]);
        $this->card();

        $res = $this->actingAs($staff = $this->admin())->postJson("/users/{$person->id}/verification/claude")->assertOk();
        $compare = collect($res->json('assist.compare'))->keyBy('label');
        $this->assertTrue($compare['Name on the account']['same']);
        $this->assertTrue($compare['ID number']['same']);
        $this->assertFalse($compare['Date of birth']['same']);           // flagged for the reviewer
        $res->assertJsonPath('assist.concerns', 'Glare over the date of birth.')->assertJsonPath('assist.by', $staff->name);

        // Both photos went to Anthropic as images
        Http::assertSent(fn (Request $r) => count(array_filter($r['messages'][0]['content'], fn ($b) => $b['type'] === 'image')) === 2);

        // Advice only: review, details and level untouched
        $after = $person->fresh();
        $this->assertSame('pending', $after->kyc_review);
        $this->assertSame('none', $after->kyc_level);
        $data = Kyc::data($after);
        $this->assertSame($before['review'], $data['review']);
        $this->assertSame('1990-01-01', $data['date_of_birth']);
        $this->assertSame('read', $data['assist']['status']);

        // It shows on the person's page for the next reviewer
        $this->getJson("/users/{$person->id}")->assertJsonPath('review.claude.available', true)
            ->assertJsonPath('review.claude.assist.status', 'read')->assertJsonPath('review.status', 'pending');
    }

    public function test_a_refusal_or_no_key_changes_nothing(): void
    {
        $person = $this->pendingPerson();
        $staff = $this->admin();

        $this->actingAs($staff)->postJson("/users/{$person->id}/verification/claude")->assertStatus(422); // no key yet
        Http::assertNothingSent();

        Http::fake(['api.anthropic.com/*' => Http::response($this->reply([], 'refusal'))]);
        $this->card();
        $this->postJson("/users/{$person->id}/verification/claude")->assertStatus(422)
            ->assertJsonPath('assist.status', 'failed')->assertJsonPath('message', 'Claude declined to read this one. Check it by eye.');
        $this->assertSame('pending', $person->fresh()->kyc_review);

        // Nobody asks Claude about their own account
        $this->actingAs($person)->postJson("/users/{$person->id}/verification/claude")->assertForbidden();
    }
}
