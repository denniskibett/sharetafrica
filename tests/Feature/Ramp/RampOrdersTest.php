<?php

namespace Tests\Feature\Ramp;

use App\Integrations\RailResult;
use App\Integrations\Rails;
use App\Ledger\Invariants;
use App\Ledger\Ledger;
use App\Models\LedgerJournal;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\Pricing;
use App\Ramp\RampOrders;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RampOrdersTest extends TestCase
{
    use RefreshDatabase;

    private RampOrders $orders;

    private Ledger $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        $this->seed(LedgerAccountSeeder::class);
        $this->orders = app(RampOrders::class);
        $this->ledger = app(Ledger::class);
    }

    private function payer(string $phone = '0712345678', string $level = 'basic'): User
    {
        $user = User::factory()->create(['phone' => $phone]);
        $user->forceFill(['kyc_level' => $level])->save();

        return $user;
    }

    private function natural(string $code, $owner = null): int
    {
        return $this->ledger->account($code, $owner)->fresh()->natural();
    }

    private function assertBooksClean(): void
    {
        $results = app(Invariants::class)->run();
        $this->assertTrue(Invariants::passed($results), json_encode($results, JSON_PRETTY_PRINT));
    }

    private function refreshUntilFinal(RampOrder $o, int $max = 6): RampOrder
    {
        for ($i = 0; $i < $max && ! $o->final(); $i++) {
            $o = $this->orders->refresh($o);
        }

        return $o;
    }

    public function test_pricing_adds_fee_excise_and_rounds_up_to_whole_shillings(): void
    {
        // up to 100.00 is free, as on M-PESA
        $this->assertSame(['fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => 10000], Pricing::quote(10000));
        // 985.00: Safaricom 13.00 + 20% markup 2.60 = fee 15.60, excise 15% of 2.60 = 0.39 → 1,000.99 → pay 1,001.00
        $this->assertSame(['fee' => 1560, 'excise' => 39, 'rounding' => 1, 'total' => 100100], Pricing::quote(98500));
        // 5,000.00: 57.00 + 11.40 = 68.40, excise 1.71 → 5,070.11 → 5,071.00
        $this->assertSame(['fee' => 6840, 'excise' => 171, 'rounding' => 89, 'total' => 507100], Pricing::quote(500000));
        // 50,000.00: 108.00 + 21.60 = 129.60, excise 3.24 → 50,132.84 → 50,133.00
        $this->assertSame(['fee' => 12960, 'excise' => 324, 'rounding' => 16, 'total' => 5013300], Pricing::quote(5000000));
    }

    public function test_a_payment_to_a_phone_is_collected_paid_and_booked_to_the_cent(): void
    {
        $order = $this->orders->quote($this->payer(), 'phone', ['phone' => '0722 111 222'], '985', 'Lunch');
        $this->assertSame('quoted', $order->status);
        $this->assertSame('254722111222', $order->destination['phone']);

        $order = $this->orders->confirm($order);
        $this->assertSame('collecting', $order->status);
        $this->assertSame(0, LedgerJournal::count(), 'nothing is booked before M-PESA confirms');

        $order = $this->orders->refresh($order);
        $this->assertSame('paying', $order->status);
        $this->assertSame(100100, $this->natural('2200', $order), 'collected money waits in the order account');

        $order = $this->orders->refresh($order);
        $this->assertSame('paid', $order->status);
        $this->assertSame(0, $this->natural('2200', $order));
        $this->assertSame(1560, $this->natural('4100'));
        $this->assertSame(39, $this->natural('2300'));
        $this->assertSame(1, $this->natural('4900'));
        $this->assertSame(1600, $this->natural('1130'), 'what stays at the PSP is exactly fee + excise + rounding');
        $this->assertCount(4, $order->timeline);
        $this->assertBooksClean();
    }

    public function test_every_destination_type_pays(): void
    {
        $payer = $this->payer();
        foreach ([
            ['paybill', ['paybill' => '888880', 'account' => 'METER 123'], 'Paybill 888880 · account METER 123'],
            ['till', ['till' => '524107'], 'Till 524107'],
            ['bank', ['bank' => 'equity', 'account' => '0180293844'], 'Equity Bank · 0180293844'],
        ] as [$type, $dest, $label]) {
            $o = $this->refreshUntilFinal($this->orders->confirm($this->orders->quote($payer, $type, $dest, '1,500')));
            $this->assertSame('paid', $o->status, $type);
            $this->assertSame($label, $o->recipient());
        }
        $this->assertBooksClean();
    }

    public function test_a_cancelled_m_pesa_prompt_ends_the_order_and_books_nothing(): void
    {
        $order = $this->orders->confirm($this->orders->quote($this->payer('0712340000'), 'phone', ['phone' => '0722111222'], '500'));
        $order = $this->orders->refresh($order);

        $this->assertSame('failed', $order->status);
        $this->assertStringContainsString('cancelled', $order->failure);
        $this->assertSame(0, LedgerJournal::count());
    }

    public function test_a_failed_payout_is_refunded_in_full_and_no_fee_is_kept(): void
    {
        $order = $this->orders->confirm($this->orders->quote($this->payer(), 'phone', ['phone' => '0722220000'], '2,000'));
        $order = $this->refreshUntilFinal($order);

        $this->assertSame('refunded', $order->status);
        $this->assertSame(0, $this->natural('2200', $order));
        $this->assertSame(0, $this->natural('1130'));
        $this->assertSame(0, $this->natural('4100'));
        $this->assertSame(['ramp_collect', 'ramp_refund'], LedgerJournal::orderBy('id')->pluck('type')->all());
        $this->assertBooksClean();
    }

    public function test_no_answer_means_status_checks_never_a_second_payout(): void
    {
        $order = $this->orders->confirm($this->orders->quote($this->payer(), 'phone', ['phone' => '0722229999'], '1,000'));
        $order = $this->orders->refresh($order);           // collected → paying
        $order = $this->orders->refresh($order);           // provider silent
        $order = $this->orders->refresh($order);           // still silent
        $this->assertSame('paying', $order->status);
        $this->assertSame('unknown', RailRequest::find($order->payout_request_id)->status);

        $order = $this->orders->refresh($order);           // answers: paid
        $this->assertSame('paid', $order->status);
        $this->assertSame(1, RailRequest::where('kind', 'payout')->count(), 'only one payout request was ever made');
        $this->assertBooksClean();
    }

    public function test_a_callback_and_a_status_check_arriving_together_post_once(): void
    {
        $order = $this->orders->refresh($this->orders->confirm($this->orders->quote($this->payer(), 'phone', ['phone' => '0722111222'], '700')));
        $payout = RailRequest::find($order->payout_request_id);
        $result = RailResult::succeeded('SABC123');

        $this->orders->apply($payout, $result);
        $this->orders->apply($payout, $result);
        $this->orders->apply($payout->fresh(), RailResult::failed('late contradictory callback'));

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1, LedgerJournal::where('type', 'ramp_payout')->count());
        $this->assertSame(0, RailRequest::where('kind', 'payout')->where('meta->role', 'refund')->count());
        $this->assertBooksClean();
    }

    public function test_confirming_twice_sends_one_prompt_and_old_quotes_expire(): void
    {
        $payer = $this->payer();
        $order = $this->orders->quote($payer, 'phone', ['phone' => '0722111222'], '300');
        $this->orders->confirm($order);
        $this->orders->confirm($order);
        $this->assertSame(1, RailRequest::where('kind', 'collect')->count());

        $old = $this->orders->quote($payer, 'phone', ['phone' => '0722111222'], '300');
        $this->travel(11)->minutes();
        try {
            $this->orders->confirm($old);
            $this->fail('An expired quote was confirmed.');
        } catch (ValidationException) {
        }
        $this->assertSame('expired', $old->fresh()->status);
    }

    public function test_bad_details_and_limits_are_refused_with_plain_messages(): void
    {
        $payer = $this->payer();
        $cases = [
            fn () => $this->orders->quote($payer, 'phone', ['phone' => '0712345678'], '100'),               // own number
            fn () => $this->orders->quote($payer, 'phone', ['phone' => '12345'], '100'),                    // not a phone
            fn () => $this->orders->quote($payer, 'phone', ['phone' => '0722111222'], '100.50'),            // cents
            fn () => $this->orders->quote($payer, 'phone', ['phone' => '0722111222'], '5'),                 // below minimum
            fn () => $this->orders->quote($payer, 'paybill', ['paybill' => '12', 'account' => 'x'], '100'), // bad paybill
            fn () => $this->orders->quote($payer, 'bank', ['bank' => 'nope', 'account' => '0123456'], '100'),
            fn () => $this->orders->quote($payer, 'phone', ['phone' => '0722111222'], '70,000'),            // over basic limit with fees
            fn () => $this->orders->quote($this->payer('0711000111', 'none'), 'phone', ['phone' => '0722111222'], '100'), // no KYC
            fn () => $this->orders->quote(User::factory()->create(['phone' => null])->forceFill(['kyc_level' => 'basic']), 'phone', ['phone' => '0722111222'], '100'),
        ];
        foreach ($cases as $i => $case) {
            try {
                $case();
                $this->fail("Case {$i} was accepted.");
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors(), "case {$i}");
            }
        }
        $this->assertSame(0, RampOrder::count());
    }

    public function test_the_resolver_moves_quiet_orders_and_expires_old_quotes(): void
    {
        $payer = $this->payer();
        $waiting = $this->orders->confirm($this->orders->quote($payer, 'phone', ['phone' => '0722111222'], '400'));
        $stale = $this->orders->quote($payer, 'phone', ['phone' => '0722111222'], '400');
        $this->travel(15)->minutes();

        $this->artisan('ramp:resolve')->assertSuccessful();
        $this->assertSame('paying', $waiting->fresh()->status);
        $this->assertSame('expired', $stale->fresh()->status);
    }

    public function test_the_simulator_cannot_run_in_production_unless_allowed(): void
    {
        $this->app['env'] = 'production';
        $this->expectException(\RuntimeException::class);
        app(Rails::class)->for('simulator');
    }

    public function test_the_pay_pages_quote_confirm_and_follow_without_reloads(): void
    {
        $payer = $this->payer();
        $this->actingAs($payer);

        $this->get('/pay')->assertOk()->assertSee('Transactions')->assertSee('Test mode');
        $quote = $this->postJson('/pay/quote', ['type' => 'phone', 'phone' => '0722 111 222', 'amount' => '985'])
            ->assertOk()->assertJsonPath('order.total', '1,001.00')->assertJsonPath('order.status', 'quoted')->json('order');
        $this->postJson('/pay/quote', ['type' => 'phone', 'phone' => '0722', 'amount' => '985'])->assertStatus(422)->assertJsonValidationErrors('phone');

        $this->postJson("/pay/{$quote['uuid']}/confirm")->assertOk()->assertJsonPath('order.status', 'collecting');
        $this->getJson("/pay/{$quote['uuid']}")->assertOk()->assertJsonPath('order.status', 'paying');
        $this->getJson("/pay/{$quote['uuid']}")->assertOk()->assertJsonPath('order.status', 'paid')->assertJsonPath('order.final', true);
        $this->get("/pay/{$quote['uuid']}")->assertOk()->assertSee('Payment');
        $this->getJson('/pay')->assertOk()->assertJsonPath('data.0.status', 'paid')->assertJsonPath('stats.paid', 1);

        $this->actingAs($this->payer('0733000111'))->getJson("/pay/{$quote['uuid']}")->assertNotFound();
    }
}
