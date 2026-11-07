<?php

namespace Tests\Feature\Api;

use App\Ledger\Ledger;
use App\Models\ApiKey;
use App\Models\ApiPaymentRequest;
use App\Models\ApiSettlement;
use App\Models\Company;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use App\Support\TransactionPin;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** API v1: split settlement on payment requests and batch settlements with a second approver (INTEGRATION.md §4, §5). */
class V1SettlementsTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        config(['rails.default' => 'simulator', 'rails.simulator.delay' => 0, 'api.second_approval_cents' => 100_000,   // KES 1,000 for the tests
            'security.lockdown_file' => sys_get_temp_dir().'/apiset-lock-'.uniqid().'.json']);
        Lockdown::forget();
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
        Http::fake();
        $this->company = Company::create(['name' => 'scharge', 'verified_at' => now()]);
    }

    private function person(string $name, string $handle, string $phone, string $balance = '0', string $kyc = 'standard'): User
    {
        $u = User::factory()->create(['name' => $name, 'handle' => $handle, 'phone' => $phone]);
        $u->forceFill(['kyc_level' => $kyc])->save();
        TransactionPin::set($u, '4826');
        if ($balance !== '0') {
            $o = app(RampOrders::class)->confirm(app(RampOrders::class)->topUp($u, $balance));
            while (! $o->final()) {
                $o = app(RampOrders::class)->refresh($o);
            }
        }

        return $u;
    }

    private function staff(string $name, string $handle, string $phone, string $balance = '0', string $kyc = 'standard'): User
    {
        $u = $this->person($name, $handle, $phone, $balance, $kyc);
        $u->forceFill(['company_id' => $this->company->id])->save();
        $u->assignRole('techie');

        return $u;
    }

    private function key(User $u, string $env = 'live', array $scopes = ['read', 'collect', 'payout']): array
    {
        $k = new ApiKey(['user_id' => $u->id, 'company_id' => $u->company_id, 'name' => 'scharge', 'environment' => $env, 'scopes' => $scopes, 'enabled' => true]);
        $plain = $k->issue();
        $k->save();

        return ['Authorization' => "Bearer $plain"];
    }

    private function balance(User $u): int
    {
        return app(Ledger::class)->account('2100', $u)->available();
    }

    public function test_split_rent_pays_landlord_and_agent_together_and_the_business_keeps_the_rest(): void
    {
        $biz = $this->staff('Peter Kamau', 'scharge', '0711000111');
        $tenant = $this->person('Akinyi Wekesa', 'akinyi', '0712345678', '50000', 'basic');
        $landlord = $this->person('Amina Otieno', 'amina', '0713000222');
        $agent = $this->person('Kevin Mutua', 'kevin', '0714000333');

        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '35000', 'payer' => ['handle' => '@akinyi'], 'channels' => ['sharet'], 'splits' => [
            ['to' => ['sharet_id' => $landlord->sharet_id], 'percent' => 90], ['to' => ['handle' => '@kevin'], 'percent' => 8], ['to' => 'self', 'percent' => 2],
        ]], $this->key($biz) + ['Idempotency-Key' => 'rent-split-1'])->assertCreated()
            ->assertJsonPath('splits.0.sharet_id', $landlord->sharet_id)->assertJsonPath('splits.0.amount', '31500.00')->assertJsonPath('splits.1.amount', '2800.00')->json();

        $token = basename(parse_url($r['pay_url'], PHP_URL_PATH));
        $this->actingAs($tenant)->post("/r/$token/sharet", ['pin' => '4826'])->assertRedirect("/r/$token");

        $this->assertSame(3_150_000, $this->balance($landlord));
        $this->assertSame(280_000, $this->balance($agent));
        $this->assertSame(70_000, $this->balance($biz));
        $this->assertSame(1_500_000, $this->balance($tenant));
        $this->assertSame('settled', ApiPaymentRequest::first()->split_status);
    }

    public function test_splits_are_checked_when_the_request_is_made(): void
    {
        $biz = $this->staff('Peter Kamau', 'scharge', '0711000111');
        $landlord = $this->person('Amina Otieno', 'amina', '0713000222');
        $h = $this->key($biz, 'test');
        $bad = [
            [['to' => ['type' => 'mpesa', 'phone' => '0722000904'], 'percent' => 100]],           // outside leg
            [['to' => ['handle' => '@amina'], 'percent' => 80]],                                   // doesn't add up, no "self"
            [['to' => ['handle' => '@amina'], 'percent' => 60], ['to' => ['sharet_id' => $landlord->sharet_id], 'percent' => 40]],   // twice
            [['to' => ['handle' => '@amina'], 'percent' => 90], ['to' => 'self', 'percent' => 20]],   // over 100
        ];
        foreach ($bad as $i => $splits) {
            $this->postJson('/api/v1/payment-requests', ['amount' => '1000', 'splits' => $splits], $h + ['Idempotency-Key' => "bad-split-$i"])->assertStatus(422);
        }
        $this->assertDatabaseCount('api_payment_requests', 0);
    }

    public function test_a_failed_split_keeps_the_money_with_the_business_until_it_is_retried(): void
    {
        $biz = $this->staff('Peter Kamau', 'scharge', '0711000111', '0', 'none');   // can't send yet, so the split fails
        $tenant = $this->person('Akinyi Wekesa', 'akinyi', '0712345678', '5000', 'basic');
        $landlord = $this->person('Amina Otieno', 'amina', '0713000222');
        $r = $this->postJson('/api/v1/payment-requests', ['amount' => '2000', 'channels' => ['sharet'], 'splits' => [['to' => ['handle' => '@amina'], 'percent' => 100]]],
            $this->key($biz) + ['Idempotency-Key' => 'split-fail-1'])->json();
        $this->actingAs($tenant)->post('/r/'.basename(parse_url($r['pay_url'], PHP_URL_PATH)).'/sharet', ['pin' => '4826']);

        $req = ApiPaymentRequest::first();
        $this->assertSame('paid', $req->status);
        $this->assertSame('failed', $req->split_status);
        $this->assertSame(200_000, $this->balance($biz), 'all or nothing: the money stays with the business');
        $this->assertSame(0, $this->balance($landlord));

        $biz->forceFill(['kyc_level' => 'standard'])->save();
        $this->postJson('/api/v1/payment-requests/'.$r['id'].'/settle', [], $this->key($biz))->assertOk()->assertJsonPath('split_status', 'settled');
        $this->assertSame(200_000, $this->balance($landlord));
        $this->assertSame(0, $this->balance($biz));
    }

    public function test_sandbox_batches_pay_every_item_and_a_bad_item_stops_the_whole_batch(): void
    {
        $biz = $this->staff('Peter Kamau', 'scharge', '0711000111');
        $this->person('Amina Otieno', 'amina', '0713000222');
        $h = $this->key($biz, 'test');

        $this->postJson('/api/v1/settlements', ['items' => [['amount' => '500', 'to' => ['handle' => '@amina']], ['amount' => '100', 'to' => ['type' => 'mpesa', 'phone' => '123']]]],
            $h + ['Idempotency-Key' => 'batch-bad-1'])->assertStatus(422)->assertJsonPath('errors', fn ($e) => isset($e['items.1']));
        $this->assertDatabaseCount('api_settlements', 0);

        $this->postJson('/api/v1/settlements', ['reference' => 'Landlords Nov', 'items' => [
            ['amount' => '500', 'to' => ['handle' => '@amina']], ['amount' => '300', 'to' => ['type' => 'mpesa', 'phone' => '0722000904']], ['amount' => '200', 'to' => ['type' => 'mpesa', 'phone' => '0700000002']],
        ]], $h + ['Idempotency-Key' => 'batch-ok-1'])->assertCreated()
            ->assertJsonPath('status', 'partially_failed')->assertJsonPath('paid', 2)->assertJsonPath('failed', 1)->assertJsonPath('total', '1000.00');
        $this->assertDatabaseCount('ramp_orders', 0);
    }

    public function test_large_live_batches_wait_for_a_second_person_from_the_same_business(): void
    {
        $owner = $this->staff('Peter Kamau', 'scharge', '0711000111', '5000');
        $colleague = $this->staff('Grace Wanjiru', 'grace', '0715000444');
        $outsider = $this->person('Otieno Ouma', 'otieno', '0716000555');
        $outsider->assignRole('techie');
        $amina = $this->person('Amina Otieno', 'amina', '0713000222');
        $kevin = $this->person('Kevin Mutua', 'kevin', '0714000333');

        $small = $this->postJson('/api/v1/settlements', ['items' => [['amount' => '400', 'to' => ['handle' => '@amina']]]], $this->key($owner) + ['Idempotency-Key' => 'small-live-1'])
            ->assertCreated()->assertJsonPath('status', 'completed');

        $big = $this->postJson('/api/v1/settlements', ['items' => [['amount' => '1500', 'to' => ['handle' => '@amina']], ['amount' => '1000', 'to' => ['handle' => '@kevin']]]],
            $this->key($owner) + ['Idempotency-Key' => 'big-live-1'])->assertStatus(202)->assertJsonPath('status', 'awaiting_approval')->json();
        $this->assertSame(40_000, $this->balance($amina), 'nothing moved yet');
        $s = ApiSettlement::where('public_id', $big['id'])->first();

        $confirmed = ['auth.password_confirmed_at' => time()];
        $this->actingAs($owner)->withSession($confirmed)->post("/settlements/{$s->id}/approve")->assertSessionHasErrors('settlement');
        $this->actingAs($outsider)->withSession($confirmed)->post("/settlements/{$s->id}/approve")->assertSessionHasErrors('settlement');
        $this->actingAs($colleague)->get('/settlements')->assertOk()->assertSee('Approve');
        $this->flushSession();
        $this->actingAs($colleague)->post("/settlements/{$s->id}/approve")->assertRedirect(route('password.confirm'));   // password first
        $this->assertSame('awaiting_approval', $s->fresh()->status);

        $this->actingAs($colleague)->withSession($confirmed)->post("/settlements/{$s->id}/approve")->assertSessionHas('status');
        $this->assertSame('completed', $s->fresh()->status);
        $this->assertSame(190_000, $this->balance($amina));
        $this->assertSame(100_000, $this->balance($kevin));
        $this->assertSame($colleague->id, $s->fresh()->approved_by);
        $this->getJson('/api/v1/settlements/'.$big['id'], $this->key($owner))->assertJsonPath('approved_by', 'Grace Wanjiru')->assertJsonPath('paid', 2);
    }
}
