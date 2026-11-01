<?php

namespace Tests\Feature\Rails;

use App\Integrations\Daraja\DarajaRail;
use App\Models\Integration;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Daraja collections (Add money) against a faked Safaricom: the prompt, the callback, the query, refusals. */
class DarajaRailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LedgerAccountSeeder::class);
        config(['rails.default' => 'simulator', 'rails.collect' => 'daraja']);
        Integration::create(['provider' => 'mpesa_sandbox', 'enabled' => true, 'credentials' => [
            'consumer_key' => 'key', 'consumer_secret' => 'secret', 'shortcode' => '174379', 'passkey' => 'pass',
        ]]);
    }

    private function jane(): User
    {
        $u = User::factory()->create(['phone' => '0712345678']);
        $u->forceFill(['kyc_level' => 'basic'])->save();

        return $u;
    }

    private function fakeSafaricom(array $query = ['ResultCode' => '0', 'ResultDesc' => 'ok']): void
    {
        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'tok', 'expires_in' => '3599']),
            '*/mpesa/stkpush/v1/processrequest' => Http::response(['ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_1', 'MerchantRequestID' => 'm1']),
            '*/mpesa/stkpushquery/v1/query' => Http::response($query),
        ]);
    }

    private function stkResult(string $code, int $amount = 500, string $receipt = 'TKA1B2C3D4'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/rails/daraja/callback/'.DarajaRail::callbackToken(), ['Body' => ['stkCallback' => [
            'MerchantRequestID' => 'm1', 'CheckoutRequestID' => 'ws_CO_1', 'ResultCode' => (int) $code, 'ResultDesc' => 'desc',
            'CallbackMetadata' => ['Item' => [['Name' => 'Amount', 'Value' => $amount], ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt], ['Name' => 'PhoneNumber', 'Value' => 254712345678]]],
        ]]]);
    }

    public function test_add_money_prompts_the_phone_and_the_callback_credits_the_wallet(): void
    {
        $this->fakeSafaricom();
        $orders = app(RampOrders::class);
        $o = $orders->confirm($orders->topUp($jane = $this->jane(), '500'));

        $this->assertSame('daraja', $o->rail);
        $this->assertSame('collecting', $o->status);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/mpesa/stkpush/v1/processrequest')
            && $r['Amount'] === 500 && $r['PhoneNumber'] === '254712345678' && $r['TransactionType'] === 'CustomerPayBillOnline'
            && str_contains($r['CallBackURL'], '/rails/daraja/callback/'));

        $this->stkResult('0')->assertOk()->assertJson(['ResultCode' => 0]);
        $this->stkResult('0')->assertOk(); // Safaricom repeats callbacks: posts once
        $this->assertSame('paid', $o->fresh()->status);
        $this->assertSame(50000, $orders->walletBalance($jane));
        $this->assertSame('TKA1B2C3D4', $o->fresh()->reference());
    }

    public function test_a_cancelled_prompt_fails_and_a_wrong_amount_is_left_for_reconciliation(): void
    {
        $this->fakeSafaricom();
        $orders = app(RampOrders::class);
        $o = $orders->confirm($orders->topUp($this->jane(), '500'));
        $this->stkResult('0', 499)->assertOk();
        $this->assertSame('collecting', $o->fresh()->status, 'a different amount is never booked');
        $this->stkResult('1032')->assertOk();
        $this->assertSame('failed', $o->fresh()->status);
        $this->assertStringContainsString('cancelled', $o->fresh()->failure);
    }

    public function test_a_status_query_settles_a_quiet_prompt_and_the_callback_needs_the_secret_path(): void
    {
        $this->fakeSafaricom(['ResultCode' => '1037', 'ResultDesc' => 'timeout']);
        $orders = app(RampOrders::class);
        $o = $orders->confirm($orders->topUp($this->jane(), '500'));
        $this->assertSame('failed', $orders->refresh($o)->status);

        $this->postJson('/rails/daraja/callback/wrong', [])->assertNotFound();
    }

    public function test_still_processing_waits_and_a_late_confirmation_is_booked_not_dropped(): void
    {
        $this->fakeSafaricom(['ResultCode' => '4999', 'ResultDesc' => 'The transaction is still under processing']);
        $orders = app(RampOrders::class);
        $o = $orders->confirm($orders->topUp($jane = $this->jane(), '500'));
        $this->assertSame('collecting', $orders->refresh($o)->status, 'still on the phone: keep waiting');

        // the 1 November case: an early answer had already marked it failed, then M-PESA confirms
        \App\Models\RailRequest::whereKey($o->collect_request_id)->update(['status' => 'failed', 'failure' => 'The transaction is still under processing']);
        RampOrder::whereKey($o->id)->update(['status' => 'failed']);
        $this->stkResult('0')->assertOk();
        $this->stkResult('0')->assertOk(); // repeated: still booked once

        $this->assertSame('paid', $o->fresh()->status);
        $this->assertSame(50000, $orders->walletBalance($jane));
        $this->assertSame('TKA1B2C3D4', $o->fresh()->reference());
        $this->assertCount(2, \App\Models\RailRequest::find($o->collect_request_id)->meta['callbacks'], 'every payload kept');
        $this->assertTrue(\App\Ledger\Invariants::passed(app(\App\Ledger\Invariants::class)->run()));
    }

    public function test_real_collection_can_be_limited_to_named_accounts(): void
    {
        $this->fakeSafaricom();
        config(['rails.collect_only_for' => ['tester@sharet.test']]);
        $orders = app(RampOrders::class);
        $this->assertSame('simulator', $orders->topUp($this->jane(), '500')->rail);
        $tester = User::factory()->create(['phone' => '0722333444', 'email' => 'Tester@sharet.test']);
        $tester->forceFill(['kyc_level' => 'basic'])->save();
        $this->assertSame('daraja', $orders->topUp($tester, '500')->rail);
    }

    public function test_payouts_are_refused_until_b2c_credentials_exist(): void
    {
        $this->assertSame('failed', app(DarajaRail::class)->payout(new \App\Models\RailRequest(['amount' => 100]))->status);
        $this->assertSame(0, RampOrder::count());
    }
}
