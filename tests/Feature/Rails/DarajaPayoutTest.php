<?php

namespace Tests\Feature\Rails;

use App\Integrations\Daraja\DarajaRail;
use App\Ledger\Invariants;
use App\Ledger\Ledger;
use App\Models\Integration;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Models\SupportCase;
use App\Models\User;
use App\Ramp\RampOrders;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/** Daraja payouts (B2C / B2B) with the float held in escrow until M-PESA's result callback. Safaricom is faked. */
class DarajaPayoutTest extends TestCase
{
    use RefreshDatabase;

    private RampOrders $orders;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LedgerAccountSeeder::class);
        config(['rails.default' => 'simulator', 'rails.collect' => 'daraja', 'rails.payout' => 'daraja', 'rails.collect_only_for' => []]);
        Integration::create(['provider' => 'mpesa_sandbox', 'enabled' => true, 'credentials' => [
            'consumer_key' => 'key', 'consumer_secret' => 'secret', 'shortcode' => '600000', 'passkey' => 'pass',
            'initiator_name' => 'testapi', 'security_credential' => 'cred==',
        ]]);
        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'tok', 'expires_in' => '3599']),
            '*/mpesa/stkpush/v1/processrequest' => fn () => Http::response(['ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_'.\Illuminate\Support\Str::random(12)]),
            '*/mpesa/b2c/v3/paymentrequest' => fn () => Http::response(['ResponseCode' => '0', 'ConversationID' => 'AG_'.\Illuminate\Support\Str::random(12)]),
            '*/mpesa/b2b/v1/paymentrequest' => fn () => Http::response(['ResponseCode' => '0', 'ConversationID' => 'AG_B2B_'.\Illuminate\Support\Str::random(12)]),
        ]);
        $this->orders = app(RampOrders::class);
    }

    /** Jane with a real top-up of $kes in her wallet (lands in 1110), and $float moved to the utility account 1120. */
    private function jane(int $kes = 500, int $float = 300): User
    {
        $jane = User::factory()->create(['phone' => '0712345678']);
        $jane->forceFill(['kyc_level' => 'basic'])->save();
        $o = $this->orders->confirm($this->orders->topUp($jane, (string) $kes));
        $this->postJson('/rails/daraja/callback/'.DarajaRail::callbackToken(), ['Body' => ['stkCallback' => [
            'CheckoutRequestID' => RailRequest::find($o->collect_request_id)->provider_reference, 'ResultCode' => 0, 'ResultDesc' => 'ok',
            'CallbackMetadata' => ['Item' => [['Name' => 'Amount', 'Value' => $kes], ['Name' => 'MpesaReceiptNumber', 'Value' => 'TOPUP'.$kes]]],
        ]]])->assertOk();
        $this->assertSame('paid', $o->fresh()->status);
        if ($float) {
            $this->assertSame(0, Artisan::call('ledger:float', ['from' => '1110', 'to' => '1120', 'amount' => (string) $float]));
        }

        return $jane;
    }

    private function mpesaResult(RailRequest $req, int $code = 0, ?float $amount = null): void
    {
        $this->postJson('/rails/daraja/result/'.DarajaRail::callbackToken(), ['Result' => [
            'ResultType' => 0, 'ResultCode' => $code, 'ResultDesc' => $code ? 'The initiator information is invalid.' : 'ok',
            'OriginatorConversationID' => DarajaRail::originator($req), 'ConversationID' => $req->provider_reference, 'TransactionID' => 'UK2B2C0001',
            'ResultParameters' => ['ResultParameter' => [['Key' => 'TransactionAmount', 'Value' => $amount ?? $req->amount / 100], ['Key' => 'TransactionReceipt', 'Value' => 'UK2B2C0001']]],
        ]])->assertOk()->assertJson(['ResultCode' => 0]);
    }

    private function held(string $code): int
    {
        return app(Ledger::class)->account($code)->fresh()->held;
    }

    public function test_a_wallet_send_holds_the_float_pays_by_b2c_and_captures_on_the_result(): void
    {
        $jane = $this->jane();
        $o = $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722111222'], '100', null, 'wallet'));
        $req = RailRequest::find($o->payout_request_id);

        $this->assertSame('paying', $o->status);
        $this->assertSame('daraja', $req->rail);
        $this->assertSame(10000, $this->held('1120'), 'float held while in flight');
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/mpesa/b2c/v3/paymentrequest') && $r['CommandID'] === 'BusinessPayment'
            && $r['PartyB'] === '254722111222' && $r['Amount'] === 100 && $r['InitiatorName'] === 'testapi' && $r['OriginatorConversationID'] === DarajaRail::originator($req)
            && str_contains($r['ResultURL'], '/rails/daraja/result/'));

        $this->mpesaResult($req);
        $this->mpesaResult($req); // repeated: books once
        $this->assertSame('paid', $o->fresh()->status);
        $this->assertSame('UK2B2C0001', $o->fresh()->reference());
        $this->assertSame(0, $this->held('1120'), 'hold captured');
        $this->assertSame(20000, app(Ledger::class)->account('1120')->fresh()->natural());
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));
    }

    public function test_a_failed_payout_releases_the_float_and_returns_the_money_to_the_wallet(): void
    {
        $jane = $this->jane();
        $o = $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722111222'], '100', null, 'wallet'));
        $this->mpesaResult(RailRequest::find($o->payout_request_id), 2001);
        $this->assertSame('refunded', $o->fresh()->status);
        $this->assertSame(0, $this->held('1120'));
        $this->assertSame(50000, $this->orders->walletBalance($jane));
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));
    }

    public function test_not_enough_float_refuses_a_wallet_send_before_anything_is_taken(): void
    {
        $jane = $this->jane(500, 100);
        try {
            $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722111222'], '300', null, 'wallet'));
            $this->fail('sent without float');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('float is low', $e->errors()['amount'][0]);
        }
        $this->assertSame(50000, $this->orders->walletBalance($jane), 'nothing taken');
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/b2c/'));
    }

    public function test_a_collected_payment_waits_for_float_then_goes_out(): void
    {
        $jane = $this->jane(100, 0);
        $o = $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722111222'], '200'));
        $collect = RailRequest::find($o->collect_request_id);
        $this->postJson('/rails/daraja/callback/'.DarajaRail::callbackToken(), ['Body' => ['stkCallback' => [
            'CheckoutRequestID' => $collect->provider_reference, 'ResultCode' => 0, 'ResultDesc' => 'ok',
            'CallbackMetadata' => ['Item' => [['Name' => 'Amount', 'Value' => $o->total / 100], ['Name' => 'MpesaReceiptNumber', 'Value' => 'UKCOLLECT1']]],
        ]]])->assertOk();

        $o->refresh();
        $payout = RailRequest::find($o->payout_request_id);
        $this->assertSame('paying', $o->status);
        $this->assertTrue($payout->meta['awaiting_float']);
        $this->assertSame('FLOAT_LOW', SupportCase::where('ramp_order_id', $o->id)->sole()->reason);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/b2c/'));
        $this->assertSame('paying', $this->orders->refresh($o)->status, 'an unsent payout is never asked about');

        Artisan::call('ledger:float', ['from' => '1110', 'to' => '1120', 'amount' => '300']);
        $this->assertSame(1, $this->orders->sendAwaitingFloat());
        Http::assertSent(fn ($r) => str_contains($r->url(), '/b2c/') && $r['Amount'] === 200);
        $this->mpesaResult($payout->fresh());
        $this->assertSame('paid', $o->fresh()->status);
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));
    }

    public function test_paybills_tills_and_banks_go_by_b2b_and_a_timeout_keeps_waiting(): void
    {
        $jane = $this->jane(1000, 900);
        $this->orders->confirm($this->orders->quote($jane, 'paybill', ['paybill' => '888880', 'account' => '12345678'], '100', null, 'wallet'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/b2b/v1/paymentrequest') && $r['CommandID'] === 'BusinessPayBill' && $r['PartyB'] === '888880' && $r['AccountReference'] === '12345678');

        $o = $this->orders->confirm($this->orders->quote($jane, 'bank', ['bank' => 'equity', 'account' => '0123456789'], '100', null, 'wallet'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/b2b/v1/paymentrequest') && $r['PartyB'] === '247247' && $r['AccountReference'] === '0123456789');

        $req = RailRequest::find($o->payout_request_id);
        $this->postJson('/rails/daraja/timeout/'.DarajaRail::callbackToken(), ['ConversationID' => $req->provider_reference])->assertOk();
        $this->assertSame('paying', $o->fresh()->status, 'a queue timeout is not a failure');
        $this->assertCount(1, $req->fresh()->meta['timeouts']);
    }
}
