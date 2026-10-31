<?php

namespace Tests\Feature\Ledger;

use App\Ledger\Ledger;
use App\Models\LedgerAccount;
use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LedgerPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_with_reconciliation_access_see_the_ledger_and_others_do_not(): void
    {
        $this->seed(LedgerAccountSeeder::class);
        $ledger = app(Ledger::class);
        $amina = User::factory()->create();
        $j = $ledger->post('stk:PAGE', 'deposit', [Ledger::debit($ledger->account('1110'), 25000), Ledger::credit($ledger->account('2100', $amina), 25000)], ['memo' => 'STK deposit']);

        $staff = User::factory()->create();
        $staff->givePermissionTo(Permission::findOrCreate('admin.reconciliation', 'web'));
        $this->actingAs($staff);

        $this->get('/ledger')->assertOk()->assertSee('Ledger');
        $this->getJson('/ledger?search=1110')->assertOk()
            ->assertJsonPath('data.0.code', '1110')->assertJsonPath('data.0.balance', '250.00')
            ->assertJsonPath('stats.balanced', 'Balanced');

        $wallet = LedgerAccount::where('code', '2100')->first();
        $this->get("/ledger/accounts/{$wallet->id}")->assertOk();
        $this->getJson("/ledger/accounts/{$wallet->id}")->assertOk()
            ->assertJsonPath('data.0.credit', '250.00')->assertJsonPath('stats.balance', '250.00');
        $this->getJson("/ledger/journals/{$j->uuid}")->assertOk()
            ->assertJsonPath('memo', 'STK deposit')->assertJsonPath('total', '250.00')->assertJsonCount(2, 'lines');

        $this->actingAs(User::factory()->create())->get('/ledger')->assertForbidden();
    }
}
