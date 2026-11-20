<?php

namespace Tests\Feature\Recon;

use App\Integrations\Simulator\SimulatorRail;
use App\Ledger\Invariants;
use App\Ledger\Ledger;
use App\Models\RampOrder;
use App\Models\StatementLine;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Recon\Reconciler;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private RampOrders $orders;

    private Reconciler $recon;

    private string $today;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        $this->seed(LedgerAccountSeeder::class);
        $this->orders = app(RampOrders::class);
        $this->recon = app(Reconciler::class);
        $this->today = now()->toDateString();
    }

    private function payer(string $phone = '0712345678'): User
    {
        $u = User::factory()->create(['phone' => $phone]);
        $u->forceFill(['kyc_level' => 'basic'])->save();

        return $u;
    }

    private function paidOrder(string $to = '0722111222'): RampOrder
    {
        $o = $this->orders->confirm($this->orders->quote($this->payer(), 'phone', ['phone' => $to], '1,000'));
        while (! $o->final()) {
            $o = $this->orders->refresh($o);
        }

        return $o;
    }

    private function staff(): User
    {
        $u = User::factory()->create();
        $u->givePermissionTo(Permission::findOrCreate('admin.reconciliation', 'web'));

        return $u;
    }

    private function assertBooksClean(): void
    {
        $r = app(Invariants::class)->run();
        $this->assertTrue(Invariants::passed($r), json_encode($r, JSON_PRETTY_PRINT));
    }

    public function test_a_clean_day_matches_and_needs_two_different_people_to_sign_off(): void
    {
        $this->paidOrder();
        $day = $this->recon->run('simulator', $this->today)->present();
        $this->assertSame(2, $day['lines']);
        $this->assertSame(2, $day['matched']);
        $this->assertSame(0, $day['breaks']);

        $maker = $this->staff();
        $checker = $this->staff();
        $d = $this->recon->prepare(\App\Models\ReconDay::first(), $maker);
        try {
            $this->recon->signOff($d, $maker);
            $this->fail('The maker signed off their own day.');
        } catch (ValidationException) {
        }
        $this->assertSame('signed_off', $this->recon->signOff($d, $checker)->status);

        // running again changes nothing
        $this->assertSame(2, $this->recon->run('simulator', $this->today)->present()['lines']);
    }

    public function test_a_missed_callback_is_found_on_the_statement_and_booked_from_it(): void
    {
        // the payer paid, but we never heard back: no refresh, no callback
        $order = $this->orders->confirm($this->orders->quote($this->payer(), 'phone', ['phone' => '0722111222'], '1,000'));
        $this->assertSame('collecting', $order->status);

        $this->recon->run('simulator', $this->today);
        $missed = StatementLine::where('status', 'missed')->firstOrFail();
        $this->assertSame('in', $missed->direction);

        $maker = $this->staff();
        try {
            $this->recon->prepare(\App\Models\ReconDay::first(), $maker);
            $this->fail('A day with breaks was prepared.');
        } catch (ValidationException) {
        }

        $this->assertSame('fixed', $this->recon->fixFromStatement($missed, $maker)->status);
        $this->assertSame('paying', $order->fresh()->status, 'the order moved on as if the callback had arrived');

        // the payout we then sent is also on today's statement; its callback was missed too
        $this->recon->run('simulator', $this->today);
        $payout = StatementLine::where('status', 'missed')->where('direction', 'out')->firstOrFail();
        $this->recon->fixFromStatement($payout, $maker);
        $this->assertSame('paid', $order->fresh()->status);

        $this->assertSame(0, $this->recon->run('simulator', $this->today)->openBreaks());
        $this->assertBooksClean();
    }

    public function test_unidentified_money_is_parked_in_suspense_so_the_books_equal_reality(): void
    {
        $this->paidOrder();
        SimulatorRail::addStatementLine($this->today, 'in', 50000, '254711000222');
        $this->recon->run('simulator', $this->today);

        $line = StatementLine::where('status', 'unknown')->firstOrFail();
        $parked = $this->recon->park($line, $this->staff());
        $this->assertSame('parked', $parked->status);
        $this->assertSame(50000, app(Ledger::class)->account('2900')->fresh()->natural());

        $this->assertSame('prepared', $this->recon->prepare(\App\Models\ReconDay::first(), $this->staff())->status);
        $this->assertBooksClean();
    }

    public function test_cancelled_prompts_never_reach_the_statement(): void
    {
        $o = $this->orders->confirm($this->orders->quote($this->payer('0712340000'), 'phone', ['phone' => '0722111222'], '500'));
        $this->assertSame('failed', $this->orders->refresh($o)->status);

        $day = $this->recon->run('simulator', $this->today)->present();
        $this->assertSame(0, $day['lines']);
        $this->assertSame(0, $day['breaks']);
    }

    public function test_the_screens_run_fix_and_sign_off_in_place(): void
    {
        $this->orders->confirm($this->orders->quote($this->payer(), 'phone', ['phone' => '0722111222'], '700'));
        $maker = $this->staff();
        // Money consoles ask for the password again every 15 minutes (SystemGuard)
        $this->actingAs($maker)->withSession(['auth.password_confirmed_at' => time()]);

        $this->get('/ops/reconciliation')->assertOk()->assertSee('Reconciliation');
        $day = $this->postJson('/ops/reconciliation/run', ['day' => $this->today])->assertOk()->json('day');
        $this->assertSame(1, $day['breaks']);
        $this->getJson('/ops/reconciliation')->assertOk()->assertJsonPath('stats.open_breaks', 1);

        $this->get("/ops/reconciliation/{$day['id']}")->assertOk();
        $line = $this->getJson("/ops/reconciliation/{$day['id']}?status=open")->assertOk()->json('data.0');
        $this->postJson("/ops/reconciliation/lines/{$line['id']}/fix")->assertOk()->assertJsonPath('line.status', 'fixed');
        $this->postJson("/ops/reconciliation/{$day['id']}/prepare")->assertStatus(422); // the payout line is now a break too

        $this->postJson('/ops/reconciliation/run', ['day' => $this->today])->assertOk();
        $payout = $this->getJson("/ops/reconciliation/{$day['id']}?status=open")->json('data.0');
        $this->postJson("/ops/reconciliation/lines/{$payout['id']}/fix")->assertOk();
        $this->postJson("/ops/reconciliation/{$day['id']}/prepare")->assertOk();
        $this->postJson("/ops/reconciliation/{$day['id']}/sign-off")->assertStatus(422)->assertJsonValidationErrors('day');

        $this->actingAs($this->staff())->postJson("/ops/reconciliation/{$day['id']}/sign-off")->assertOk()->assertJsonPath('day.status', 'signed_off');
        $this->actingAs(User::factory()->create())->get('/ops/reconciliation')->assertForbidden();
    }
}
