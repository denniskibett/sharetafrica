<?php

namespace Tests\Feature\Ramp;

use App\Ledger\Invariants;
use App\Ledger\Ledger;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Recon\Reconciler;
use App\Support\Handle;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** Add money, pay from the wallet, withdraw, and wallet-to-wallet transfers: every shilling accounted for. */
class WalletPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private RampOrders $orders;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        $this->seed(LedgerAccountSeeder::class);
        $this->orders = app(RampOrders::class);
    }

    private function person(string $phone, string $name = 'Jane Wanjiru'): User
    {
        $u = User::factory()->create(['phone' => $phone, 'name' => $name]);
        $u->forceFill(['kyc_level' => 'basic'])->save();

        return $u;
    }

    private function settle(RampOrder $o): RampOrder
    {
        while (! $o->final()) {
            $o = $this->orders->refresh($o);
        }

        return $o;
    }

    private function wallet(User $u): int
    {
        return $this->orders->walletBalance($u);
    }

    private function assertBooksClean(): void
    {
        $r = app(Invariants::class)->run();
        $this->assertTrue(Invariants::passed($r), json_encode($r, JSON_PRETTY_PRINT));
    }

    public function test_add_money_collects_into_the_wallet_free_and_reconciles(): void
    {
        $jane = $this->person('0712345678');
        $o = $this->settle($this->orders->confirm($this->orders->topUp($jane, '2,000')));

        $this->assertSame('paid', $o->status);
        $this->assertSame(0, $o->fee);
        $this->assertSame(200000, $this->wallet($jane));
        $this->assertSame(0, app(Reconciler::class)->run('simulator', now()->toDateString())->openBreaks());
        $this->assertBooksClean();
    }

    public function test_paying_from_the_wallet_skips_m_pesa_and_a_failed_payout_comes_back(): void
    {
        $jane = $this->person('0712345678');
        $this->settle($this->orders->confirm($this->orders->topUp($jane, '2,000')));

        $paid = $this->settle($this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722111222'], '985', null, 'wallet')));
        $this->assertSame('paid', $paid->status);
        $this->assertNull($paid->collect_request_id, 'no M-PESA prompt for a wallet payment');
        $this->assertSame(200000 - 100100, $this->wallet($jane));

        // the simulator fails numbers ending 0000: the money returns to the wallet, no rail refund
        $failed = $this->settle($this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722110000'], '100', null, 'wallet')));
        $this->assertSame('refunded', $failed->status);
        $this->assertSame(99900, $this->wallet($jane));

        $this->expectException(ValidationException::class); // more than the wallet holds
        $this->orders->quote($jane, 'phone', ['phone' => '0722111222'], '5,000', null, 'wallet');
    }

    public function test_withdraw_to_your_own_number_is_allowed_from_the_wallet_only(): void
    {
        $jane = $this->person('0712345678');
        $this->settle($this->orders->confirm($this->orders->topUp($jane, '1,000')));

        $w = $this->settle($this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0712345678'], '100', null, 'wallet')));
        $this->assertSame('paid', $w->status);
        $this->assertTrue($w->present($jane)['withdrawal']);
        $this->assertSame(90000, $this->wallet($jane), 'up to 100.00 is free');

        $this->expectException(ValidationException::class);
        $this->orders->quote($jane, 'phone', ['phone' => '0712345678'], '100'); // from M-PESA to the same M-PESA
    }

    public function test_wallet_to_wallet_by_handle_is_instant_and_free(): void
    {
        $jane = $this->person('0712345678');
        $juma = $this->person('0722333444', 'Juma Otieno');
        $this->settle($this->orders->confirm($this->orders->topUp($jane, '1,000')));

        $this->assertSame('juma.otieno', Handle::for($juma));
        $t = $this->orders->transfer($jane, '@Juma.Otieno', '400', 'Lunch');
        $this->assertSame('paid', $t->status);
        $this->assertSame(60000, $this->wallet($jane));
        $this->assertSame(40000, $this->wallet($juma));
        $this->assertSame('From @'.Handle::for($jane).' · Jane Wanjiru', $t->present($juma)['recipient']);
        $this->assertNull($t->present($juma)['payer'], 'the recipient never sees the sender\'s number');

        foreach (['@nobody' => 'handle', '@juma.otieno' => 'amount'] as $to => $field) {
            try {
                $this->orders->transfer($jane, $to, $to === '@nobody' ? '10' : '5,000');
                $this->fail("transfer to {$to} went through");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey($field, $e->errors());
            }
        }
        $this->assertBooksClean();
    }

    public function test_the_dashboard_panel_endpoints_work_in_place(): void
    {
        $jane = $this->person('0712345678');
        $juma = $this->person('0722333444', 'Juma Otieno');
        $this->actingAs($jane);

        $this->get('/dashboard')->assertOk()->assertSee('Available balance')->assertSee('@jane.wanjiru');
        $this->get('/wallet')->assertRedirect('/dashboard');
        $order = $this->postJson('/pay/top-up', ['amount' => '500'])->assertOk()->assertJsonPath('order.top_up', true)->json('order');
        $this->postJson("/pay/{$order['uuid']}/confirm")->assertOk();
        $this->settle(RampOrder::where('uuid', $order['uuid'])->first());

        $to = ['handle' => '@'.Handle::for($juma), 'amount' => '200'];
        $this->postJson('/pay/transfer', $to + ['pin' => '4826'])->assertStatus(422)->assertJsonValidationErrors('pin'); // no PIN yet
        $this->postJson('/pay/pin', ['pin' => '4826', 'pin_confirmation' => '4826'])->assertOk();
        $this->postJson('/pay/transfer', $to + ['pin' => '4826'])->assertOk()->assertJsonPath('order.status', 'paid');
        $this->getJson('/pay')->assertOk()->assertJsonPath('stats.wallet', '300.00');
        $this->actingAs($juma)->getJson('/pay')->assertOk()->assertJsonPath('data.0.incoming', true)->assertJsonPath('stats.wallet', '200.00');
        $this->assertSame(20000, app(Ledger::class)->account('2100', $juma)->available());
        $chart = $this->actingAs($jane)->getJson('/pay/chart?days=7')->assertOk()->json();
        $this->assertEquals([500, 200], [end($chart['series'][0]['data']), end($chart['series'][1]['data'])], 'today: 500 in, 200 out');
    }

    public function test_the_sharet_pin_guards_wallet_money_and_locks_after_five_wrong_tries(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class); // many tries in one minute
        $jane = $this->person('0712345678');
        $this->settle($this->orders->confirm($this->orders->topUp($jane, '1,000')));
        $this->actingAs($jane);

        $this->postJson('/pay/pin', ['pin' => '1234', 'pin_confirmation' => '1234'])->assertStatus(422)->assertJsonValidationErrors('pin'); // too easy
        $this->postJson('/pay/pin', ['pin' => '4826', 'pin_confirmation' => '4827'])->assertStatus(422)->assertJsonValidationErrors('pin_confirmation');
        $this->postJson('/pay/pin', ['pin' => '4826', 'pin_confirmation' => '4826'])->assertOk();
        $this->assertNotSame('4826', $jane->fresh()->transaction_pin, 'stored hashed');
        $this->postJson('/pay/pin', ['pin' => '9157', 'pin_confirmation' => '9157'])->assertStatus(422); // changing needs the current PIN: wrong try 1

        $quote = $this->postJson('/pay/quote', ['type' => 'phone', 'phone' => '0722111222', 'amount' => '100', 'funding' => 'wallet'])->assertOk()->json('order');
        foreach (range(2, 4) as $try) {
            $this->postJson("/pay/{$quote['uuid']}/confirm", ['pin' => '0001'])->assertStatus(422)->assertJsonPath('errors.pin.0', 'Wrong PIN. '.(5 - $try).' '.(5 - $try === 1 ? 'try' : 'tries').' left.');
        }
        $this->postJson("/pay/{$quote['uuid']}/confirm", ['pin' => '0001'])->assertStatus(422);
        $this->postJson("/pay/{$quote['uuid']}/confirm", ['pin' => '4826'])->assertStatus(422)->assertJsonValidationErrors('pin'); // locked, even with the right PIN
        $this->assertSame(100000, $this->wallet($jane), 'nothing left the wallet');

        $jane->forceFill(['pin_locked_until' => now()->subMinute()])->save();
        $this->postJson("/pay/{$quote['uuid']}/confirm", ['pin' => '4826'])->assertOk();

        // money from M-PESA is approved on the phone: no Sharet PIN asked
        $mpesa = $this->postJson('/pay/quote', ['type' => 'phone', 'phone' => '0722111222', 'amount' => '100'])->json('order');
        $this->postJson("/pay/{$mpesa['uuid']}/confirm")->assertOk();
    }

    public function test_receipts_download_as_pdf_and_payments_from_the_threshold_are_emailed(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        config(['pricing.receipt_email_min' => 100000]); // KES 1,000.00
        $jane = $this->person('0712345678');
        $juma = $this->person('0722333444', 'Juma Otieno');

        $small = $this->settle($this->orders->confirm($this->orders->topUp($jane, '500')));
        \Illuminate\Support\Facades\Mail::assertNothingSent(); // below the threshold

        $this->settle($this->orders->confirm($this->orders->topUp($jane, '2,000')));
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\SharetMail::class, fn ($m) => $m->hasTo($jane->email) && str_contains($m->subjectLine, 'You received KES 2,000.00'));

        $t = $this->orders->transfer($jane, '@'.Handle::for($juma), '1,500');
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\SharetMail::class, fn ($m) => $m->hasTo($juma->email) && str_contains($m->subjectLine, 'You received KES 1,500.00'));
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\SharetMail::class, fn ($m) => $m->hasTo($jane->email) && str_contains($m->subjectLine, 'You sent KES 1,500.00'));

        $lines = $t->receipt($jane);
        $this->assertSame('Juma Otieno (@juma.otieno)', $lines['Recipient']);
        $this->assertSame('@juma.otieno', $lines['Account ID']);
        $this->assertSame('Sharet wallet', $lines['Bank']);
        $this->assertArrayHasKey('Transaction ID', $lines);
        $this->assertArrayHasKey('Date', $lines);

        $this->actingAs($jane)->get("/pay/{$small->uuid}/receipt")->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($jane)->getJson("/pay/{$small->uuid}")->assertJsonPath('order.receipt.Bank', 'Sharet wallet');
        $this->actingAs(User::factory()->create())->get("/pay/{$small->uuid}/receipt")->assertNotFound();
    }
}
