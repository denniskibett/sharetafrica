<?php

namespace Tests\Feature;

use App\Models\ApiPaymentRequest;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Support\Handle;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Requests and splits (canvas P05): ask an @name or a phone, they pay on the link; split a bill; decline, remind, cancel. */
class RequestsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0]);
        $this->seed(LedgerAccountSeeder::class);
    }

    private function person(string $name, string $phone): User
    {
        $u = User::factory()->create(['name' => $name, 'phone' => $phone, 'onboarding_status' => 'active']);
        $u->forceFill(['kyc_level' => 'basic'])->save();

        return $u;
    }

    public function test_ask_a_member_by_name_they_pay_from_their_wallet_free(): void
    {
        $juma = $this->person('Juma Otieno', '0712345678');
        $akinyi = $this->person('Akinyi Wanjiru', '0722410556');
        TransactionPin::set($akinyi, '4826');
        $orders = app(RampOrders::class);
        $o = $orders->confirm($orders->topUp($akinyi, '1000'));
        while (! $o->final()) {
            $o = $orders->refresh($o);
        }

        $this->actingAs($juma)->get('/requests')->assertOk()->assertSee('Request money')->assertDontSee('Coming soon');
        $this->postJson('/requests', ['to' => '@'.Handle::for($akinyi), 'amount' => '600', 'note' => 'Lunch on Friday'])->assertOk();
        $r = ApiPaymentRequest::sole();
        $this->assertSame(['personal', null, $akinyi->id, 60000, 'open'], [$r->kind, $r->api_key_id, $r->payer_user_id, $r->amount, $r->status]);

        $this->actingAs($akinyi)->getJson('/requests/data')->assertJsonPath('to_pay.0.amount', '600.00')->assertJsonPath('to_pay.0.status', 'open');
        $this->post("/r/{$r->token}/sharet", ['pin' => '4826'])->assertRedirect("/r/{$r->token}");
        $this->assertSame('paid', $r->fresh()->status);
        $this->assertSame([40000, 60000], [$orders->walletBalance($akinyi), $orders->walletBalance($juma)]);
        $this->actingAs($juma)->getJson('/requests/data')->assertJsonPath('sent.0.status', 'paid');
    }

    public function test_ask_any_phone_split_a_bill_decline_remind_and_cancel(): void
    {
        $juma = $this->person('Juma Otieno', '0712345678');
        $akinyi = $this->person('Akinyi Wanjiru', '0722410556');

        // A phone that isn't on Sharet: the request waits for them, by SMS link
        $this->actingAs($juma)->postJson('/requests', ['to' => '0733112908', 'amount' => '250'])->assertOk();
        $phoneReq = ApiPaymentRequest::latest('id')->first();
        $this->assertSame(['254733112908', null], [$phoneReq->payer_phone, $phoneReq->payer_user_id]);
        $this->get('/r/'.$phoneReq->token)->assertOk()->assertSee('Juma Otieno')->assertDontSee('Cloud9');

        // A member's number finds them
        $this->postJson('/requests', ['to' => '0722 410 556', 'amount' => '100'])->assertOk();
        $this->assertSame($akinyi->id, ApiPaymentRequest::latest('id')->first()->payer_user_id);

        $this->postJson('/requests', ['to' => '@nobody', 'amount' => '100'])->assertJsonValidationErrors('to');
        $this->postJson('/requests', ['to' => '0712345678', 'amount' => '100'])->assertJsonValidationErrors('to');   // yourself

        // Split KES 4,801 between you and two others: three shares of whole shillings, the odd shilling on the first request
        ApiPaymentRequest::query()->delete();
        $this->postJson('/requests/split', ['total' => '4801', 'people' => ['@'.Handle::for($akinyi), '0733112908'], 'note' => 'Dinner', 'include_me' => true])->assertOk();
        $split = ApiPaymentRequest::orderBy('id')->get();
        $this->assertSame([160100, 160000], $split->pluck('amount')->all());
        $this->assertCount(1, $split->pluck('group')->unique());

        $this->actingAs($akinyi)->postJson("/requests/{$split[0]->public_id}/decline")->assertOk();
        $this->assertSame('declined', $split[0]->fresh()->status);
        $this->actingAs($juma)->postJson("/requests/{$split[1]->public_id}/remind")->assertOk();
        $this->postJson("/requests/{$split[1]->public_id}/remind")->assertJsonValidationErrors('request');   // not twice in 12 hours
        $this->postJson("/requests/{$split[1]->public_id}/cancel")->assertOk();
        $this->assertSame('cancelled', $split[1]->fresh()->status);
        $this->actingAs($akinyi)->postJson("/requests/{$split[1]->public_id}/cancel")->assertNotFound();   // only the asker cancels
    }

    public function test_personal_requests_stay_out_of_the_business_api(): void
    {
        $juma = $this->person('Juma Otieno', '0712345678');
        $this->actingAs($juma)->postJson('/requests', ['to' => '0733112908', 'amount' => '250'])->assertOk();
        $r = ApiPaymentRequest::sole();
        $this->assertSame('personal', $r->kind);
        $this->assertSame(0, ApiPaymentRequest::where('kind', 'api')->count());
    }
}
