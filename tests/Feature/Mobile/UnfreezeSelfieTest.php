<?php

namespace Tests\Feature\Mobile;

use App\Models\User;
use App\Security\Lockdown;
use App\Support\Kyc;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unfreeze needs the PIN and a selfie, and a person in Administration › Verifications approves it after comparing the
 * selfie with the verification selfie: a thief with the phone and the PIN can't undo a freeze.
 */
class UnfreezeSelfieTest extends TestCase
{
    use RefreshDatabase;

    private const DEVICE = 'android-test-0042';

    private const SELFIE = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ==';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['security.lockdown_file' => sys_get_temp_dir().'/unfreeze-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
    }

    /** @return array{User, string} */
    private function frozenPerson(): array
    {
        $u = User::factory()->create(['phone' => '0712345678', 'name' => 'Juma Otieno', 'password' => bcrypt('Secret-pass-1')]);
        $u->forceFill(['kyc_level' => 'basic'])->save();
        TransactionPin::set($u, '4826');
        Kyc::storeDocument($u, 'selfie_center', UploadedFile::fake()->image('me.jpg', 600, 800));
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712345678', 'password' => 'Secret-pass-1', 'device_id' => self::DEVICE, 'platform' => 'android'])
            ->assertCreated()->json('token');
        $this->postJson('/api/mobile/v1/me/freeze', [], $this->h($token))->assertOk();

        return [$u->fresh(), $token];
    }

    private function h(string $token): array
    {
        return ['Authorization' => "Bearer $token", 'X-Device-Id' => self::DEVICE];
    }

    private function staff(): User
    {
        $staff = User::factory()->create(['name' => 'Amina Staff']);
        $staff->assignRole('super_admin');

        return $staff;
    }

    public function test_the_pin_alone_never_unfreezes(): void
    {
        [$juma, $token] = $this->frozenPerson();

        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '4826'], $this->h($token))
            ->assertJsonValidationErrors(['selfie' => 'Take a selfie so we can check it is you.']);
        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '4826', 'selfie' => 'not a photo'], $this->h($token))->assertJsonValidationErrors('selfie');
        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '0000', 'selfie' => self::SELFIE], $this->h($token))->assertJsonValidationErrors('pin');
        $this->assertNotNull($juma->fresh()->frozen_at);
        $this->assertNull($juma->fresh()->unfreeze_requested_at);
    }

    public function test_a_selfie_waits_for_staff_who_approve_or_reject_it(): void
    {
        [$juma, $token] = $this->frozenPerson();
        $staff = $this->staff();

        // PIN + selfie: queued, still frozen
        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '4826', 'selfie' => self::SELFIE], $this->h($token))
            ->assertStatus(202)->assertJsonPath('frozen', true)->assertJsonPath('unfreeze.status', 'pending');
        $this->assertNotNull($juma->fresh()->frozen_at);
        $path = Kyc::data($juma->fresh())['documents']['unfreeze_selfie']['path'];
        Storage::disk('local')->assertExists($path);
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('frozen', true)->assertJsonPath('unfreeze.status', 'pending');

        // Staff see both selfies side by side in Verifications › Unfreeze; others can't decide
        $this->actingAs($staff)->get('/admin/verifications?tab=unfreeze')->assertOk()
            ->assertSee('Juma Otieno')->assertSee('Verification selfie')->assertSee('New selfie')
            ->assertSee(route('admin.verifications.unfreeze', $juma), false);
        $this->actingAs($staff)->get("/kyc-documents/{$juma->id}/unfreeze_selfie")->assertOk();
        $this->actingAs(User::factory()->create())->post(route('admin.verifications.unfreeze', $juma), ['decision' => 'approve'])->assertForbidden();
        $this->assertNotNull($juma->fresh()->frozen_at);

        // Reject: stays frozen, and the person sees why
        $this->actingAs($staff)->post(route('admin.verifications.unfreeze', $juma), ['decision' => 'reject'])->assertSessionHasErrors('reason');
        $this->actingAs($staff)->post(route('admin.verifications.unfreeze', $juma), ['decision' => 'reject', 'reason' => 'The face is not clear.'])->assertRedirect();
        $this->assertNotNull($juma->fresh()->frozen_at);
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('unfreeze.status', 'rejected')
            ->assertJsonPath('unfreeze.text', 'We could not unfreeze your account: The face is not clear.');

        // A new selfie, approved: unfrozen
        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '4826', 'selfie' => UploadedFile::fake()->image('again.jpg', 600, 800)], $this->h($token))->assertStatus(202);
        $this->actingAs($staff)->post(route('admin.verifications.unfreeze', $juma), ['decision' => 'approve'])->assertRedirect();
        $this->assertNull($juma->fresh()->frozen_at);
        $this->getJson('/api/mobile/v1/me', $this->h($token))->assertJsonPath('frozen', false)->assertJsonPath('unfreeze', null);
    }

    public function test_freezing_again_cancels_a_waiting_request(): void
    {
        [$juma, $token] = $this->frozenPerson();
        $this->postJson('/api/mobile/v1/me/unfreeze', ['pin' => '4826', 'selfie' => self::SELFIE], $this->h($token))->assertStatus(202);

        $this->postJson('/api/mobile/v1/me/freeze', [], $this->h($token))->assertOk();
        $this->assertNull($juma->fresh()->unfreeze_requested_at);
        $this->actingAs($this->staff())->post(route('admin.verifications.unfreeze', $juma), ['decision' => 'approve'])->assertNotFound();
        $this->assertNotNull($juma->fresh()->frozen_at);
    }
}
