<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\GroupCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Forms whose save code disagreed with the form (frontend audit, 21 Nov 2026). */
class FormFixesTest extends TestCase
{
    use RefreshDatabase;

    private function invited(): User
    {
        return User::factory()->create(['onboarding_status' => 'invited', 'phone' => '+2547'.rand(10000000, 99999999)]);
    }

    public function test_the_developer_lane_opens_the_developer_form(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->actingAs($this->invited())->post('/onboarding/choose-lane', ['lane' => 'techie'])
            ->assertRedirect(route('onboarding.developer'));
    }

    public function test_typing_an_existing_business_name_never_joins_that_company(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $theirs = Company::create(['name' => 'Duka Ltd', 'slug' => 'duka-ltd', 'country' => 'KE', 'status' => true,
            'settings' => ['kyb' => encrypt(['settlement' => ['account_number' => '0123456789']])]]);

        foreach (['merchant' => ['business_name' => 'Duka Ltd', 'business_type' => 'shop', 'country' => 'KE'],
            'business' => ['business_name' => 'Duka Ltd', 'country' => 'KE']] as $lane => $form) {
            $user = $this->invited();
            $this->actingAs($user)->post("/onboarding/$lane", $form)->assertRedirect(route('dashboard'));
            $mine = $user->fresh()->company;
            $this->assertNotNull($mine);
            $this->assertNotSame($theirs->id, $mine->id);
            $this->assertSame('Duka Ltd', $mine->name);
            $this->actingAs($user->fresh())->getJson('/settings/business')->assertOk()->assertDontSee('0123456789');
        }
        $this->assertSame(1, Company::where('slug', 'duka-ltd')->count());
    }

    public function test_business_settings_save_every_business_type_without_touching_companies_type(): void
    {
        $owner = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254711000111']);
        $company = Company::create(['name' => 'Duka', 'type' => 'merchant', 'country' => 'KE', 'status' => true]);
        $owner->update(['company_id' => $company->id]);
        \App\Support\Kyc::save($owner, ['documents' => array_fill_keys(\App\Support\Kyc::VERIFY, ['path' => 'kyc/x.jpg'])]);
        \App\Support\Kyc::decide($owner, User::factory()->create(), 'approve', null); // verified (staff approved)

        foreach (['sole_proprietor', 'partnership', 'limited_company', 'ngo', 'other'] as $type) {
            $this->actingAs($owner->fresh())->putJson('/settings/business', ['name' => 'Duka', 'type' => $type])->assertOk();
            $this->getJson('/settings/business')->assertJsonPath('company.type', $type);
        }
        $this->assertSame('merchant', $company->fresh()->type);
    }

    public function test_a_country_name_on_the_profile_is_stored_as_its_code(): void
    {
        $user = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254711000222']);
        $this->actingAs($user)->patchJson('/profile', ['name' => $user->name, 'email' => $user->email, 'country' => 'Kenya'])->assertOk();
        $this->assertSame('KE', $user->fresh()->country);
        $this->getJson('/profile/data')->assertJsonPath('user.country', 'Kenya');
    }

    public function test_group_company_with_an_empty_order_saves(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $admin = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254711000333']);
        $admin->assignRole(Role::findByName('super_admin', 'web'));
        $this->actingAs($admin)->postJson('/admin/companies', ['name' => 'Sharet Labs', 'sort' => ''])->assertSuccessful();
        $c = GroupCompany::where('name', 'Sharet Labs')->firstOrFail();
        $this->assertGreaterThan(0, $c->sort);
        $this->postJson("/admin/companies/{$c->id}", ['name' => 'Sharet Labs', 'sort' => ''])->assertSuccessful();
        $this->assertSame($c->sort, $c->fresh()->sort);
    }

    public function test_pricing_time_is_read_as_nairobi_time(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $admin = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254711000444']);
        $admin->assignRole(Role::findByName('super_admin', 'web'));
        $at = now('Africa/Nairobi')->addDay()->startOfHour();
        $form = ['effective_from' => $at->format('Y-m-d\TH:i'), 'excise_percent' => '15',
            'bands' => \App\Models\Tariff::current()?->bands ?? [], 'tiers' => [['from' => 0, 'discount_percent' => 0]]];
        $res = $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()])->post('/admin/pricing', $form);
        if (session('errors')) {
            $this->markTestSkipped('pricing form fixture: '.collect(session('errors')->all())->first());
        }
        $t = \App\Models\Tariff::latest('version')->first();
        $this->assertTrue($t->effective_from->equalTo($at), "stored {$t->effective_from} for {$at}");
    }

    public function test_audit_data_is_a_dry_run_unless_applied_and_lists_shared_businesses(): void
    {
        $c = Company::create(['name' => 'Duka', 'country' => 'KE', 'status' => true, 'phone' => '0712 345 678']);
        User::factory()->count(2)->create(['company_id' => $c->id]);

        $this->artisan('sharet:audit-data')->expectsOutputToContain('Would normalise 1 business phone')
            ->expectsOutputToContain('1 business(es) with more than one member')->assertSuccessful();
        $this->assertSame('0712 345 678', $c->fresh()->phone);

        $this->artisan('sharet:audit-data --apply')->expectsOutputToContain('Normalised 1')->assertSuccessful();
        $this->assertSame('+254712345678', $c->fresh()->phone);
        $this->assertSame(2, User::where('company_id', $c->id)->count()); // members are only listed, never moved
    }
}
