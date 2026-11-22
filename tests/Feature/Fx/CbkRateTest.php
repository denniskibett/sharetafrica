<?php

namespace Tests\Feature\Fx;

use App\Fx\Cbk;
use App\Fx\FxException;
use App\Fx\Rates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** The KES/USD reference from the Central Bank of Kenya each morning, shared with Breve, scharge and loans through /api/v1/fx/rates. */
class CbkRateTest extends TestCase
{
    use RefreshDatabase;

    private function page(string $usd = '130.00', string $posted = '20-11-2026'): string
    {
        return <<<HTML
<table><tr><th class="tg-hgcj" colspan="3">Daily KES Exchange Rates<br></th></tr>
<tr><td class="tg-4eph" colspan="2"><small>US DOLLAR</small></td>

<td class="tg-4eph">$usd</td></tr>
<tr><td class="tg-4eph" colspan="2"><small>STG POUND</small></td><td class="tg-4eph">170.35</td></tr>
<tr><td class="tg-4eph" colspan="2"><small>EURO</small></td><td class="tg-4eph">142.71</td></tr>
<tr><td colspan="3" style="text-align:right;"><i>Posted On: $posted</i></td></tr></table>
HTML;
    }

    public function test_reads_the_panel_and_refuses_a_page_it_does_not_recognise(): void
    {
        $this->assertSame(['posted' => '2026-11-20', 'kes_per' => ['USD' => '130.00', 'GBP' => '170.35', 'EUR' => '142.71']], Cbk::parse($this->page()));
        $this->expectException(FxException::class);
        Cbk::parse('<html>Maintenance</html>');
    }

    public function test_records_cbk_daily_leaves_a_hand_set_rate_alone_and_the_api_shares_it(): void
    {
        Http::fake(['www.centralbank.go.ke/*' => Http::response($this->page())]);

        $this->artisan('fx:cbk')->assertSuccessful();
        $row = DB::table('fx_rates')->where('pair', 'KES/USD')->sole();
        $this->assertSame(['0.00769230', 'cbk'], [bcadd((string) $row->rate, '0', 8), $row->source]);
        $this->artisan('fx:cbk')->expectsOutputToContain('already CBK')->assertSuccessful();   // later runs the same day: nothing new
        $this->assertSame(1, DB::table('fx_rates')->count());

        $this->getJson('/api/v1/fx/rates')->assertOk()
            ->assertJsonPath('pairs.KES/USD', '0.00769230')->assertJsonPath('rates.USD', '0.00769230')
            ->assertJsonPath('kes_per.USD', '130.0001')->assertJsonPath('source', 'cbk')->assertJsonPath('stale', []);
        $this->assertSame('USD 1 = KES 132.65', \App\Support\HomeRates::line());   // customers: CBK's rate less the 2% margin

        // treasury set it by hand today: left alone unless --force
        DB::table('fx_rates')->delete();
        app(Rates::class)->record('KES/USD', '0.00760');
        $this->artisan('fx:cbk')->expectsOutputToContain('set by hand')->assertSuccessful();
        $this->artisan('fx:cbk --force')->assertSuccessful();
        $this->assertSame('cbk', DB::table('fx_rates')->orderByDesc('id')->value('source'));
    }

    public function test_a_big_jump_or_an_outage_records_nothing(): void
    {
        app(Rates::class)->record('KES/USD', '0.00769230', now('Africa/Nairobi')->subDay()->toDateString(), source: 'cbk');
        Http::fake(['www.centralbank.go.ke/*' => Http::response($this->page('150.00'))]);
        $this->artisan('fx:cbk')->assertFailed();   // 13% overnight: treasury checks it
        $this->assertSame(1, DB::table('fx_rates')->count());

        Http::fake(['www.centralbank.go.ke/*' => Http::response('Forbidden', 403)]);
        $this->artisan('fx:cbk')->assertFailed();
        $this->assertSame(1, DB::table('fx_rates')->count());
    }
}
