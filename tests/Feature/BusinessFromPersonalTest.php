<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Support\Kyc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** A personal account holder starts a business from their own account, uploads its documents, and as a merchant gets API keys. */
class BusinessFromPersonalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        Storage::fake('local');
    }

    private function verified(string $role = 'individual'): User
    {
        $user = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+2547'.rand(10000000, 99999999)]);
        $user->assignRole($role);
        Kyc::save($user, ['documents' => array_fill_keys(Kyc::VERIFY, ['path' => 'kyc/x.jpg'])]);
        Kyc::decide($user, User::factory()->create(), 'approve', null);

        return $user->fresh();
    }

    public function test_the_personal_account_offers_to_start_a_business(): void
    {
        $user = $this->verified();
        $this->actingAs($user)->get('/profile')->assertOk()
            ->assertSee('Start a business')->assertSee(route('settings.company'), false);
    }

    public function test_the_first_save_creates_the_business_and_makes_the_holder_a_merchant(): void
    {
        $user = $this->verified();
        $this->actingAs($user)->get('/api-keys')->assertForbidden();

        $this->putJson('/settings/business', ['name' => 'Wanjiku Crafts', 'type' => 'sole_proprietor'])->assertOk();
        $user = $user->fresh();
        $this->assertSame('Wanjiku Crafts', $user->company->name);
        $this->assertTrue($user->hasRole('merchant'));
        $this->assertTrue($user->hasRole('individual'));   // the personal account stays
        $wallet = $user->company->walletUser();   // the business has its own account, apart from the person's
        $this->assertTrue($wallet->is_business_wallet);
        $this->assertNotSame($user->id, $wallet->id);
        $this->assertNotSame($user->sharet_id, $wallet->sharet_id);
        $this->getJson('/settings/business')->assertJsonPath('account.sharet_id', $wallet->sharet_id);

        $this->actingAs($user)->get('/api-keys')->assertOk();
        $this->get('/settlements')->assertOk();
        $this->get('/profile')->assertSee('Business settings');
    }

    public function test_merchants_open_api_keys_and_approvals_but_live_money_still_needs_a_verified_business(): void
    {
        $merchant = $this->verified('merchant');
        $this->actingAs($merchant)->get('/api-keys')->assertOk();
        $this->get('/settlements')->assertOk();

        $this->actingAs($this->verified('individual'))->get('/api-keys')->assertForbidden();
    }

    public function test_documents_upload_privately_count_towards_the_profile_and_open_for_staff(): void
    {
        $owner = $this->verified();
        $this->actingAs($owner)->postJson('/settings/business/documents/certificate', ['file' => UploadedFile::fake()->create('cert.pdf', 120, 'application/pdf')])
            ->assertStatus(422);   // no business yet

        $this->putJson('/settings/business', ['name' => 'Duka', 'type' => 'limited_company'])->assertOk();
        $this->postJson('/settings/business/documents/certificate', ['file' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain')])
            ->assertStatus(422)->assertJsonValidationErrors('file');

        foreach (['certificate', 'kra_pin', 'bank_letter'] as $type) {
            $this->postJson("/settings/business/documents/$type", ['file' => UploadedFile::fake()->create("$type.pdf", 200, 'application/pdf')])->assertOk();
        }
        $docs = collect($this->getJson('/settings/business')->assertOk()->json('documents'))->keyBy('type');
        $this->assertTrue($docs['certificate']['uploaded']);
        $this->assertTrue($docs['cr12']['required']);   // limited company
        $this->assertFalse($docs['cr12']['uploaded']);
        $this->assertFalse($docs['permit']['required']);

        $company = $owner->fresh()->company;
        $before = \App\Http\Controllers\CompanySettingsController::progressOf($company)['done'];
        $this->postJson('/settings/business/documents/cr12', ['file' => UploadedFile::fake()->image('cr12.jpg', 1200, 900)])->assertOk();
        $this->assertSame($before + 1, \App\Http\Controllers\CompanySettingsController::progressOf($company->fresh())['done']);

        $path = decrypt($company->fresh()->settings['kyb_documents'])['certificate']['path'];
        Storage::disk('local')->assertExists($path);
        $this->assertStringStartsWith('kyb/'.$company->id.'/', $path);   // private disk, never public/

        $this->get('/settings/business/documents/certificate')->assertOk();
        $this->actingAs($this->verified())->get('/settings/business/documents/certificate')->assertNotFound();   // someone else

        $staff = User::factory()->create(); $staff->assignRole('super_admin');
        $this->actingAs($staff)->get(route('admin.businesses.document', [$company, 'certificate']))->assertOk();
        $this->get('/admin/businesses')->assertOk()->assertSee('Certificate of registration')->assertSee(route('admin.businesses.document', [$company, 'kra_pin']), false);
    }
}
