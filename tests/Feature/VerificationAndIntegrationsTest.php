<?php

namespace Tests\Feature;

use App\Integrations\Contracts\IdReader;
use App\Models\Integration;
use App\Models\User;
use App\Services\Integrations;
use App\Support\Kyc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VerificationAndIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        $user = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254700000001']);
        $user->assignRole(Role::findOrCreate('super_admin', 'web'));

        return $user;
    }

    public function test_verified_is_the_mpesa_number_id_and_selfies_and_bank_grade_adds_details_and_signature(): void
    {
        Storage::fake('local');
        $this->app->instance(IdReader::class, new class implements IdReader
        {
            public function read(string $path, string $side): array
            {
                return $side === 'id_front' ? ['full_name' => 'Jane Wanjiru Doe', 'id_number' => '12345678', 'date_of_birth' => '1990-01-01', 'sex' => 'female'] : [];
            }
        });
        $user = User::factory()->create(['onboarding_status' => 'active', 'phone' => null]);
        $this->actingAs($user)->getJson('/profile/data')->assertJsonPath('level.key', 'none');

        $user->update(['phone' => '+254711222333']);
        $this->getJson('/profile/data')->assertJsonPath('level.key', 'none'); // the number alone no longer verifies

        // Too small an ID photo is refused
        $this->postJson('/profile/documents/id_front', ['file' => UploadedFile::fake()->image('id.jpg', 400, 300)])->assertStatus(422);

        $this->postJson('/profile/documents/id_front', ['file' => UploadedFile::fake()->image('id_front.jpg', 1600, 1000)])
            ->assertOk()->assertJsonPath('read.id_number', '12345678')->assertJsonPath('verified', false);
        $this->postJson('/profile/documents/id_back', ['file' => UploadedFile::fake()->image('id_back.jpg', 1600, 1000)])->assertOk();
        $png = fn ($w, $h) => 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('x.png', $w, $h)->getContent());
        foreach (['selfie_center', 'selfie_left', 'selfie_right'] as $type) {
            $last = $this->postJson("/profile/documents/$type", ['image' => $png(800, 600)])->assertOk();
        }
        $last->assertJsonPath('verified', true);
        $this->assertSame('basic', $user->fresh()->kyc_level);
        $this->getJson('/profile/data')->assertJsonPath('level.label', 'Verified')
            ->assertJsonPath('kyc.id_number', '12345678')->assertJsonPath('kyc.date_of_birth', '1990-01-01')
            ->assertJsonPath('kyc.full_name', 'Jane Wanjiru Doe')->assertJsonPath('kyc.id_type', 'national_id');

        $this->putJson('/profile/verification', [
            'id_type' => 'national_id', 'id_number' => '12345678', 'date_of_birth' => '1990-01-01', 'kra_pin' => 'A012345678Z',
            'nationality' => 'Kenyan', 'county' => 'Nairobi', 'town' => 'Westlands', 'address' => 'Mpaka Rd', 'occupation' => 'Engineer',
            'income_band' => '50k_150k', 'source_of_funds' => 'salary', 'next_of_kin_name' => 'Jane', 'next_of_kin_phone' => '+254700000009', 'pep' => false,
        ])->assertOk();
        $this->assertSame('basic', $user->fresh()->kyc_level); // signature still missing

        $this->postJson('/profile/documents/signature', ['image' => $png(600, 200)])->assertOk();

        $this->assertSame('standard', $user->fresh()->kyc_level);
        $doc = Kyc::data($user->fresh())['documents']['selfie_left']['path'];
        Storage::disk('local')->assertExists($doc);
        $this->assertStringStartsWith("kyc/{$user->id}/", $doc);

        // Only the owner or staff can open the photos
        $this->get("/kyc-documents/{$user->id}/id_front")->assertOk();
        $this->actingAs(User::factory()->create())->get("/kyc-documents/{$user->id}/id_front")->assertForbidden();
        $this->actingAs($this->admin())->get("/kyc-documents/{$user->id}/id_front")->assertOk();
    }

    public function test_mpesa_live_and_sandbox_are_exclusive_and_live_wins(): void
    {
        $this->actingAs($this->admin());

        $this->putJson('/integrations/mpesa_sandbox?company=platform', ['values' => ['consumer_key' => 'ck', 'consumer_secret' => 'cs', 'shortcode' => '174379']])->assertOk();
        $this->assertSame('sandbox', Integrations::mpesa()[1]);

        $this->putJson('/integrations/mpesa?company=platform', ['values' => ['consumer_key' => 'live', 'consumer_secret' => 'cs', 'shortcode' => '123456']])
            ->assertOk()->assertJsonPath('siblings.0.key', 'mpesa_sandbox')->assertJsonPath('siblings.0.enabled', false);
        $this->assertSame('live', Integrations::mpesa()[1]);

        $this->patchJson('/integrations/mpesa_sandbox/toggle?company=platform')->assertOk();
        $this->assertFalse(Integration::where('provider', 'mpesa')->first()->enabled);
        $this->assertSame('sandbox', Integrations::mpesa()[1]);
    }

    public function test_person_system_and_menu_json(): void
    {
        $this->actingAs($admin = $this->admin());
        $person = User::factory()->create(['onboarding_status' => 'waiting_list']);

        $this->get("/users/{$person->id}")->assertOk();
        $this->getJson("/users/{$person->id}")->assertOk()->assertJsonPath('waiting', true)->assertJsonStructure(['person', 'level', 'details', 'documents', 'stats', 'timeline', 'all_roles']);
        $role = Role::findOrCreate('support', 'web');
        $this->putJson("/users/{$person->id}/roles", ['roles' => [$role->id]])->assertOk();
        $this->assertTrue($person->fresh()->hasRole('support'));

        $this->get('/admin/system')->assertOk();
        $this->getJson('/admin/system')->assertOk()->assertJsonStructure(['values', 'files', 'timezones', 'currencies']);
        $this->getJson('/dashboard/menu')->assertOk()->assertJsonFragment(['label' => 'System']);
        $this->getJson('/api-keys')->assertOk()->assertJsonStructure(['keys']);
        $this->getJson('/integrations?company=platform')->assertOk()->assertJsonFragment(['key' => 'mpesa_sandbox']);
    }
}
