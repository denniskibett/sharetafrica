<?php

namespace Tests\Feature\Rails;

use App\Ledger\Invariants;
use App\Ledger\Ledger;
use App\Models\Integration;
use App\Models\RailRequest;
use App\Models\RailRoute;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Cloud9 as a rail behind Sharet: money in, money out, its charges on the ledger, webhooks, the switch and the fallback. */
class Cloud9RailTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://sandbox-wallets.cloud9.money/api/v1/';

    private RampOrders $orders;

    private Ledger $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0, 'rails.collect_only_for' => []]);
        $this->seed([LedgerAccountSeeder::class]);
        $this->orders = app(RampOrders::class);
        $this->ledger = app(Ledger::class);
        Integration::create(['company_id' => null, 'provider' => 'cloud9', 'enabled' => true, 'credentials' => [
            'environment' => 'sandbox', 'api_key' => 'tokenTestabc', 'payin_secret' => 'in-secret', 'payout_secret' => 'out-secret',
        ]]);
    }

    private function route(string $movement, string $rail, ?string $fallback = null): void
    {
        RailRoute::create(['movement' => $movement, 'rail' => $rail, 'fallback' => $fallback, 'audience' => 'everyone']);
    }

    private function person(string $phone = '0712345678'): User
    {
        $u = User::factory()->create(['phone' => $phone, 'name' => 'Jane Wanjiru']);
        $u->forceFill(['kyc_level' => 'basic'])->save();

        return $u;
    }

    private function webhook(string $event, array $body, string $secret)
    {
        return $this->postJson('/rails/cloud9/callback', ['event_type' => $event] + $body, ['X-Cloud9-Callback-Secret' => $secret]);
    }

    public function test_money_in_by_cloud9_the_customer_pays_the_charge_and_sees_only_sharet(): void
    {
        $this->route('collect', 'cloud9');
        $deposit = ['id' => '68MW27JX', 'provider' => 'MPESA-C2B', 'status' => 'SUCCESS', 'amount' => '100.00', 'charge' => '1.50', 'taxes' => '0.00', 'net_amount' => '98.50', 'provider_reference' => 'NLJ7RT61SV'];
        Http::fake([
            self::BASE.'payin/deposits/init/' => Http::response(['id' => '68MW27JX', 'status' => 'PENDING']),
            self::BASE.'payin/deposits/68MW27JX/' => Http::response($deposit),
        ]);
        $jane = $this->person();
        $order = $this->orders->confirm($this->orders->topUp($jane, '100'));
        $this->assertSame('collecting', $order->status);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE.'payin/deposits/init/' && $r['amount'] === 100 && $r['provider'] === 'MPESA-C2B'
            && $r['phone_number'] === '254712345678' && $r['reference'] === $order->sharetReference() && $r['narration'] === 'Sharet'
            && $r->hasHeader('Authorization', 'Bearer tokenTestabc'));

        $this->webhook('PAYIN', $deposit, 'wrong')->assertStatus(401);
        $this->assertSame('collecting', $order->fresh()->status);

        $this->webhook('PAYIN', $deposit, 'in-secret')->assertOk();
        $this->webhook('PAYIN', $deposit, 'in-secret')->assertOk();   // Cloud9 retries: booked once
        $order = $order->fresh();
        $this->assertSame('paid', $order->status);
        // The customer pays the charge: KES 100 by M-PESA, KES 98.50 in the wallet, KES 1.50 shown as the fee
        $this->assertSame(9850, $this->orders->walletBalance($jane));
        $this->assertSame([10000, 150, 9850], [$order->total, $order->fee, $order->amount]);
        $this->assertSame('+98.50', $order->present($jane)['signed']);
        $this->assertStringContainsString('payment charge 1.50', json_encode($order->timeline));
        $this->assertSame(9850, $this->ledger->account('1160')->fresh()->natural());
        $this->assertSame(0, (int) $this->ledger->account('5130')->fresh()->natural());
        // Three references: Sharet's, Cloud9's, and the network's own (the M-PESA receipt)
        $req = RailRequest::find($order->collect_request_id);
        $this->assertSame(['68MW27JX', 'NLJ7RT61SV', 'M-PESA'], [$req->provider_reference, $req->receipt, $req->meta['network']]);
        $this->assertMatchesRegularExpression('/^SH[A-Z0-9]+$/', $order->sharetReference());
        $this->assertStringNotContainsStringIgnoringCase('cloud9', json_encode($order->timeline).$order->failure);
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()), 'safeguarded money covers what customers are owed');
    }

    public function test_money_out_by_cloud9_takes_amount_plus_charge_from_the_cloud9_wallet(): void
    {
        $this->route('collect', 'cloud9');
        $this->route('payout_phone', 'cloud9');
        $in = ['id' => 'DEP1', 'status' => 'SUCCESS', 'amount' => '2000.00', 'charge' => '0.00', 'taxes' => '0.00', 'provider_reference' => 'RCPT1'];
        $out = ['id' => 'V3MWVE4N', 'status' => 'SUCCESS', 'amount' => '1500.00', 'charge' => '15.00', 'taxes' => '0.00', 'provider_reference' => 'NLK2PQ90AZ'];
        Http::fake([
            self::BASE.'payin/deposits/init/' => Http::response(['id' => 'DEP1', 'status' => 'PENDING']),
            self::BASE.'payin/deposits/DEP1/' => Http::response($in),
            self::BASE.'payout/payouts/init/' => Http::response(['id' => 'V3MWVE4N', 'status' => 'PENDING', 'charge' => '15.00']),
            self::BASE.'payout/payouts/V3MWVE4N/' => Http::response($out),
        ]);
        $jane = $this->person();
        $this->orders->confirm($this->orders->topUp($jane, '2000'));
        $this->webhook('PAYIN', $in, 'in-secret')->assertOk();

        $order = $this->orders->confirm($this->orders->quote($jane, 'phone', ['phone' => '0722333444'], '1500', null, 'wallet'));
        $this->assertSame('paying', $order->status);
        $this->assertSame('cloud9', $order->rail);
        Http::assertSent(fn (Request $r) => $r->url() === self::BASE.'payout/payouts/init/' && $r['provider'] === 'MPESA-B2C' && $r['account'] === '254722333444' && $r['amount'] === 1500);

        $this->webhook('PAYOUT', $out, 'out-secret')->assertOk();
        $order = $order->fresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame(200000 - 150000 - 1500, $this->ledger->account('1160')->fresh()->natural());   // amount + Cloud9's charge left
        $this->assertSame(1500, $this->ledger->account('5130')->fresh()->natural());
        $this->assertTrue(Invariants::passed(app(Invariants::class)->run()));
    }

    public function test_a_refusal_before_acceptance_falls_back_but_a_timeout_never_does(): void
    {
        $this->route('collect', 'cloud9', 'simulator');
        Http::fake([self::BASE.'payin/deposits/init/' => Http::response(['error' => 'Cloud9 deposits are paused for maintenance.'], 400)]);
        $jane = $this->person();
        $order = $this->orders->confirm($this->orders->topUp($jane, '100'));
        $req = RailRequest::find($order->collect_request_id);
        $this->assertSame('simulator', $req->rail);
        $this->assertSame('cloud9', $req->meta['fell_back_from']);
        while (! $order->final()) {
            $order = $this->orders->refresh($order);
        }
        $this->assertSame('paid', $order->status);

        // No answer at all: the request stays on Cloud9 as unknown, to be checked by id, never sent elsewhere
        Http::fake(fn () => throw new ConnectionException('timed out'));
        $second = $this->orders->confirm($this->orders->topUp($jane, '100'));
        $req = RailRequest::find($second->collect_request_id);
        $this->assertSame(['cloud9', 'unknown'], [$req->rail, $req->status]);
    }

    public function test_cloud9_wording_never_reaches_the_customer(): void
    {
        $this->route('collect', 'cloud9');
        Http::fake([self::BASE.'payin/deposits/init/' => Http::response(['error' => 'Cloud9 limit reached for this account.'], 400)]);
        $order = $this->orders->confirm($this->orders->topUp($this->person(), '100'));
        $this->assertSame('failed', $order->status);
        $this->assertStringNotContainsStringIgnoringCase('cloud9', json_encode($order->timeline).$order->failure);
        $this->assertStringContainsString('the payment partner limit reached', $order->failure);

        $this->get('/')->assertDontSee('Cloud9')->assertDontSee('Cashia');
    }

    public function test_switching_a_rail_takes_two_people(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$maker, $checker] = [User::factory()->create(['name' => 'Amina Otieno']), User::factory()->create(['name' => 'Brian Mwangi'])];
        $maker->assignRole('super_admin');
        $checker->assignRole('super_admin');

        $this->actingAs(User::factory()->create())->get('/admin/rails')->assertForbidden();
        $this->actingAs($maker)->get('/admin/rails')->assertOk()->assertSee('Send to an M-PESA number')->assertSee(route('rails.cloud9.callback'));

        $this->post('/admin/rails/changes', ['movement' => 'payout_phone', 'rail' => 'cloud9', 'fallback' => 'cloud9', 'audience' => 'named', 'reason' => 'x'])->assertSessionHasErrors('fallback');
        $this->post('/admin/rails/changes', ['movement' => 'payout_phone', 'rail' => 'cloud9', 'audience' => 'named', 'reason' => 'Sandbox tests passed'])->assertSessionHasNoErrors();
        $change = \App\Models\RailRouteChange::firstOrFail();
        $this->assertNull(RailRoute::for('payout_phone'));

        $this->actingAs($checker)->post("/admin/rails/changes/{$change->id}/approve")->assertRedirect(route('password.confirm'));
        $this->actingAs($maker)->withSession(['auth.password_confirmed_at' => time()])->post("/admin/rails/changes/{$change->id}/approve")->assertForbidden();
        $this->actingAs($checker)->withSession(['auth.password_confirmed_at' => time()])->post("/admin/rails/changes/{$change->id}/approve")->assertRedirect();
        $this->assertSame(['cloud9', 'named', $checker->id], [RailRoute::for('payout_phone')->rail, RailRoute::for('payout_phone')->audience, RailRoute::for('payout_phone')->approved_by]);

        // Only the test list gets it while the audience is "named"
        config(['rails.collect_only_for' => ['jane@example.test']]);
        $rails = app(\App\Integrations\Rails::class);
        $this->assertSame('cloud9', $rails->paying(User::factory()->create(['email' => 'jane@example.test'])));
        $this->assertSame('simulator', $rails->paying(User::factory()->create()));

        // Cloud9 can't be chosen before it is connected
        Integration::where('provider', 'cloud9')->delete();
        $this->actingAs($maker)->post('/admin/rails/changes', ['movement' => 'collect', 'rail' => 'cloud9', 'audience' => 'named', 'reason' => 'x'])->assertSessionHasErrors('rail');

        $this->actingAs($maker)->get('/admin/rails/activity')->assertOk()->assertSee('Rail activity');
    }

    public function test_sandbox_checks_run_against_cloud9_only_in_the_sandbox(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create(['phone' => '+254712345678']);
        $admin->assignRole('super_admin');
        Http::fake([self::BASE.'wallets/' => Http::response(['results' => [['wallet_id' => '7VXW9MJD', 'wallet_type' => 'MAIN', 'currency' => 'KES', 'available_balance' => '25000.00',
            'mpesa_paybill' => ['paybill_number' => '123456', 'account' => 482915]]]])]);
        $this->actingAs($admin)->post('/admin/rails/test', ['check' => 'wallets'])->assertSessionHas('cloud9_test', fn ($t) => $t['ok'] && str_contains($t['summary'], '7VXW9MJD MAIN KES 25000.00'));

        Integration::where('provider', 'cloud9')->first()->update(['credentials' => ['environment' => 'live', 'api_key' => 'tokenLivexyz']]);
        $this->actingAs($admin)->post('/admin/rails/test', ['check' => 'payout'])->assertSessionHasErrors('check');
    }
}
