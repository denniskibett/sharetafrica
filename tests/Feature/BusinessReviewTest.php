<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BusinessReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function staff(): User
    {
        $u = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254700000009']);
        $u->assignRole(Role::findOrCreate('super_admin', 'web'));

        return $u;
    }

    protected function business(): array
    {
        $owner = User::factory()->create(['onboarding_status' => 'active']);
        $company = Company::create(['name' => 'Scharge', 'country' => 'KE', 'status' => true]);
        $owner->update(['company_id' => $company->id]);

        return [$owner, $company];
    }

    public function test_country_names_are_stored_as_two_letter_codes(): void
    {
        $this->assertSame('KE', Company::countryCode('Kenya'));
        $this->assertSame('UG', Company::countryCode('ug'));
        $this->assertSame('KE', Company::countryCode(null));
        $this->assertSame('Kenya', Company::countryName('KE'));
    }

    public function test_staff_approve_a_business_and_its_live_key_passes_the_verification_gate(): void
    {
        [, $company] = $this->business();
        $staff = $this->staff();

        $this->actingAs($staff)->get('/admin/businesses')->assertOk()->assertSee('Scharge');
        $this->actingAs($staff)->postJson("/admin/businesses/{$company->id}/decision", ['decision' => 'approve'])->assertOk();

        $company->refresh();
        $this->assertNotNull($company->verified_at);
        $this->assertSame($staff->id, $company->verified_by);
        $this->assertSame('business', $company->walletUser()->kyc_level);
        $this->actingAs($staff)->get('/admin/businesses?tab=approved')->assertSee('Scharge');
    }

    public function test_reject_needs_a_reason_revokes_and_the_owner_sees_it_then_resubmits(): void
    {
        [$owner, $company] = $this->business();
        $staff = $this->staff();
        $company->verify($staff);

        $this->actingAs($staff)->postJson("/admin/businesses/{$company->id}/decision", ['decision' => 'reject'])->assertStatus(422);
        $this->actingAs($staff)->postJson("/admin/businesses/{$company->id}/decision", ['decision' => 'reject', 'reason' => 'KRA PIN missing.'])->assertOk();
        $this->assertNull($company->fresh()->verified_at);
        $this->actingAs($staff)->get('/admin/businesses?tab=rejected')->assertSee('KRA PIN missing.');
        $this->actingAs($owner)->getJson('/settings/business')->assertJsonPath('review.status', 'rejected')->assertJsonPath('review.reason', 'KRA PIN missing.');
    }

    public function test_staff_cannot_review_their_own_business_and_others_cannot_review_at_all(): void
    {
        [$owner, $company] = $this->business();
        $staff = $this->staff();
        $staff->update(['company_id' => $company->id]);
        $this->actingAs($staff)->postJson("/admin/businesses/{$company->id}/decision", ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($owner)->postJson("/admin/businesses/{$company->id}/decision", ['decision' => 'approve'])->assertForbidden();
        $this->assertNull($company->fresh()->verified_at);
    }
}
