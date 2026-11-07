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

    public function test_keep_account_only_reverses_the_simulated_money(): void
    {
        $orders = app(RampOrders::class);
        $person = User::factory()->create(['email' => 'dennis@example.test', 'phone' => '0712345679']);
        $person->forceFill(['kyc_level' => 'basic'])->save();
        foreach (['100', '250'] as $amount) {
            $o = $orders->confirm($orders->topUp($person, $amount));
            while (! $o->final()) {
                $o = $orders->refresh($o);
            }
        }
        $this->assertSame(35000, $orders->walletBalance($person));

        $this->artisan('sharet:reset-test-data', ['--email' => 'dennis@example.test', '--keep-account' => true, '--apply' => true])
            ->expectsOutputToContain('Account kept')->assertSuccessful();
        $this->assertSame(0, $orders->walletBalance($person));
        $this->assertFalse($person->fresh()->trashed());
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));
        $this->assertSame(['reversed', 'reversed'], \App\Models\RampOrder::where('user_id', $person->id)->pluck('status')->all());
        $this->actingAs($person)->getJson('/pay')->assertOk()->assertJsonCount(0, 'data');   // gone from their transactions

        // Orders reversed before this marking existed are marked on a re-run
        \App\Models\RampOrder::where('user_id', $person->id)->update(['status' => 'paid']);
        $this->artisan('sharet:reset-test-data', ['--email' => 'dennis@example.test', '--keep-account' => true, '--apply' => true])
            ->expectsOutputToContain('marked 2 order(s) reversed')->assertSuccessful();
        $this->assertSame(0, $orders->walletBalance($person));
    }

    public function test_clean_simulated_keeps_only_real_money_for_everyone(): void
    {
        $orders = app(RampOrders::class);
        $settle = function ($o) use ($orders) {
            while (! $o->final()) {
                $o = $orders->refresh($o);
            }

            return $o;
        };
        $jane = User::factory()->create(['name' => 'Jane Doe', 'phone' => '0712345671']);
        $juma = User::factory()->create(['name' => 'Juma Otieno', 'phone' => '0712345672']);
        foreach ([$jane, $juma] as $u) {
            $u->forceFill(['kyc_level' => 'basic'])->save();
        }
        $settle($orders->confirm($orders->topUp($jane, '500')));
        $real = $settle($orders->confirm($orders->topUp($juma, '100')));
        \App\Models\RailRequest::whereKey($real->collect_request_id)->update(['rail' => 'daraja']);   // as if M-PESA had collected it
        $orders->transfer($jane, '@'.\App\Support\Handle::for($juma), '200');
        $this->assertSame([30000, 30000], [$orders->walletBalance($jane), $orders->walletBalance($juma)]);

        $this->artisan('sharet:clean-simulated')->expectsOutputToContain('Report only')->assertSuccessful();
        $this->assertSame(30000, $orders->walletBalance($jane));

        // Without --transfers Jane's top-up can't be undone (she passed 200 on): skipped, nothing half-done
        $this->artisan('sharet:clean-simulated', ['--apply' => true])->expectsOutputToContain('Skipped')->assertFailed();
        $this->assertSame([30000, 30000], [$orders->walletBalance($jane), $orders->walletBalance($juma)]);

        $this->artisan('sharet:clean-simulated', ['--apply' => true, '--transfers' => true])->expectsOutputToContain('Reversed 2 order(s)')->assertSuccessful();
        $this->assertSame([0, 10000], [$orders->walletBalance($jane), $orders->walletBalance($juma)]);   // only Juma's real 100 is left
        $this->assertSame(1, RampOrder::where('status', '!=', 'reversed')->count());
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));
    }
}
