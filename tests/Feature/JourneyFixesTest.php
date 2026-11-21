<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Breaks found by walking every user journey in a browser (journey test, 21 Nov 2026). */
class JourneyFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    private function person(string $role, array $extra = []): User
    {
        $user = User::factory()->create($extra + ['onboarding_status' => 'active', 'phone' => '+2547'.rand(10000000, 99999999)]);
        $user->assignRole($role);

        return $user;
    }

    public function test_business_settings_ask_for_the_id_check_when_the_number_is_already_there(): void
    {
        $user = $this->person('merchant');
        $this->actingAs($user)->getJson('/settings/business')->assertOk()
            ->assertJsonPath('owner.personal', false)->assertJsonPath('owner.phone', true);
        $this->putJson('/settings/business', ['name' => 'Duka'])->assertStatus(422)
            ->assertJsonPath('message', 'Verify your personal account first (ID photos and a selfie): only verified account holders can register a business.');

        $noPhone = $this->person('merchant', ['phone' => null]);
        $this->actingAs($noPhone)->putJson('/settings/business', ['name' => 'Duka'])->assertStatus(422)
            ->assertJsonPath('message', 'Add your M-Pesa number to your personal account first: only account holders can register a business.');
    }

    public function test_business_settings_only_link_to_pages_the_person_can_open(): void
    {
        $merchant = $this->person('merchant');
        $this->actingAs($merchant)->get('/settings/business')->assertOk()
            ->assertDontSee(route('integrations.index'), false)->assertDontSee(route('api-keys.index'), false);

        $developer = $this->person('techie');
        $this->actingAs($developer)->get('/settings/business')->assertOk()
            ->assertSee(route('api-keys.index'), false)->assertDontSee(route('integrations.index'), false);
    }

    public function test_the_account_menu_opens_requests_and_each_lanes_own_tools(): void
    {
        $individual = $this->person('individual');
        $html = $this->actingAs($individual)->get('/profile')->assertOk()->getContent();
        $this->assertStringContainsString('Request and split</a>', $html); // live since P05: no "Soon" badge
        $this->assertStringNotContainsString('API keys</a>', $html);
        $this->assertStringNotContainsString('Business settings</a>', $html);

        $developer = $this->person('techie');
        $this->actingAs($developer)->get('/profile')->assertOk()
            ->assertSee('API keys</a>', false)->assertSee('Developer docs</a>', false);

        $company = Company::create(['name' => 'Duka', 'country' => 'KE', 'status' => true]);
        $merchant = $this->person('merchant', ['company_id' => $company->id]);
        $this->actingAs($merchant)->get('/profile')->assertOk()->assertSee('Business settings</a>', false);
    }

    public function test_alpine_scopes_the_pages_rely_on(): void
    {
        // The money panel shows the payer's number before a quote exists; `payer` must be on the component
        $user = $this->person('individual');
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee("pinSet, payer, account:", false);

        // Form fields render on cards without an `errors` object (the verification review on a person's page)
        $field = file_get_contents(resource_path('views/components/ui/field.blade.php'));
        $this->assertStringContainsString("\$data.errors?.[", $field);

        // The receipt link never points at /undefined/receipt before a transaction is open
        $detail = file_get_contents(resource_path('views/pay/partials/detail.blade.php'));
        $this->assertStringContainsString("order?.url ? order.url + '/receipt' : null", $detail);
    }

    public function test_a_website_form_with_a_number_already_on_another_account_still_goes_through(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com', 'phone' => '+254717492048']);
        $trade = ['name' => 'Kiprono Bett', 'business' => 'Bett Imports', 'email' => 'kiprono@example.com', 'phone' => '0717492048',
            'trade_type' => 'importer', 'need' => 'payment_abroad', 'brief' => 'Tiles'];
        $this->from('/trade')->post('/trade/apply', $trade)->assertRedirect('/trade')->assertSessionHas('trade_received');
        $merchant = ['name' => 'Achieng', 'business' => 'Duka', 'email' => 'achieng@example.com', 'phone' => '0717492048', 'country' => 'KE', 'brief' => 'Shop'];
        $this->from('/merchants')->post('/merchants/signup', $merchant)->assertRedirect('/merchants');

        $this->assertNull(User::where('email', 'kiprono@example.com')->value('phone')); // the number stays with its account
        $this->assertSame('+254717492048', $owner->fresh()->phone);
        $this->assertSame(1, \App\Models\TradeApplication::count());
    }

    public function test_the_brand_tab_never_shows_a_broken_logo(): void
    {
        $this->seed(\Database\Seeders\SystemSeeder::class); // logo paths that were never uploaded
        $admin = $this->person('super_admin');
        $this->actingAs($admin)->getJson('/admin/system')->assertOk()
            ->assertJsonPath('files.logo', asset('images/logo/logo.svg'))
            ->assertJsonPath('files.logo_dark', asset('images/logo/logo-dark.svg'))
            ->assertJsonPath('files.logo_icon', asset('images/logo/logo-icon.svg'));
    }

    public function test_a_money_console_button_after_a_while_leads_back_through_the_password_screen(): void
    {
        $ops = $this->person('operations');
        $this->actingAs($ops)->from('/ops/reconciliation')->postJson('/ops/reconciliation/run')
            ->assertStatus(423)->assertJsonPath('error', 'staff_password_not_reconfirmed')
            ->assertSessionHas('url.intended', url('/ops/reconciliation'));
        $this->assertStringContainsString("body.error === 'staff_password_not_reconfirmed'", file_get_contents(resource_path('js/app.js')));
    }
}
