<?php

namespace Tests\Feature\Fx;

use App\Fx\Rates;
use App\Models\User;
use App\Support\HomeRates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** USD is Sharet's main reference: "USD 1 = KES 131.30" on the customer header and in the app, with the country. */
class UsdLineTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_line_is_usd_first_at_the_customer_rate_and_dated_when_not_todays(): void
    {
        $this->assertNull(HomeRates::usd(latest: true));   // nothing recorded yet: nothing made up

        app(Rates::class)->record('KES/USD', '0.00775', now('Africa/Nairobi')->subDay()->toDateString());
        $this->assertNull(HomeRates::line());   // strict: today's only (the website hero)
        $line = HomeRates::line(latest: true);
        // 1 / 0.00775 + 1.30 = 130.33 (buying a dollar: CBK + KES 1.30)
        $this->assertSame('USD 1 = KES 130.33 · '.now('Africa/Nairobi')->subDay()->format('j M'), $line);

        app(Rates::class)->record('KES/USD', '0.00770');
        $this->assertSame('USD 1 = KES 131.17', HomeRates::line());
        $this->assertTrue(HomeRates::usd()['today']);
    }

    public function test_the_app_gets_the_rate_and_the_country_for_its_flag(): void
    {
        app(Rates::class)->record('KES/USD', '0.00770');
        $this->seed(\Database\Seeders\LedgerAccountSeeder::class);
        User::factory()->create(['phone' => '0712345678', 'country' => 'Kenya', 'password' => bcrypt('Secret-pass-1')]);
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712345678', 'password' => 'Secret-pass-1', 'device_id' => 'sh-usd-test', 'platform' => 'android'])->assertCreated()->json('token');
        $this->withHeaders(['Authorization' => "Bearer $token", 'X-Device-Id' => 'sh-usd-test'])->getJson('/api/mobile/v1/me')->assertOk()
            ->assertJsonPath('country', ['code' => 'KE', 'name' => 'Kenya'])
            ->assertJsonPath('usd.rate', '131.17')->assertJsonPath('rates', 'USD 1 = KES 131.17');
        $this->getJson('/api/mobile/v1/config')->assertJsonPath('usd.line', 'USD 1 = KES 131.17');
    }
}
