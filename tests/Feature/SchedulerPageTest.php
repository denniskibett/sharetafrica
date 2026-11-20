<?php

namespace Tests\Feature;

use App\Console\Commands\SharetGoLive;
use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Administration › Scheduled jobs: cron status, the schedule, and "Run now" for a fixed list of commands. */
class SchedulerPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
        $this->admin = User::factory()->create(['name' => 'Dennis Kibett']);
        $this->admin->assignRole('super_admin');
    }

    public function test_only_system_staff_see_it_and_it_shows_the_cron_and_the_schedule(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Juma Jones']))->get('/admin/scheduler')->assertForbidden();

        @unlink(SharetGoLive::heartbeatPath());
        $this->actingAs($this->admin)->get('/admin/scheduler')->assertOk()->assertSee('The cron has never run')->assertSee('api:webhooks-retry')->assertSee('Run the scheduler now');

        file_put_contents(SharetGoLive::heartbeatPath(), (string) now()->subSeconds(30)->timestamp);
        $this->actingAs($this->admin)->get('/admin/scheduler')->assertSee('The cron is running');
        @unlink(SharetGoLive::heartbeatPath());
    }

    public function test_running_needs_a_fresh_password_and_only_listed_commands_run(): void
    {
        $this->actingAs($this->admin)->post('/admin/scheduler/run', ['command' => 'api:requests-expire'])->assertRedirect(route('password.confirm'));

        $ok = $this->actingAs($this->admin)->withSession(['auth.password_confirmed_at' => time()]);
        $ok->post('/admin/scheduler/run', ['command' => 'migrate:fresh'])->assertSessionHasErrors('command');
        $ok->post('/admin/scheduler/run', ['command' => 'api:requests-expire'])->assertRedirect(route('admin.scheduler.index'));
        $this->get('/admin/scheduler')->assertSee('Expire payment requests finished')->assertSee('0 payment requests expired.');

        $ok->post('/admin/scheduler/run', ['command' => 'sharet:golive']);
        $this->get('/admin/scheduler')->assertSee('Scheduler cron');
    }
}
