<?php

namespace Tests\Feature\Api;

use App\Fx\Rates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FxRatesEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_rates_are_public_read_only_and_without_margin(): void
    {
        config(['fx.margin_bps' => 150]);
        $fx = app(Rates::class);
        $yesterday = now(config('fx.timezone'))->subDay()->toDateString();
        $today = now(config('fx.timezone'))->toDateString();
        $fx->record('KES/UGX', '28.10', $yesterday);
        $fx->record('KES/UGX', '28.65');
        $fx->record('KES/UGX', '28.66', null, null, false, 'correction'); // the latest of the day wins
        $fx->record('KES/USD', '0.0077', $yesterday);

        $this->getJson('/api/v1/fx/rates') // no API key needed
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertExactJson([
                'base' => 'KES', 'date' => $today,
                'pairs' => ['KES/UGX' => '28.66000000', 'KES/USD' => '0.00770000'],   // reference, no margin
                'rates' => ['UGX' => '28.66000000', 'USD' => '0.00770000'], 'kes_per' => ['UGX' => '0.0348', 'USD' => '129.8701'],
                'dates' => ['KES/UGX' => $today, 'KES/USD' => $yesterday], 'sources' => ['KES/UGX' => 'safaricom', 'KES/USD' => 'safaricom'],
                'stale' => ['KES/USD'], 'source' => 'safaricom', 'margin' => 'none', 'use' => 'display_only',
            ]);

        // Display only: nothing can be written or quoted here
        $this->postJson('/api/v1/fx/rates', ['pair' => 'KES/UGX', 'rate' => '1'])->assertStatus(405);
        $this->assertSame(4, \DB::table('fx_rates')->count());
    }

    public function test_no_rates_yet_is_an_empty_answer(): void
    {
        $this->getJson('/api/v1/fx/rates')->assertOk()->assertJsonPath('date', null)->assertJsonPath('pairs', []);
    }
}
