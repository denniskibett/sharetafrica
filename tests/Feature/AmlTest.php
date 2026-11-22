<?php

namespace Tests\Feature;

use App\Ledger\Invariants;
use App\Models\AmlCheck;
use App\Models\AmlWatch;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Support\Handle;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** AML screening before any money moves: allow, hold for a person, or block; Compliance decides; nothing moves until then. */
class AmlTest extends TestCase
{
    use RefreshDatabase;

    private RampOrders $orders;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
        $this->orders = app(RampOrders::class);
    }

    private function person(string $phone, string $name, string $balance = '0', string $level = 'standard'): User
    {
        $u = User::factory()->create(['phone' => $phone, 'name' => $name, 'created_at' => now()->subMonth()]);
        $u->forceFill(['kyc_level' => $level])->save();
        if ($balance !== '0') {
            $o = $this->orders->confirm($this->orders->topUp($u, $balance));
            while (! $o->final()) {
                $o = $this->orders->refresh($o);
            }
            RampOrder::whereKey($o->id)->update(['created_at' => now()->subDays(2)]);   // keep the top-up out of this hour's checks
        }

        return $u;
    }

    private function staff(string $name): User
    {
        $u = User::factory()->create(['name' => $name]);
        $u->assignRole('super_admin');

        return $u;
    }

    public function test_an_ordinary_payment_passes_and_the_watch_list_blocks_before_anything_moves(): void
    {
        $jane = $this->person('0712345601', 'Jane Wanjiru', '20000');
        $o = $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722333444'], '500', null, 'wallet'));
        $this->assertNotSame('held', $o->status);
        $this->assertSame(0, AmlCheck::count());

        AmlWatch::create(['type' => 'phone', 'value' => '254733000999', 'reason' => 'Reported fraud']);
        $q = $this->orders->quote($jane, 'phone', ['phone' => '0733 000 999'], '500', null, 'wallet');
        try {
            $this->orders->confirm($q);
            $this->fail('The watch list should block it');
        } catch (ValidationException $e) {
            $this->assertStringContainsString("can't be made", collect($e->errors())->flatten()->first());
        }
        $this->assertSame('rejected', $q->fresh()->status);
        $this->assertNull($q->fresh()->payout_request_id);   // never sent
        $this->assertSame(['block', 'closed', 'watch_list'], [AmlCheck::sole()->decision, AmlCheck::sole()->status, AmlCheck::sole()->hits[0]['code']]);
    }

    public function test_a_large_first_payment_is_held_and_released_or_stopped_by_someone_else(): void
    {
        $jane = $this->person('0712345602', 'Jane Wanjiru', '100000');
        $before = $this->orders->walletBalance($jane);
        $held = $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722333444'], '25000', null, 'wallet'));
        $this->assertSame('held', $held->status);
        $this->assertSame($before, $this->orders->walletBalance($jane));   // nothing moved
        $this->assertSame(0, RailRequest::count() - RailRequest::where('kind', 'collect')->count());   // no payout sent to any rail
        $this->assertSame('Being checked', $held->statusLabel()[0]);
        $check = AmlCheck::sole();
        $this->assertSame('new_payee_large', $check->hits[0]['code']);

        // The customer can't clear their own; a reviewer approves with a fresh password, then it is sent
        $checker = $this->staff('Amina Otieno');
        $this->actingAs($checker)->get('/admin/aml')->assertOk()->assertSee('A large first payment to someone new')->assertSee('Jane Wanjiru');
        $this->actingAs($checker)->post("/admin/aml/checks/{$check->id}/approve")->assertRedirect(route('password.confirm'));
        $this->actingAs($checker)->withSession(['auth.password_confirmed_at' => time()])->post("/admin/aml/checks/{$check->id}/approve", ['note' => 'Paying her landlord'])->assertRedirect();
        $o = $held->fresh();
        while (! $o->final()) {
            $o = $this->orders->refresh($o);
        }
        $this->assertSame('paid', $o->status);
        $this->assertSame(['approved', $checker->id], [$check->fresh()->status, $check->fresh()->reviewed_by]);

        // A second one, stopped: nothing leaves
        $held2 = $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722555666'], '25000', null, 'wallet'));
        $balance = $this->orders->walletBalance($jane);
        $check2 = AmlCheck::where('ramp_order_id', $held2->id)->sole();
        $this->actingAs($checker)->withSession(['auth.password_confirmed_at' => time()])->post("/admin/aml/checks/{$check2->id}/reject", ['note' => 'Customer says it is not them'])->assertRedirect();
        $this->assertSame(['rejected', $balance], [$held2->fresh()->status, $this->orders->walletBalance($jane)]);
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));

        // Nobody decides on their own payment
        $checker->forceFill(['kyc_level' => 'standard', 'phone' => '0712000999', 'created_at' => now()->subMonth()])->save();
        $held3 = $this->orders->confirm($this->orders->quote($checker->fresh(), 'phone', ['phone' => '0722777888'], '25000', null, 'mpesa'));
        $check3 = AmlCheck::where('ramp_order_id', $held3->id)->sole();
        $this->actingAs($checker)->withSession(['auth.password_confirmed_at' => time()])->post("/admin/aml/checks/{$check3->id}/approve")->assertForbidden();
    }

    public function test_too_many_payments_in_an_hour_are_held(): void
    {
        $jane = $this->person('0712345603', 'Jane Wanjiru', '50000');
        $akinyi = $this->person('0722410556', 'Akinyi Wanjiru');
        config(['aml.max_payments_per_hour' => 3]);
        foreach (range(1, 3) as $i) {
            $this->assertSame('paid', $this->orders->transfer($jane, '@'.Handle::for($akinyi), '100')->status);
        }
        $fourth = $this->orders->transfer($jane, '@'.Handle::for($akinyi), '100');
        $this->assertSame('held', $fourth->status);
        $this->assertSame(30000, $this->orders->walletBalance($akinyi));   // the held one did not move
        $this->assertSame('velocity_count', AmlCheck::sole()->hits[0]['code']);
    }

    public function test_a_held_transfer_that_pays_a_request_marks_it_paid_only_when_released(): void
    {
        $juma = $this->person('0712345604', 'Juma Otieno');
        $akinyi = $this->person('0722410557', 'Akinyi Wanjiru', '60000');
        \App\Support\TransactionPin::set($akinyi, '4826');
        $r = \App\Support\PersonalRequests::ask($juma, '@'.Handle::for($akinyi), '30000', 'Car repair', notify: false);   // KES 30,000 to someone new: held

        $this->actingAs($akinyi)->post("/r/{$r->token}/sharet", ['pin' => '4826'])->assertRedirect("/r/{$r->token}");
        $this->assertSame('open', $r->fresh()->status);
        $this->assertSame(0, $this->orders->walletBalance($juma));

        $checker = $this->staff('Amina Otieno');
        $check = AmlCheck::sole();
        $this->actingAs($checker)->withSession(['auth.password_confirmed_at' => time()])->post("/admin/aml/checks/{$check->id}/approve")->assertRedirect();
        $this->assertSame(['paid', 3000000], [$r->fresh()->status, $this->orders->walletBalance($juma)]);
    }
}
