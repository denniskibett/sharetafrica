<?php

namespace Tests\Feature\Fx;

use App\Fx\FxException;
use App\Fx\Rates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatesTest extends TestCase
{
    use RefreshDatabase;

    private Rates $fx;

    protected function setUp(): void
    {
        parent::setUp();
        config(['fx.buy_markup_bps' => 150, 'fx.sell_markdown_bps' => 350, 'fx.fixed_kes' => ['USD' => ['buy' => '1.30', 'sell' => '3.00']]]);
        $this->fx = app(Rates::class);
    }

    private function refused(string $reason, callable $fn): void
    {
        try {
            $fn();
            $this->fail("Expected $reason");
        } catch (FxException $e) {
            $this->assertSame($reason, $e->reason);
        }
    }

    public function test_buying_foreign_currency_costs_the_reference_plus_the_buy_markup_and_rounds_down(): void
    {
        $this->fx->record('KES/UGX', '28.65');
        $q = $this->fx->quote('KES', 'UGX', 1_000_000);        // KES 10,000.00 buys UGX

        $this->assertSame(['28.65000000', 'buy', 150], [$q['reference_rate'], $q['side'], $q['markup_bps']]);
        $this->assertSame(28_226_600, $q['to_amount']);         // 10,000 × 28.65 / 1.015 = UGX 282,266.00
        $this->assertSame(423_400, $q['spread']);               // UGX 4,234.00 to Sharet
        $this->assertSame('safaricom', $q['source']);
    }

    public function test_dollars_cost_cbk_plus_kes_1_30_and_fetch_cbk_less_kes_3(): void
    {
        $this->fx->record('KES/USD', bcdiv('1', '130', 8), source: 'cbk');   // CBK: USD 1 = KES 130.00 (stored 0.00769230)
        $this->assertSame(['131.3001', '127.0001'], [$this->fx->kesPer('USD', 'buy'), $this->fx->kesPer('USD', 'sell')]);
        $buy = $this->fx->quote('KES', 'USD', 1_313_000);   // KES 13,130.00
        $this->assertSame([9_999, 'cbk', 'buy', '1.30'], [$buy['to_amount'], $buy['source'], $buy['side'], $buy['markup_kes']]);   // USD 99.99, rounded down
        $sell = $this->fx->quote('USD', 'KES', 10_000);      // USD 100.00
        $this->assertSame([1_270_001, '3.00'], [$sell['to_amount'], $sell['markup_kes']]);   // KES 12,700.01
    }

    public function test_the_reverse_direction_also_favours_sharet(): void
    {
        $this->fx->record('KES/UGX', '28.65');
        $q = $this->fx->quote('UGX', 'KES', 30_000_000);       // UGX 300,000.00 home to Kenya
        // at the reference: 300,000 / 28.65 = KES 10,471.20; selling, the customer gets 3.5% less, rounded down
        $this->assertSame(1_010_470, $q['to_amount']);
        $this->assertSame('sell', $q['side']);
        $this->assertGreaterThan(0, $q['spread']);
        $this->assertLessThan(bcdiv('1', '28.65', 8), $q['customer_rate']);
    }

    public function test_no_rate_today_means_no_quote(): void
    {
        $this->fx->record('KES/UGX', '28.65', now('Africa/Nairobi')->subDay()->toDateString());
        $this->refused('rate_stale', fn () => $this->fx->quote('KES', 'UGX', 100_00));
    }

    public function test_strictly_fiat_virtual_assets_are_refused(): void
    {
        $this->refused('not_fiat', fn () => $this->fx->quote('KES', 'USDT', 100_00));
        $this->refused('not_fiat', fn () => $this->fx->record('BTC/KES', '1'));
        $this->refused('pair_not_offered', fn () => $this->fx->quote('KES', 'EUR', 100_00));
    }

    public function test_a_big_overnight_jump_must_be_confirmed(): void
    {
        $this->fx->record('KES/UGX', '28.65', now('Africa/Nairobi')->subDay()->toDateString());
        $this->refused('rate_jump', fn () => $this->fx->record('KES/UGX', '32.00'));
        $this->fx->record('KES/UGX', '32.00', null, null, true);
        $this->assertSame('32.00000000', $this->fx->reference('KES/UGX'));
    }

    public function test_treasury_command(): void
    {
        $this->artisan('fx:rate', ['pair' => 'KES/UGX', 'rate' => '28.65'])->expectsOutputToContain('Recorded KES/UGX')->assertSuccessful();
        $this->artisan('fx:rate', ['pair' => 'KES/USDT', 'rate' => '0.0077'])->assertFailed();
    }
}
