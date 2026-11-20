<?php

namespace Tests\Feature\Ramp;

use App\Models\Integration;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Support\Handle;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** SMS to customers through KenyaSMS as payments move: prompt, result, transfers; never in the way of money. */
class CustomerMessagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LedgerAccountSeeder::class);
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        Http::fake(['kenyasms.com/*' => Http::response(['success' => true, 'data' => ['message_id' => 'm1', 'status' => 'queued']], 201)]);
    }

    private function person(string $phone, string $name = 'Jane Wanjiru'): User
    {
        $u = User::factory()->create(['phone' => $phone, 'name' => $name]);
        $u->forceFill(['kyc_level' => 'basic'])->save();

        return $u;
    }

    private function smsTo(string $phone): array
    {
        return collect(Http::recorded())->map(fn ($p) => $p[0])->filter(fn ($r) => str_contains($r->url(), '/sms/send') && $r['recipient'] === $phone)
            ->map(fn ($r) => $r['message'])->values()->all();
    }

    public function test_the_payer_gets_the_prompt_and_the_result_and_a_transfer_tells_both(): void
    {
        Integration::create(['provider' => 'kenyasms', 'enabled' => true, 'credentials' => ['api_url' => 'https://kenyasms.com/api/v1', 'api_key' => 'ks_live_x', 'sender_id' => 'SHARET']]);
        $orders = app(RampOrders::class);
        $jane = $this->person('0712345678');
        $o = $orders->confirm($orders->topUp($jane, '500'));
        while (! $o->final()) {
            $o = $orders->refresh($o);
        }

        $msgs = $this->smsTo('254712345678');
        $this->assertCount(2, $msgs);
        $this->assertStringContainsString('approve KES 500.00 on the M-PESA prompt', $msgs[0]);
        $this->assertStringContainsString('KES 500.00 added to your wallet', $msgs[1]);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/sms/send') && $r->hasHeader('Authorization', 'Bearer ks_live_x')
            && $r['sender_id'] === 'SHARET' && $r['message_type'] === 'transactional' && str_starts_with($r['idempotency_key'], 'sms:'));

        $juma = $this->person('0722333444', 'Juma Otieno');
        $orders->transfer($jane, '@'.Handle::for($juma), '200');
        $this->assertStringContainsString('KES 200.00 sent to @juma.otieno', last($this->smsTo('254712345678')));
        $this->assertStringContainsString('you received KES 200.00 from @jane.wanjiru', last($this->smsTo('254722333444')));
    }

    public function test_without_kenyasms_set_up_payments_carry_on_silently(): void
    {
        $orders = app(RampOrders::class);
        $o = $orders->confirm($orders->topUp($this->person('0712345678'), '500'));
        $this->assertSame('collecting', $o->status);
        Http::assertNothingSent();
    }
}
