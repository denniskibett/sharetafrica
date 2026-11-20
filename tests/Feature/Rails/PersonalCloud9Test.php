<?php

namespace Tests\Feature\Rails;

use App\Ledger\Invariants;
use App\Ledger\Ledger;
use App\Models\Integration;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Personal with Cloud9 underneath: your own account number on Sharet's paybill, and card top-ups. Customers see only Sharet. */
class PersonalCloud9Test extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://sandbox-wallets.cloud9.money/api/v1/';

    private RampOrders $orders;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0, 'rails.collect_only_for' => []]);
        $this->seed(LedgerAccountSeeder::class);
        $this->orders = app(RampOrders::class);
        Integration::create(['company_id' => null, 'provider' => 'cloud9', 'enabled' => true, 'credentials' => [
            'environment' => 'sandbox', 'api_key' => 'tokenTestabc', 'payin_secret' => 'in-secret', 'payout_secret' => 'out-secret', 'wallet_id' => '7VXW9MJD',
        ]]);
    }

    private function person(): User
    {
        $u = User::factory()->create(['phone' => '0712345678', 'name' => 'Juma Otieno', 'onboarding_status' => 'active']);
        $u->forceFill(['kyc_level' => 'basic'])->save();

        return $u;
    }

    public function test_each_person_gets_their_own_account_number_and_money_paid_to_it_lands_in_their_wallet(): void
    {
        $juma = $this->person();
        $deposit = ['id' => 'DEPX1', 'status' => 'SUCCESS', 'provider' => 'MPESA-C2B', 'amount' => '1000.00', 'charge' => '15.00', 'taxes' => '0.00', 'net_amount' => '985.00',
            'phone_number' => '254722410556', 'narration' => 'MAMA WANJIKU', 'provider_reference' => 'NLJ7RT61SV', 'reference' => '482915',
            'paybill_account' => ['account_id' => '4KQ8ZP2M', 'account' => 482915, 'label' => 'Juma Otieno', 'reference' => $juma->sharet_id]];
        Http::fake([
            self::BASE.'wallets/7VXW9MJD/paybill_accounts/' => Http::response(['account_id' => '4KQ8ZP2M', 'paybill_number' => '123456', 'account' => 482915, 'wallet_id' => '7VXW9MJD']),
            self::BASE.'payin/deposits/DEPX1/' => Http::response($deposit),
        ]);

        $this->actingAs($juma)->getJson('/pay/account-number')->assertOk()->assertJson(['paybill' => '123456', 'account' => '482915', 'formatted' => '48 2915']);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), 'paybill_accounts/') && $r['label'] === 'Juma Otieno' && $r['reference'] === $juma->sharet_id);
        $this->getJson('/pay/account-number')->assertJson(['account' => '482915']);
        Http::assertSentCount(1);   // made once, then kept

        // Mama pays it from her M-PESA menu
        $this->postJson('/rails/cloud9/callback', ['event_type' => 'PAYIN'] + $deposit, ['X-Cloud9-Callback-Secret' => 'in-secret'])->assertOk();
        $this->postJson('/rails/cloud9/callback', ['event_type' => 'PAYIN'] + $deposit, ['X-Cloud9-Callback-Secret' => 'in-secret'])->assertOk();   // retried: once

        $order = RampOrder::where('user_id', $juma->id)->sole();
        $this->assertSame(['paid', 100000, 1500, 98500], [$order->status, $order->total, $order->fee, $order->amount]);
        $this->assertSame(98500, $this->orders->walletBalance($juma));
        $this->assertSame('MAMA WANJIKU', $order->destination['from']);
        $req = RailRequest::find($order->collect_request_id);
        $this->assertSame(['DEPX1', 'NLJ7RT61SV', 'M-PESA'], [$req->provider_reference, $req->receipt, $req->meta['network']]);
        $this->assertStringNotContainsStringIgnoringCase('cloud9', json_encode($order->timeline));
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));

        // Someone else's account number never credits Juma
        $other = ['id' => 'DEPX2', 'paybill_account' => ['account' => 999999]] + $deposit;
        $this->postJson('/rails/cloud9/callback', ['event_type' => 'PAYIN'] + $other, ['X-Cloud9-Callback-Secret' => 'in-secret'])->assertOk();
        $this->assertSame(1, RampOrder::count());
    }

    public function test_card_top_up_opens_the_card_page_and_the_customer_pays_the_card_charge_on_top(): void
    {
        $juma = $this->person();
        $deposit = ['id' => 'DEPC1', 'status' => 'SUCCESS', 'provider' => 'LOCAL-CARD', 'amount' => '2070.00', 'charge' => '70.00', 'taxes' => '0.00', 'net_amount' => '2000.00',
            'provider_reference' => 'CARD-778899', 'checkout_id' => 'K7PW2M9X'];
        Http::fake([
            self::BASE.'checkout/sessions/' => Http::response(['checkout_id' => 'K7PW2M9X', 'checkout_url' => 'https://sandbox-wallets.cloud9.money/checkout/abc/', 'status' => 'PENDING', 'charge' => '70.00', 'taxes' => '0.00']),
            self::BASE.'payin/deposits/DEPC1/' => Http::response($deposit),
        ]);

        $res = $this->actingAs($juma)->postJson('/pay/card', ['amount' => '2000'])->assertOk()->assertJsonPath('url', 'https://sandbox-wallets.cloud9.money/checkout/abc/');
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), 'checkout/sessions/') && $r['amount'] === '2000.00' && $r['charge_bearer'] === 'CUSTOMER'
            && $r['description'] === 'Sharet wallet top-up' && str_contains($r['redirect_url'], '/card-return'));
        $order = RampOrder::where('user_id', $juma->id)->sole();
        $this->assertSame(['collecting', 'cloud9'], [$order->status, $order->rail]);

        $this->postJson('/rails/cloud9/callback', ['event_type' => 'PAYIN'] + $deposit, ['X-Cloud9-Callback-Secret' => 'in-secret'])->assertOk();
        $order = $order->fresh();
        $this->assertSame(['paid', 200000, 0], [$order->status, $order->amount, $order->fee]);
        $this->assertSame(200000, $this->orders->walletBalance($juma));
        $this->assertStringContainsString('card charge 70.00 paid on your card', json_encode($order->timeline));
        $this->assertSame(200000, app(Ledger::class)->account('1160')->fresh()->natural());
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));

        $this->get(route('pay.card.return', $order))->assertRedirect(route('pay.show', $order));
    }

    public function test_receive_panel_offers_the_new_ways_only_once_cloud9_is_connected(): void
    {
        $juma = $this->person();
        $this->actingAs($juma)->get('/dashboard')->assertOk()->assertSee('My account number')->assertSee('Debit or credit card')->assertSee('Get my account number')->assertDontSee('Cloud9');

        Integration::where('provider', 'cloud9')->delete();
        $this->get('/dashboard')->assertOk()->assertDontSee('My account number')->assertDontSee('Debit or credit card');
    }
}
