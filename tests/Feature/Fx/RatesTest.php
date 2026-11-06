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
        config(['fx.margin_bps' => 150, 'fx.pair_margin_bps' => []]);
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

    public function test_quotes_use_todays_safaricom_rate_less_the_margin_and_round_down(): void
    {
        $this->fx->record('KES/UGX', '28.65');
        $q = $this->fx->quote('KES', 'UGX', 1_000_000);        // KES 10,000.00

        $this->assertSame('28.65000000', $q['reference_rate']);
        $this->assertSame(28_220_250, $q['to_amount']);         // 10,000 × 28.65 × 0.985 = UGX 282,202.50
        $this->assertSame(429_750, $q['spread']);               // UGX 4,297.50 to Sharet
        $this->assertSame('safaricom', $q['source']);
    }

    public function test_the_reverse_direction_also_favours_sharet(): void
    {
        $this->fx->record('KES/UGX', '28.65');
        $q = $this->fx->quote('UGX', 'KES', 30_000_000);       // UGX 300,000.00 home to Kenya
        // at the reference: 300,000 / 28.65 = KES 10,471.20; customer gets 1.5% less, rounded down
        $this->assertSame(1_031_413, $q['to_amount']);
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
