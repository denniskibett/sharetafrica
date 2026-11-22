<?php

namespace Tests\Feature\Mobile;

use App\Ledger\Ledger;
use App\Merchant\Sales;
use App\Models\Company;
use App\Models\MerchantSale;
use App\Models\MerchantTill;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 2 · Merchant and cashier: owner, tills, cashier PIN, charging, refunds with owner approval, end of day. */
class MerchantCashierTest extends TestCase
{
    use RefreshDatabase;

    private const OWNER_PHONE = 'owner-phone-0001';

    private const SHOP_PHONE = 'shop-phone-0002';

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0,
            'security.lockdown_file' => sys_get_temp_dir().'/merchant-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed(LedgerAccountSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    private function person(string $phone, string $name, string $balance = '0'): User
    {
        $u = User::factory()->create(['phone' => $phone, 'name' => $name, 'password' => bcrypt('Secret-pass-1')]);
        $u->forceFill(['kyc_level' => 'basic'])->save();
        TransactionPin::set($u, '4826');
        if ($balance !== '0') {
            $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($u, $balance));
            while (! $o->final()) {
                $o = app(RampOrders::class)->refresh($o);
            }
        }

        return $u->fresh();
    }

    private function owner(bool $verified = true): array
    {
        $owner = $this->person('0711000111', 'Shiku Wanjiru');
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0711000111', 'password' => 'Secret-pass-1', 'device_id' => self::OWNER_PHONE])->assertCreated()->json('token');
        $this->postJson('/api/mobile/v1/merchant/setup', ['name' => 'Mama Shiku Stores', 'kra_pin' => 'P051234567X'], $this->h($token))->assertCreated()
            ->assertJsonPath('shop.name', 'Mama Shiku Stores')->assertJsonPath('shop.verified', false);
        $shop = Company::where('name', 'Mama Shiku Stores')->firstOrFail();
        if ($verified) {
            $shop->forceFill(['verified_at' => now()])->save();
        }

        return [$owner->fresh(), $token, $shop->fresh()];
    }

    private function h(string $token, ?string $key = null, string $device = self::OWNER_PHONE): array
    {
        return array_filter(['Authorization' => "Bearer $token", 'X-Device-Id' => $device, 'Idempotency-Key' => $key]);
    }

    private function t(string $till, ?string $key = null): array
    {
        return $this->h($till, $key, self::SHOP_PHONE);
    }

    /** Owner adds a cashier; the shop phone links with phone + code + new cashier PIN. Returns the till token. */
    private function cashier(string $ownerToken, string $name = 'Brian Kamau', string $phone = '0722410556'): string
    {
        $code = $this->postJson('/api/mobile/v1/merchant/team', ['name' => $name, 'phone' => $phone], $this->h($ownerToken))->assertCreated()->json('code');

        return $this->postJson('/api/mobile/v1/cashier/link', ['phone' => $phone, 'code' => $code, 'pin' => '5937', 'pin_confirmation' => '5937', 'device_id' => self::SHOP_PHONE])
            ->assertCreated()->json('token');
    }

    private function settle(string $uuid): MerchantSale
    {
        $sale = MerchantSale::where('uuid', $uuid)->firstOrFail();
        for ($i = 0; $i < 6 && $sale->status === 'pending'; $i++) {
            $sale = app(Sales::class)->follow($sale);
        }

        return $sale;
    }

    public function test_a_shop_needs_a_verified_owner_and_sharets_check_before_taking_money(): void
    {
        $nobody = User::factory()->create(['phone' => '0733000999', 'password' => bcrypt('Secret-pass-1')]);
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0733000999', 'password' => 'Secret-pass-1', 'device_id' => self::OWNER_PHONE])->json('token');
        $this->getJson('/api/mobile/v1/merchant', $this->h($token))->assertOk()->assertJsonPath('shop', null);
        $this->getJson('/api/mobile/v1/merchant/team', $this->h($token))->assertForbidden()->assertJsonPath('error', 'no_shop');
        $this->postJson('/api/mobile/v1/merchant/setup', ['name' => 'Kiosk'], $this->h($token))->assertStatus(422);

        [$owner, $token, $shop] = $this->owner(verified: false);
        $this->assertTrue($owner->hasRole('merchant'));
        $this->assertTrue($shop->walletUser()->is_business_wallet);
        $this->postJson('/api/mobile/v1/merchant/charges', ['amount' => '850', 'method' => 'qr'], $this->h($token, 'charge-unverified-1'))
            ->assertStatus(422)->assertJsonValidationErrors('amount');
    }

    public function test_cashier_links_a_shop_phone_signs_in_with_pin_and_a_paused_till_stops_at_once(): void
    {
        [, $owner] = $this->owner();
        $code = $this->postJson('/api/mobile/v1/merchant/team', ['name' => 'Brian Kamau', 'phone' => '0722410556'], $this->h($owner))->assertCreated()
            ->assertJsonPath('till.label', 'Till 1')->assertJsonPath('till.status', 'invited')->json('code');
        $this->postJson('/api/mobile/v1/merchant/team', ['name' => 'Brian again', 'phone' => '+254722410556'], $this->h($owner))->assertStatus(422);

        $this->postJson('/api/mobile/v1/cashier/link', ['phone' => '0722410556', 'code' => '000000', 'pin' => '5937', 'pin_confirmation' => '5937', 'device_id' => self::SHOP_PHONE])->assertStatus(422);
        $till = $this->postJson('/api/mobile/v1/cashier/link', ['phone' => '0722410556', 'code' => $code, 'pin' => '5937', 'pin_confirmation' => '5937', 'device_id' => self::SHOP_PHONE])
            ->assertCreated()->assertJsonPath('till.shop', 'Mama Shiku Stores')->assertJsonPath('till.cashier', 'Brian')->json('token');
        // the code works once
        $this->postJson('/api/mobile/v1/cashier/link', ['phone' => '0722410556', 'code' => $code, 'pin' => '5937', 'pin_confirmation' => '5937', 'device_id' => 'another-phone-01'])->assertStatus(422);

        $this->getJson('/api/mobile/v1/cashier/me', $this->t($till))->assertOk()->assertJsonPath('open', true);
        $this->getJson('/api/mobile/v1/cashier/me', ['Authorization' => "Bearer $till", 'X-Device-Id' => 'stolen-token-01'])->assertUnauthorized();
        $this->postJson('/api/mobile/v1/cashier/sign-out', [], $this->t($till))->assertOk();
        $this->getJson('/api/mobile/v1/cashier/today', $this->t($till))->assertUnauthorized()->assertJsonPath('error', 'till_locked');
        $this->postJson('/api/mobile/v1/cashier/sign-in', ['pin' => '1111'], $this->t($till))->assertStatus(422)->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/cashier/sign-in', ['pin' => '5937'], $this->t($till))->assertOk()->assertJsonPath('open', true);
        $this->getJson('/api/mobile/v1/cashier/today', $this->t($till))->assertOk()->assertJsonPath('sales', 0);

        // the cashier sees nothing of the owner's: no balance, no owner routes
        $this->getJson('/api/mobile/v1/me', $this->t($till))->assertUnauthorized();
        $this->getJson('/api/mobile/v1/merchant', $this->t($till))->assertUnauthorized();

        $this->postJson('/api/mobile/v1/merchant/tills/1/pause', [], $this->h($owner))->assertOk()->assertJsonPath('till.status', 'paused');
        $this->getJson('/api/mobile/v1/cashier/today', $this->t($till))->assertForbidden()->assertJsonPath('error', 'till_paused');
        $this->postJson('/api/mobile/v1/merchant/tills/1/resume', [], $this->h($owner))->assertOk()->assertJsonPath('till.status', 'active');
        $this->getJson('/api/mobile/v1/cashier/today', $this->t($till))->assertUnauthorized()->assertJsonPath('error', 'till_locked');   // pausing locked it
        $this->postJson('/api/mobile/v1/merchant/tills/1/remove', [], $this->h($owner))->assertOk();
        $this->getJson('/api/mobile/v1/cashier/me', $this->t($till))->assertUnauthorized();
    }

    public function test_cashier_charges_by_mpesa_prompt_once_and_the_shop_pays_the_merchant_fee_in_the_ledger(): void
    {
        [, $owner, $shop] = $this->owner();
        $till = $this->cashier($owner);

        $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '850', 'method' => 'mpesa', 'phone' => '0722410999'], $this->t($till))->assertStatus(428);
        $first = $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '850', 'method' => 'mpesa', 'phone' => '0722410999'], $this->t($till, 'charge-0001-ab'))->assertCreated();
        $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '850', 'method' => 'mpesa', 'phone' => '0722410999'], $this->t($till, 'charge-0001-ab'))->assertCreated()
            ->assertHeader('Idempotent-Replay', 'true');
        $uuid = $first->json('sale.uuid');
        $this->assertSame(1, MerchantSale::count());
        $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '200000', 'method' => 'qr'], $this->t($till, 'charge-0002-ab'))->assertStatus(422);   // over the cashier limit

        for ($i = 0; $i < 6 && $this->getJson("/api/mobile/v1/cashier/sales/$uuid", $this->t($till))->json('sale.status') === 'pending'; $i++);
        $this->getJson("/api/mobile/v1/cashier/sales/$uuid", $this->t($till))->assertOk()->assertJsonPath('sale.status', 'paid')->assertJsonPath('sale.via', 'M-PESA')
            ->assertJsonPath('sale.payer', '0722 ••• 999')->assertJsonMissingPath('sale.fee');

        // 1.5% of 850.00 = 12.75: the shop's wallet holds 837.25; Sharet's fee income + excise = 12.75
        $wallet = $shop->walletUser();
        $this->assertSame(83725, app(RampOrders::class)->walletBalance($wallet));
        $ledger = app(Ledger::class);
        $this->assertSame(1275, abs($ledger->account('4110')->balance) + abs($ledger->account('2300')->balance));
        $this->artisan('ledger:check')->assertSuccessful();

        $this->getJson('/api/mobile/v1/cashier/today', $this->t($till))->assertOk()->assertJsonPath('sales', 1)->assertJsonPath('total', '850.00');
        $home = $this->getJson('/api/mobile/v1/merchant', $this->h($owner))->assertOk();
        $home->assertJsonPath('today.sales', 1)->assertJsonPath('today.gross', '850.00')->assertJsonPath('today.fees', '12.75')
            ->assertJsonPath('recent.0.till.cashier', 'Brian')->assertJsonPath('tills.0.today', '850.00')->assertJsonPath('balance', '837.25');
    }

    public function test_a_qr_sale_paid_from_a_sharet_wallet_is_free_and_refunded_only_after_the_owner_approves(): void
    {
        [, $owner, $shop] = $this->owner();
        $till = $this->cashier($owner);
        $akinyi = $this->person('0722118904', 'Akinyi Wekesa', '2000');

        $sale = $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '300', 'method' => 'qr'], $this->t($till, 'charge-qr-0001'))->assertCreated();
        $this->assertStringContainsString('/r/', $sale->json('sale.pay_url'));
        $r = MerchantSale::where('uuid', $sale->json('sale.uuid'))->first()->request;
        app(\App\Api\PaymentRequests::class)->payWithSharet($r, $akinyi, '4826');
        $s = $this->getJson('/api/mobile/v1/cashier/sales/'.$sale->json('sale.uuid'), $this->t($till))->assertJsonPath('sale.status', 'paid')->assertJsonPath('sale.payer', 'Akinyi W.');
        $this->assertSame(30000, app(RampOrders::class)->walletBalance($shop->walletUser()));   // Sharet members: no merchant fee

        // the cashier asks; nothing moves
        $uuid = $s->json('sale.uuid');
        $this->postJson("/api/mobile/v1/cashier/sales/$uuid/refund", ['amount' => '400', 'reason' => 'Too much'], $this->t($till))->assertStatus(422);
        $ask = $this->postJson("/api/mobile/v1/cashier/sales/$uuid/refund", ['amount' => '100', 'reason' => 'Wrong size, returned'], $this->t($till))->assertCreated()
            ->assertJsonPath('refund.status', 'asked');
        $this->assertSame(170000, app(RampOrders::class)->walletBalance($akinyi));
        $this->postJson("/api/mobile/v1/cashier/sales/$uuid/refund", ['amount' => '250', 'reason' => 'More'], $this->t($till))->assertStatus(422);   // 100 asked, 200 left

        $this->getJson('/api/mobile/v1/merchant/refunds', $this->h($owner))->assertOk()->assertJsonPath('waiting.0.asked_by.cashier', 'Brian')
            ->assertJsonPath('waiting.0.amount', '100.00')->assertJsonPath('waiting.0.goes_back_to', 'Their Sharet wallet, at once');
        $ruuid = $ask->json('refund.uuid');
        $this->postJson("/api/mobile/v1/merchant/refunds/$ruuid/approve", ['pin' => '0000'], $this->h($owner, 'refund-ok-0001'))->assertStatus(422);
        $this->postJson("/api/mobile/v1/merchant/refunds/$ruuid/approve", ['pin' => '4826'], $this->h($owner, 'refund-ok-0001'))->assertOk()->assertJsonPath('refund.status', 'paid');
        $this->postJson("/api/mobile/v1/merchant/refunds/$ruuid/approve", ['pin' => '4826'], $this->h($owner, 'refund-ok-0002'))->assertStatus(422);   // once

        $this->assertSame(180000, app(RampOrders::class)->walletBalance($akinyi));
        $this->assertSame(20000, app(RampOrders::class)->walletBalance($shop->walletUser()));
        $this->getJson("/api/mobile/v1/merchant/sales/$uuid", $this->h($owner))->assertJsonPath('sale.refunded', '100.00');
        $this->artisan('ledger:check')->assertSuccessful();
    }

    public function test_owner_refunds_an_mpesa_sale_to_the_payers_phone_and_declines_another(): void
    {
        [, $owner, $shop] = $this->owner();
        $till = $this->cashier($owner);
        $uuid = $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '2300', 'method' => 'mpesa', 'phone' => '0722410999'], $this->t($till, 'charge-ref-0001'))->json('sale.uuid');
        $this->assertSame('paid', $this->settle($uuid)->status);

        $ask = $this->postJson("/api/mobile/v1/cashier/sales/$uuid/refund", ['reason' => 'Changed mind'], $this->t($till))->assertCreated()->assertJsonPath('refund.amount', '2,300.00');
        $this->postJson('/api/mobile/v1/merchant/refunds/'.$ask->json('refund.uuid').'/decline', [], $this->h($owner))->assertOk()->assertJsonPath('refund.status', 'declined');

        $before = app(RampOrders::class)->walletBalance($shop->walletUser());
        $r = $this->postJson("/api/mobile/v1/merchant/sales/$uuid/refund", ['amount' => '500', 'reason' => 'Partly returned', 'pin' => '4826'], $this->h($owner, 'own-refund-0001'))
            ->assertCreated()->assertJsonPath('refund.goes_back_to', 'Their M-PESA, within minutes');
        $refund = \App\Models\MerchantRefund::where('uuid', $r->json('refund.uuid'))->first();
        $order = $refund->order;
        $this->assertSame('phone', $order->destination_type);
        $this->assertSame('254722410999', $order->destination['phone']);
        while (! $order->final()) {
            $order = app(RampOrders::class)->refresh($order);
        }
        $this->assertSame('paid', $refund->fresh()->status);
        $this->assertSame($before - $order->total, app(RampOrders::class)->walletBalance($shop->walletUser()));   // the shop pays the payout fee
        $this->artisan('ledger:check')->assertSuccessful();
    }

    public function test_end_of_day_closes_each_till_and_the_owner_sees_every_till(): void
    {
        [, $owner, $shop] = $this->owner();
        $brian = $this->cashier($owner);
        $this->postJson('/api/mobile/v1/merchant/team', ['name' => 'Achieng Odhiambo', 'phone' => '0733222111'], $this->h($owner))->assertCreated()->assertJsonPath('till.label', 'Till 2');

        foreach (['850', '1200'] as $i => $amount) {
            $uuid = $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => $amount, 'method' => 'mpesa', 'phone' => '0722410999'], $this->t($brian, "eod-charge-000$i"))->json('sale.uuid');
            $this->settle($uuid);
        }
        $this->getJson('/api/mobile/v1/merchant/team', $this->h($owner))->assertOk()->assertJsonPath('tills.0.today', '2,050.00')->assertJsonPath('tills.0.signed_in', true)
            ->assertJsonPath('tills.1.status', 'invited')->assertJsonPath('tills.1.sales_today', 0);

        $this->postJson('/api/mobile/v1/cashier/close', [], $this->t($brian))->assertOk()->assertJsonPath('close.sales', 2)->assertJsonPath('close.gross', '2,050.00')
            ->assertJsonPath('close.fees', '30.75')->assertJsonPath('close.net', '2,019.25');
        $this->getJson('/api/mobile/v1/cashier/today', $this->t($brian))->assertUnauthorized();   // closed: PIN to open again
        $this->postJson('/api/mobile/v1/cashier/sign-in', ['pin' => '5937'], $this->t($brian))->assertOk();
        $this->getJson('/api/mobile/v1/cashier/today', $this->t($brian))->assertOk()->assertJsonPath('sales', 0)->assertJsonPath('totals_today.sales', 2)
            ->assertJsonPath('last_close.gross', '2,050.00');

        // the owner still sees the whole day
        $this->getJson('/api/mobile/v1/merchant/sales', $this->h($owner))->assertOk()->assertJsonPath('totals.sales', 2)->assertJsonPath('totals.net', '2,019.25');
        $this->getJson('/api/mobile/v1/merchant/sales?till=1', $this->h($owner))->assertOk()->assertJsonCount(2, 'data');

        // automatic close at the shop's time; settlement stays off until switched on
        $uuid = $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '500', 'method' => 'mpesa', 'phone' => '0722410999'], $this->t($brian, 'eod-charge-0009'))->json('sale.uuid');
        $this->settle($uuid);
        $this->travelTo(now(config('merchant.timezone'))->setTime(17, 5));
        $this->artisan('merchant:close-day')->assertSuccessful();
        $this->assertNotNull(MerchantSale::where('uuid', $uuid)->value('close_id'));
        $this->artisan('merchant:close-day')->assertSuccessful();
        $this->assertSame(2, \App\Models\MerchantClose::where('company_id', $shop->id)->count());
    }

    public function test_settlement_needs_the_pin_and_pays_the_balance_to_mpesa_when_switched_on(): void
    {
        [, $owner, $shop] = $this->owner();
        $till = $this->cashier($owner);
        $this->postJson('/api/mobile/v1/merchant/settlement', ['mode' => 'mpesa', 'phone' => '0711000111', 'settle_at' => '17:00'], $this->h($owner))->assertStatus(422);
        $this->postJson('/api/mobile/v1/merchant/settlement', ['mode' => 'mpesa', 'phone' => '0711000111', 'settle_at' => '17:00', 'pin' => '4826'], $this->h($owner))
            ->assertOk()->assertJsonPath('settlement.mode', 'mpesa')->assertJsonPath('settlement.phone', '254711000111');

        $uuid = $this->postJson('/api/mobile/v1/cashier/charges', ['amount' => '5000', 'method' => 'mpesa', 'phone' => '0722410999'], $this->t($till, 'settle-charge-01'))->json('sale.uuid');
        $this->settle($uuid);
        config(['merchant.auto_settle' => true]);
        $this->travelTo(now(config('merchant.timezone'))->setTime(17, 1));
        $this->artisan('merchant:close-day')->assertSuccessful();

        $last = \App\Merchant\Shop::settings($shop->fresh())['last_settlement'];
        $this->assertNotNull($last['order']);
        $order = \App\Models\RampOrder::where('uuid', $last['order'])->first();
        while (! $order->final()) {
            $order = app(RampOrders::class)->refresh($order);
        }
        $this->assertSame('paid', $order->status);
        $this->assertLessThan(1000, app(RampOrders::class)->walletBalance($shop->walletUser()));   // all but a few shillings went out (the payout fee from the balance)
        $this->getJson('/api/mobile/v1/merchant/settlement', $this->h($owner))->assertOk()->assertJsonPath('last_settlement.status', 'paid');
        $this->artisan('ledger:check')->assertSuccessful();
    }

    public function test_money_paid_to_the_shops_account_number_counts_as_a_sale(): void
    {
        [, , $shop] = $this->owner();
        MerchantTill::create(['company_id' => $shop->id, 'number' => 1, 'cashier_name' => 'Brian', 'cashier_phone' => '254722410556', 'status' => 'active']);
        $wallet = $shop->walletUser();
        $order = app(RampOrders::class)->creditPaybill($wallet, ['id' => 'dep-0001', 'amount' => '1000', 'phone_number' => '254722000111', 'narration' => 'JOHN DOE'],
            \App\Integrations\RailResult::succeeded('QWE123RTY'));
        $this->assertSame('paid', $order->fresh()->status);
        $sale = MerchantSale::where('ramp_order_id', $order->id)->firstOrFail();
        $this->assertSame(['account', 'paid', \App\Ledger\Money::percent($sale->amount, 150)], [$sale->method, $sale->status, $sale->fee]);
        $this->assertNull($sale->till_id);
        $this->artisan('ledger:check')->assertSuccessful();
    }
}
