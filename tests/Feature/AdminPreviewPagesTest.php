<?php

namespace Tests\Feature;

use App\Mail\SharetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Pages with a parameter that AuditCrawlTest skips: every email preview (light and dark) and a role's data. */
class AdminPreviewPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_email_preview_and_role_data_render_for_staff(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $admin = User::factory()->create(['onboarding_status' => 'active', 'phone' => '+254700000001']);
        $admin->assignRole(Role::findByName('super_admin', 'web'));
        $this->actingAs($admin);

        $keys = array_keys(SharetMail::previews());
        $this->assertNotEmpty($keys);
        foreach ($keys as $key) {
            $this->get("/admin/emails/$key")->assertOk()->assertDontSee('{{', false);
            $this->get("/admin/emails/$key?theme=dark")->assertOk();
        }
        $this->get('/admin/emails/no-such-email')->assertNotFound();

        foreach (Role::all() as $role) {
            $this->getJson("/admin/roles/{$role->id}/data")->assertOk()->assertJsonPath('role.name', $role->name);
        }
    }
}
