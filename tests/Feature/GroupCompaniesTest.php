<?php

namespace Tests\Feature;

use App\Models\GroupCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroupCompaniesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_companies_show_on_company_page_and_have_their_own_pages(): void
    {
        $this->assertSame(7, GroupCompany::count());
        $this->get('/company')->assertOk()->assertSee('Seven businesses.', false)->assertSee(route('companies.show', 'shoope'));
        $this->get('/companies/scharge')->assertOk()->assertSee('Property management for Africa')->assertSee('A Sharet company');
        $this->get('/companies/nope')->assertNotFound();
        $this->get('/sitemap.xml')->assertSee('/companies/breve');
    }

    public function test_admin_can_add_edit_and_remove_a_company(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('super_admin', 'web'));
        $this->actingAs($admin);

        $this->get('/admin/companies')->assertOk();
        $this->postJson('/admin/companies', ['name' => 'Test Co', 'tagline' => 'Testing.', 'services' => "One\nTwo\n", 'published' => 1])->assertOk();
        $c = GroupCompany::where('slug', 'test-co')->firstOrFail();
        $this->assertSame(['One', 'Two'], $c->services);

        $this->postJson("/admin/companies/{$c->id}", ['name' => 'Test Co', 'tagline' => 'Changed.', 'published' => 0])->assertOk();
        $this->get('/companies/test-co')->assertNotFound(); // hidden

        $this->deleteJson("/admin/companies/{$c->id}")->assertOk();
        $this->assertNull($c->fresh());
    }
}
