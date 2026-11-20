<?php

namespace Tests\Feature\Api;

use App\Api\Tariffs;
use App\Models\ApiKey;
use App\Models\Company;
use App\Models\Tariff;
use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Business tariff tables: transaction bands × monthly volume tiers, versioned (docs/v2/INTEGRATION.md §7). */
class TariffsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LedgerAccountSeeder::class, RolePermissionSeeder::class]);
    }

    private function wallet(bool $group = false): User
    {
        $c = Company::create(['name' => 'scharge', 'verified_at' => now()]);
        $c->forceFill(['is_group_company' => $group])->save();

        return $c->walletUser();
    }

    private function paidOut(User $u, int $cents): void
    {
        DB::table('ramp_orders')->insert(['uuid' => (string) Str::uuid(), 'user_id' => $u->id, 'rail' => 'simulator', 'destination_type' => 'phone',
            'destination' => json_encode(['phone' => '254722000904']), 'payer_phone' => '', 'funding' => 'wallet', 'amount' => $cents, 'fee' => 0, 'excise' => 0,
            'rounding' => 0, 'total' => $cents, 'currency' => 'KES', 'status' => 'paid', 'quote_expires_at' => now(), 'timeline' => '[]', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_fees_come_from_the_band_with_excise_and_sharet_is_always_free(): void
    {
        $w = $this->wallet();
        $t = app(Tariffs::class);

        $bank = $t->quote($w, 'bank', 500_000);              // KES 5,000 to a bank: KES 50 band (sample)
        $this->assertSame(5_000, $bank['fee']);
        $this->assertSame(750, $bank['excise']);              // 15% of the fee
        $this->assertSame(50, $bank['rounding']);             // 5,057.50 up to the next whole shilling
        $this->assertSame(505_800, $bank['total']);
        $this->assertSame(1, $bank['tariff_version']);

        $big = $t->quote($w, 'bank', 300_000_000);           // KES 3M: 0.01% capped at KES 500
        $this->assertSame(30_000, $big['fee']);
        $huge = $t->quote($w, 'bank', 900_000_000);
        $this->assertSame(50_000, $huge['fee']);

        $this->assertSame(0, $t->quote($w, 'sharet', 900_000_000)['fee']);
        $this->assertSame(800, $t->quote($w, 'mpesa', 50_000)['fee']);   // KES 500 to M-PESA: the band's fixed fee
    }

    public function test_monthly_volume_tiers_discount_outside_fees_and_sharet_companies_go_one_tier_up(): void
    {
        $w = $this->wallet();
        $t = app(Tariffs::class);
        $this->assertSame(0, $t->quote($w, 'bank', 500_000)['discount_bp']);

        $this->paidOut($w, 150_000_000);                     // KES 1.5M this month
        $q = $t->quote($w, 'bank', 500_000);
        $this->assertSame(2500, $q['discount_bp']);
        $this->assertSame(3_750, $q['fee']);                 // KES 50 less 25%

        $this->paidOut($w, 900_000_000);                     // KES 10.5M
        $this->assertSame(4000, $t->quote($w, 'bank', 500_000)['discount_bp']);

        $group = Company::create(['name' => 'Breve', 'verified_at' => now()]);
        $group->forceFill(['is_group_company' => true])->save();
        $this->assertSame(2500, $t->quote($group->walletUser(), 'bank', 500_000)['discount_bp'], 'no volume yet, but one tier up');
    }

    public function test_a_new_version_applies_from_its_date_and_payments_record_it(): void
    {
        $w = $this->wallet();
        Tariff::create(['version' => 2, 'effective_from' => now()->addDay(), 'excise_bp' => 1500, 'volume_tiers' => [['from' => 0, 'discount_bp' => 0]],
            'bands' => ['payout_bank' => [['up_to' => null, 'fixed' => 2_000, 'percent_bp' => 0, 'cap' => null]], 'payout_mobile' => [['up_to' => null, 'fixed' => 1_000, 'percent_bp' => 0, 'cap' => null]]]]);
        $this->assertSame(5_000, app(Tariffs::class)->quote($w, 'bank', 500_000)['fee'], 'not yet in force');

        $this->travel(2)->days();
        $this->assertSame(2_000, app(Tariffs::class)->quote($w, 'bank', 500_000)['fee']);

        $k = new ApiKey(['user_id' => User::factory()->create(['name' => 'Peter Kamau', 'company_id' => $w->company_id])->id, 'company_id' => $w->company_id,
            'name' => 'k', 'environment' => 'test', 'scopes' => ['read', 'payout'], 'enabled' => true]);
        $plain = $k->issue();
        $k->save();
        $this->postJson('/api/v1/payments', ['amount' => '5000', 'to' => ['type' => 'bank', 'bank' => 'equity', 'account' => '0123456789']],
            ['Authorization' => "Bearer $plain", 'Idempotency-Key' => 'tariff-v2-1'])->assertCreated()
            ->assertJsonPath('fee', '23.00')->assertJsonPath('tariff.version', 2);   // 20 + 15% excise, rounded up
    }

    public function test_only_staff_with_the_system_permission_and_a_fresh_password_save_a_version(): void
    {
        $admin = User::factory()->create(['name' => 'Dennis Kibett']);
        $admin->assignRole('super_admin');
        $form = ['effective_from' => now()->addHour()->format('Y-m-d H:i'), 'excise_percent' => '15', 'notes' => 'Cashia rates',
            'tiers' => [['from' => '0', 'discount' => '0'], ['from' => '2000000', 'discount' => '30']],
            'bands' => [
                'collect_sharet' => [['up_to' => '', 'fixed' => '0']], 'collect_mpesa' => [['up_to' => '', 'fixed' => '0']],
                'payout_mobile' => [['up_to' => '1000', 'fixed' => '10'], ['up_to' => '', 'fixed' => '25']],
                'payout_bank' => [['up_to' => '', 'fixed' => '40', 'percent' => '0', 'cap' => '']],
            ]];

        $this->actingAs(User::factory()->create(['name' => 'Juma Jones']))->get('/admin/pricing')->assertForbidden();
        $this->actingAs($admin)->get('/admin/pricing')->assertOk()->assertSee('Table 1')->assertSee('version 1');
        $this->actingAs($admin)->post('/admin/pricing', $form)->assertRedirect(route('password.confirm'));

        $ok = $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()]);
        $bad = $form;
        $bad['bands']['payout_bank'] = [['up_to' => '5000', 'fixed' => '40']];
        $ok->post('/admin/pricing', $bad)->assertSessionHasErrors('bands.payout_bank');

        $ok->post('/admin/pricing', $form)->assertSessionHas('status');
        $v2 = Tariff::where('version', 2)->first();
        $this->assertSame(4_000, $v2->bands['payout_bank'][0]['fixed']);
        $this->assertSame(200_000_000, $v2->volume_tiers[1]['from']);
        $this->assertSame(3000, $v2->volume_tiers[1]['discount_bp']);
        $this->assertSame(0, $v2->bands['sharet'][0]['fixed'], 'Sharet ↔ Sharet stays free');
    }
}
