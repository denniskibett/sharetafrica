<?php

namespace Tests\Feature\Support;

use App\Models\RampOrder;
use App\Models\SupportCase;
use App\Models\User;
use App\Ramp\RampOrders;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** The support desk cases queue: who sees it, take next, assign, close with a note, SLA. */
class SupportDeskTest extends TestCase
{
    use RefreshDatabase;

    private function agent(): User
    {
        $u = User::factory()->create(['onboarding_status' => 'active']);
        $u->assignRole(Role::findOrCreate('support', 'web'));

        return $u;
    }

    private function aCase(): SupportCase
    {
        $this->seed(LedgerAccountSeeder::class);
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        $jane = User::factory()->create(['phone' => '0712345678']);
        $jane->forceFill(['kyc_level' => 'basic'])->save();
        $orders = app(RampOrders::class);
        $o = $orders->confirm($orders->topUp($jane, '10'));

        return SupportCase::raise($o, 'LATE_CONFIRMATION', 'M-PESA confirmed KES 10.00 late.');
    }

    public function test_only_staff_desks_see_the_queue(): void
    {
        $this->actingAs(User::factory()->create())->get('/ops/support')->assertForbidden();
        $this->actingAs($this->agent())->get('/ops/support')->assertOk()->assertSee('Support desk');
    }

    public function test_take_next_assign_and_close_with_a_note(): void
    {
        $case = $this->aCase();
        $this->assertSame('CS-'.(10000 + $case->id), $case->number);
        $this->assertSame(1, SupportCase::where('ramp_order_id', $case->ramp_order_id)->count());
        SupportCase::raise(RampOrder::find($case->ramp_order_id), 'LATE_CONFIRMATION', 'again');
        $this->assertSame(1, SupportCase::count(), 'one case per payment and reason');

        $agent = $this->agent();
        $this->actingAs($agent)->getJson('/ops/support')->assertOk()
            ->assertJsonPath('stats.open', 1)->assertJsonPath('stats.unassigned', 1)->assertJsonPath('data.0.reason', 'LATE_CONFIRMATION');

        $this->postJson('/ops/support/next')->assertOk()->assertJsonPath('case.owner', $agent->name)->assertJsonPath('case.pill.label', 'Open');
        $this->postJson('/ops/support/next')->assertOk()->assertJsonPath('case', null);

        $this->postJson("/ops/support/{$case->id}/close", [])->assertStatus(422)->assertJsonValidationErrors('note');
        $this->postJson("/ops/support/{$case->id}/close", ['note' => 'Called her: KES 10.00 is in the wallet.'])->assertOk()->assertJsonPath('case.is_open', false);
        $this->getJson('/ops/support')->assertJsonPath('stats.open', 0);
        $this->getJson('/ops/support?status=closed')->assertJsonPath('data.0.close_note', 'Called her: KES 10.00 is in the wallet.');
    }

    public function test_sla_shows_time_left_then_overdue(): void
    {
        $case = $this->aCase();
        $this->assertMatchesRegularExpression('/^00:(29|30)$/', $case->slaLeft());
        $this->travel(45)->minutes();
        $this->assertStringEndsWith(' OVER', $case->fresh()->slaLeft());
        $this->actingAs($this->agent())->getJson('/ops/support')->assertJsonPath('stats.breached', 1);
    }
}
