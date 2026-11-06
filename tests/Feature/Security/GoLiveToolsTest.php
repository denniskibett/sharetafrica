<?php

namespace Tests\Feature\Security;

use App\Ledger\Invariants;
use App\Models\RampOrder;
use App\Models\SupportCase;
use App\Models\User;
use App\Ramp\RampOrders;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoLiveToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        $this->seed(LedgerAccountSeeder::class);
    }

    public function test_golive_reports_blockers_and_fails_until_clear(): void
    {
        @unlink(\App\Console\Commands\SharetGoLive::heartbeatPath());
        $this->artisan('sharet:golive')->expectsOutputToContain('NOT READY')->assertExitCode(1);
    }

    public function test_reset_reverses_simulated_money_and_disables_the_tester_only_with_apply(): void
    {
        $orders = app(RampOrders::class);
        $tester = User::factory()->create(['email' => 'v2-tester@sharet.test', 'phone' => '0712345678']);
        $tester->forceFill(['kyc_level' => 'basic'])->save();
        $o = $orders->confirm($orders->topUp($tester, '500'));
        while (! $o->final()) {
            $o = $orders->refresh($o);
        }
        $this->assertSame(50000, $orders->walletBalance($tester));

        $this->artisan('sharet:reset-test-data')->expectsOutputToContain('Report only')->assertSuccessful();
        $this->assertSame(50000, $orders->walletBalance($tester));
        $this->assertFalse($tester->fresh()->trashed());

        $this->artisan('sharet:reset-test-data', ['--apply' => true])->assertSuccessful();
        $this->assertSame(0, $orders->walletBalance($tester));
        $this->assertTrue(User::withTrashed()->find($tester->id)->trashed());
        $r = app(Invariants::class)->run();
        $this->assertTrue(Invariants::passed($r), json_encode($r));

        // running it again changes nothing
        $this->artisan('sharet:reset-test-data', ['--apply' => true])->assertSuccessful();
        $this->assertSame(0, $orders->walletBalance($tester));
    }
}
