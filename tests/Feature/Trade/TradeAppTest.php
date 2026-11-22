<?php

namespace Tests\Feature\Trade;

use App\Fx\Rates;
use App\Models\RampOrder;
use App\Models\TradeFxQuote;
use App\Ramp\RampOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Business\BusinessTestHelpers;
use Tests\TestCase;

/** Trade in the app: LPO → invoice → pay from the company wallet; finance off; payments abroad stop at a locked quote. */
class TradeAppTest extends TestCase
{
    use BusinessTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootBusiness();
    }

    public function test_lpo_invoice_and_pay_wallet_to_wallet_with_both_numbers_on_the_payment(): void
    {
        $owner = $this->person('Rift Owner');
        $importer = $this->business($owner, 'Rift Valley Imports', '900000');
        $maker = $this->person('Grace Maker');
        $checker = $this->person('Daniel Checker');
        $this->member($importer, $maker, 'maker');
        $this->member($importer, $checker, 'checker', 100_000_000);
        $seller = $this->person('Hardware Owner');
        $supplier = $this->business($seller, 'Nairobi Hardware');
        $unverified = $this->business($this->person('Other Owner'), 'Unverified Supplies', '0', verified: false);

        $mh = $this->phone($maker);
        $this->postJson('/api/mobile/v1/trade/orders', ['supplier' => $unverified->walletUser()->sharet_id, 'items' => 'x', 'amount' => '100', 'terms' => 'on_delivery'], $mh)
            ->assertStatus(422)->assertJsonValidationErrors('supplier');
        $lpo = $this->postJson('/api/mobile/v1/trade/orders', ['supplier' => $supplier->walletUser()->sharet_id, 'items' => '120 × cement 50 kg', 'amount' => '860000', 'terms' => 'on_delivery'], $mh)
            ->assertCreated()->assertJsonPath('order.lpo', 'LPO 0001')->assertJsonPath('order.status', 'sent')->assertJsonPath('order.supplier.name', 'Nairobi Hardware');
        $id = $lpo->json('order.id');

        // The supplier sees it to invoice; the importer can't pay before the invoice
        $sh = $this->phone($seller);
        $this->getJson('/api/mobile/v1/trade', $sh)->assertOk()->assertJsonCount(1, 'to_invoice')->assertJsonPath('finance.available', false);
        $ch = $this->phone($checker);
        $this->postJson("/api/mobile/v1/trade/orders/$id/pay", ['pin' => '4826'], $this->key($ch, 'trade-pay-early'))->assertStatus(409)->assertJsonPath('error', 'not_invoiced');
        $this->postJson("/api/mobile/v1/trade/orders/$id/invoice", ['number' => '2291', 'amount' => '900000', 'due' => now()->addDays(8)->toDateString()], $sh)->assertStatus(422)->assertJsonValidationErrors('amount');
        $this->postJson("/api/mobile/v1/trade/orders/$id/invoice", ['number' => '2291', 'amount' => '860000', 'due' => now()->addDays(8)->toDateString()], $sh)
            ->assertOk()->assertJsonPath('order.status', 'invoiced')->assertJsonPath('order.invoice.number', '2291');

        // Finance: designed, not available; nothing moves
        $this->postJson("/api/mobile/v1/trade/orders/$id/finance", [], $mh)->assertStatus(409)->assertJsonPath('error', 'not_available');

        // The maker who raised the LPO doesn't pay it; a checker does, with the PIN
        $this->getJson('/api/mobile/v1/trade', $mh)->assertOk()->assertJsonPath('to_pay.0.can_pay', false);
        $this->postJson("/api/mobile/v1/trade/orders/$id/pay", ['pin' => '4826'], $this->key($mh, 'trade-pay-maker'))->assertForbidden();
        $paid = $this->postJson("/api/mobile/v1/trade/orders/$id/pay", ['pin' => '4826'], $this->key($ch, 'trade-pay-0001'))->assertOk()
            ->assertJsonPath('order.status', 'paid')->assertJsonPath('order.paid.note', 'Invoice 2291 · LPO 0001');
        $this->assertNotEmpty($paid->json('order.paid.reference'));
        $this->postJson("/api/mobile/v1/trade/orders/$id/pay", ['pin' => '4826'], $this->key($ch, 'trade-pay-0002'))->assertStatus(409)->assertJsonPath('error', 'already_paid');

        $orders = app(RampOrders::class);
        $this->assertSame(4_000_000, $orders->walletBalance($importer->walletUser()));
        $this->assertSame(86_000_000, $orders->walletBalance($supplier->walletUser()));
        $this->assertSame(1, RampOrder::where('user_id', $importer->walletUser()->id)->where('note', 'Invoice 2291 · LPO 0001')->count());

        // Both statements carry the LPO and invoice numbers
        $this->getJson('/api/mobile/v1/business/statement', $sh)->assertOk()->assertJsonPath('statement.lines.0.note', 'Invoice 2291 · LPO 0001');
    }

    public function test_suppliers_decline_and_importers_cancel(): void
    {
        $owner = $this->person('Rift Owner');
        $importer = $this->business($owner, 'Rift Valley Imports');
        $seller = $this->person('Hardware Owner');
        $supplier = $this->business($seller, 'Nairobi Hardware');
        $oh = $this->phone($owner);
        $handle = '@'.$supplier->walletUser()->handle;

        $a = $this->postJson('/api/mobile/v1/trade/orders', ['supplier' => $handle, 'items' => 'Nails', 'amount' => '5000', 'terms' => '30_days'], $oh)->assertCreated()->json('order.id');
        $b = $this->postJson('/api/mobile/v1/trade/orders', ['supplier' => $handle, 'items' => 'Paint', 'amount' => '7000', 'terms' => 'bad'], $oh)->assertStatus(422);
        $b = $this->postJson('/api/mobile/v1/trade/orders', ['supplier' => $handle, 'items' => 'Paint', 'amount' => '7000', 'terms' => '60_days'], $oh)->assertCreated()->assertJsonPath('order.lpo', 'LPO 0002')->json('order.id');

        $this->postJson("/api/mobile/v1/trade/orders/$a/decline", ['reason' => 'Out of stock'], $this->phone($seller))->assertOk()->assertJsonPath('order.status', 'declined');
        $this->postJson("/api/mobile/v1/trade/orders/$b/cancel", [], $oh)->assertOk()->assertJsonPath('order.status', 'cancelled');
        $this->getJson("/api/mobile/v1/trade/orders/$a", $this->phone($this->person('Stranger Danger')))->assertForbidden();
    }

    public function test_paying_abroad_locks_a_real_quote_but_never_takes_money(): void
    {
        app(Rates::class)->record('KES/USD', bcdiv('1', '130', 8), source: 'cbk');   // CBK: USD 1 = KES 130.00
        $owner = $this->person('Rift Owner');
        $importer = $this->business($owner, 'Rift Valley Imports', '900000');
        $oh = $this->phone($owner);

        // Strictly fiat: no virtual assets
        $this->postJson('/api/mobile/v1/trade/fx-quotes', ['currency' => 'BTC', 'amount' => '1', 'supplier_name' => 'X', 'country' => 'CN', 'invoice_reference' => 'PI-1'], $oh)
            ->assertStatus(422)->assertJsonValidationErrors('currency');
        // No rate today for TZS: no quote, never a stale one
        $this->postJson('/api/mobile/v1/trade/fx-quotes', ['currency' => 'TZS', 'amount' => '100000', 'supplier_name' => 'Dar Mills', 'country' => 'TZ', 'invoice_reference' => 'INV-7'], $oh)
            ->assertStatus(422)->assertJsonValidationErrors('currency');

        $q = $this->postJson('/api/mobile/v1/trade/fx-quotes', ['currency' => 'USD', 'amount' => '5000.00', 'supplier_name' => 'Guangzhou Tools Co', 'country' => 'CN', 'invoice_reference' => 'PI-2026-118'], $oh)
            ->assertCreated()->assertJsonPath('quote.available', false)->assertJsonPath('quote.invoice_reference', 'PI-2026-118')
            ->assertJsonPath('quote.rate.source', 'Central Bank of Kenya');
        // USD 5,000 at CBK 130.00 + KES 1.30 = about KES 656,500
        $this->assertStringStartsWith('656,500.', $q->json('quote.kes_amount'));   // CBK stores 0.00769230, so 130.00013 a dollar
        $this->assertStringStartsWith('131.300', $q->json('quote.rate.kes_per_unit'));
        $this->assertStringContainsString('bank partner for SWIFT', $q->json('quote.message'));

        $id = $q->json('quote.id');
        $this->getJson("/api/mobile/v1/trade/fx-quotes/$id", $oh)->assertOk()->assertJsonPath('quote.expired', false);
        $this->postJson("/api/mobile/v1/trade/fx-quotes/$id/pay", ['pin' => '4826'], $this->key($oh, 'abroad-pay-1'))->assertStatus(409)->assertJsonPath('error', 'not_available');
        $this->assertSame(90_000_000, app(RampOrders::class)->walletBalance($importer->walletUser()));
        $this->assertSame(0, RampOrder::where('user_id', $importer->walletUser()->id)->count());

        $this->travel(16)->minutes();
        $this->postJson("/api/mobile/v1/trade/fx-quotes/$id/pay", ['pin' => '4826'], $this->key($oh, 'abroad-pay-2'))->assertStatus(409)->assertJsonPath('error', 'quote_expired');
        $this->assertSame(1, TradeFxQuote::count());
    }
}
