<?php

namespace Tests\Feature\Ledger;

use App\Ledger\Invariants;
use App\Ledger\Ledger;
use App\Ledger\LedgerException;
use App\Ledger\Money;
use App\Models\LedgerAccount;
use App\Models\LedgerJournal;
use App\Models\LedgerPosting;
use App\Models\User;
use Database\Seeders\LedgerAccountSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LedgerTest extends TestCase
{
    use RefreshDatabase;

    private Ledger $ledger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LedgerAccountSeeder::class);
        $this->ledger = app(Ledger::class);
    }

    private function acct(string $code, ?User $owner = null): LedgerAccount
    {
        return $this->ledger->account($code, $owner)->fresh();
    }

    /** Amina deposits by M-PESA: paybill up, her wallet up. */
    private function deposit(User $who, string $amount, string $key): LedgerJournal
    {
        $c = Money::cents($amount);

        return $this->ledger->post($key, 'deposit', [Ledger::debit($this->acct('1110'), $c), Ledger::credit($this->acct('2100', $who), $c)]);
    }

    private function assertRefused(string $reason, callable $fn): void
    {
        try {
            $fn();
            $this->fail("Expected the ledger to refuse with {$reason}.");
        } catch (LedgerException $e) {
            $this->assertSame($reason, $e->reason, $e->getMessage());
        }
    }

    private function snapshot(): array
    {
        return [
            LedgerJournal::count(), LedgerPosting::count(), DB::table('ledger_chain')->value('last_hash'),
            LedgerAccount::orderBy('id')->pluck('balance', 'id')->all(), LedgerAccount::orderBy('id')->pluck('held', 'id')->all(),
        ];
    }

    public function test_a_balanced_journal_moves_money_both_ways(): void
    {
        $amina = User::factory()->create();
        $j = $this->deposit($amina, '1,000.00', 'stk:TJV7Q2K81P');

        $this->assertSame(100000, $this->acct('1110')->natural());
        $this->assertSame(100000, $this->acct('2100', $amina)->natural());
        $this->assertSame(2, $j->postings()->count());
        $this->assertSame(0, (int) LedgerPosting::sum('amount'));
    }

    public function test_it_refuses_unbalanced_short_or_bad_journals_and_writes_nothing(): void
    {
        $before = $this->snapshot();
        $paybill = $this->acct('1110');
        $suspense = $this->acct('2900');

        $this->assertRefused('unbalanced', fn () => $this->ledger->post('k1', 'x', [Ledger::debit($paybill, 1000), Ledger::credit($suspense, 999)]));
        $this->assertRefused('too_few_legs', fn () => $this->ledger->post('k2', 'x', [Ledger::debit($paybill, 1000)]));
        $this->assertRefused('invalid_amount', fn () => $this->ledger->post('k3', 'x', [['account' => $paybill, 'amount' => 0], ['account' => $suspense, 'amount' => 0]]));
        $this->assertRefused('invalid_amount', fn () => $this->ledger->post('k4', 'x', [['account' => $paybill, 'amount' => 10.5], ['account' => $suspense, 'amount' => -10.5]]));
        $this->assertRefused('invalid_amount', fn () => Ledger::debit($paybill, -5));
        $this->assertRefused('unknown_account', fn () => $this->ledger->post('k5', 'x', [Ledger::debit(999999, 100), Ledger::credit($suspense, 100)]));

        $this->assertSame($before, $this->snapshot());
    }

    public function test_the_same_key_posts_once_and_a_reused_key_with_other_lines_is_refused(): void
    {
        $amina = User::factory()->create();
        $first = $this->deposit($amina, '500', 'stk:ONCE');
        $again = $this->deposit($amina, '500', 'stk:ONCE');

        $this->assertSame($first->id, $again->id);
        $this->assertSame(1, LedgerJournal::count());
        $this->assertSame(50000, $this->acct('2100', $amina)->natural());

        $this->assertRefused('idempotency_conflict', fn () => $this->deposit($amina, '501', 'stk:ONCE'));
    }

    public function test_customer_and_real_money_accounts_never_go_below_zero(): void
    {
        $amina = User::factory()->create();
        $juma = User::factory()->create();
        $this->deposit($amina, '100', 'stk:A');
        $this->acct('2100', $juma); // open Juma's wallet first so the snapshot compares money only
        $before = $this->snapshot();

        // Amina sends more than she has
        $this->assertRefused('insufficient_funds', fn () => $this->ledger->post('p2p:1', 'transfer', [
            Ledger::debit($this->acct('2100', $amina), 10001), Ledger::credit($this->acct('2100', $juma), 10001),
        ]));
        // a payout from a float that has no money
        $this->assertRefused('insufficient_funds', fn () => $this->ledger->post('b2c:1', 'payout', [
            Ledger::debit($this->acct('2150'), 500), Ledger::credit($this->acct('1120'), 500),
        ]));
        $this->assertSame($before, $this->snapshot());

        // income and expense accounts may go either way
        $this->ledger->post('fee-refund:1', 'fee_refund', [Ledger::debit($this->acct('4100'), 100), Ledger::credit($this->acct('2100', $amina), 100)]);
        $this->assertSame(-100, $this->acct('4100')->natural());
    }

    public function test_a_reversal_undoes_a_journal_once_and_cannot_be_reversed_itself(): void
    {
        $amina = User::factory()->create();
        $juma = User::factory()->create();
        $this->deposit($amina, '1000', 'stk:R');
        $send = $this->ledger->post('p2p:R', 'transfer', [
            Ledger::debit($this->acct('2100', $amina), 50000), Ledger::credit($this->acct('2100', $juma), 50000),
        ]);

        $rev = $this->ledger->reverse($send, 'sent to the wrong person');
        $this->assertSame($rev->id, $this->ledger->reverse($send, 'clicked twice')->id);
        $this->assertSame(100000, $this->acct('2100', $amina)->natural());
        $this->assertSame(0, $this->acct('2100', $juma)->natural());
        $this->assertSame($send->id, $rev->reverses_id);
        $this->assertRefused('already_reversed', fn () => $this->ledger->reverse($rev, 'undo the undo'));

        // reversing a deposit she has already spent would make her wallet negative: refused
        $spent = $this->deposit($amina, '300', 'stk:SPENT');
        $this->ledger->post('p2p:S', 'transfer', [Ledger::debit($this->acct('2100', $amina), 130000), Ledger::credit($this->acct('2100', $juma), 130000)]);
        $this->assertRefused('insufficient_funds', fn () => $this->ledger->reverse($spent, 'M-PESA reversal after spending'));
    }

    public function test_the_database_itself_refuses_to_change_or_delete_journals_and_postings(): void
    {
        $amina = User::factory()->create();
        $j = $this->deposit($amina, '10', 'stk:LOCK');

        foreach (['ledger_postings', 'ledger_journals'] as $table) {
            try {
                DB::table($table)->where('id', '>', 0)->update(['currency' => 'USD']);
                $this->fail("{$table} accepted an UPDATE");
            } catch (QueryException $e) {
                $this->assertStringContainsString('insert-only', $e->getMessage());
            }
            try {
                DB::table($table)->delete();
                $this->fail("{$table} accepted a DELETE");
            } catch (QueryException $e) {
                $this->assertStringContainsString('insert-only', $e->getMessage());
            }
        }
        $this->assertRefused('immutable', fn () => $j->update(['memo' => 'edited']));
        $this->assertRefused('immutable', fn () => $j->postings()->first()->delete());
    }

    public function test_holds_reserve_money_and_are_captured_or_released_exactly_once(): void
    {
        $amina = User::factory()->create();
        $wallet = $this->acct('2100', $amina);
        $this->deposit($amina, '1000', 'stk:H');

        $hold = $this->ledger->hold($wallet, 32300, 'withdrawal:W1', 'withdraw 300 + fee + excise');
        $this->assertSame($hold->id, $this->ledger->hold($wallet, 32300, 'withdrawal:W1')->id);
        $this->assertSame(67700, $wallet->fresh()->available());

        // held money cannot be spent elsewhere
        $this->assertRefused('insufficient_funds', fn () => $this->ledger->hold($wallet, 70000, 'withdrawal:W2'));
        $this->assertRefused('insufficient_funds', fn () => $this->ledger->post('p2p:H', 'transfer', [
            Ledger::debit($wallet, 70000), Ledger::credit($this->acct('2100', User::factory()->create()), 70000),
        ]));

        // the payout succeeds: hold captured, money leaves, fee and excise booked
        $this->ledger->post('float:H', 'float_topup', [Ledger::debit($this->acct('1120'), 100000), Ledger::credit($this->acct('3100'), 100000)]);
        $j = $this->ledger->capture($hold, 'b2c:W1', 'withdrawal', [
            Ledger::debit($wallet, 32300),
            Ledger::credit($this->acct('1120'), 30000),
            Ledger::credit($this->acct('4100'), 2000),
            Ledger::credit($this->acct('2300'), 300),
        ]);
        $this->assertSame($j->id, $this->ledger->capture($hold, 'b2c:W1', 'withdrawal', [
            Ledger::debit($wallet, 32300), Ledger::credit($this->acct('1120'), 30000), Ledger::credit($this->acct('4100'), 2000), Ledger::credit($this->acct('2300'), 300),
        ])->id);
        $this->assertSame('captured', $hold->fresh()->status);
        $this->assertSame(67700, $wallet->fresh()->natural());
        $this->assertSame(0, $wallet->fresh()->held);

        // a second hold that fails is released and gives the money back
        $h2 = $this->ledger->hold($wallet, 10000, 'withdrawal:W3');
        $this->ledger->release($h2);
        $this->ledger->release($h2);
        $this->assertSame(67700, $wallet->fresh()->available());
        $this->assertRefused('hold_not_open', fn () => $this->ledger->capture($h2, 'b2c:W3', 'withdrawal', [Ledger::debit($wallet, 100), Ledger::credit($this->acct('1120'), 100)]));
    }

    public function test_currency_and_closed_accounts_are_enforced(): void
    {
        $amina = User::factory()->create();
        $usd = $this->ledger->account('2100', $amina, 'USD');
        $this->assertRefused('currency_mismatch', fn () => $this->ledger->post('fx:1', 'x', [Ledger::debit($this->acct('1110'), 100), Ledger::credit($usd, 100)]));

        $wallet = $this->acct('2100', $amina);
        $wallet->forceFill(['is_active' => false])->save();
        $this->assertRefused('inactive_account', fn () => $this->deposit($amina, '1', 'stk:CLOSED'));
    }

    public function test_the_report_journeys_balance_and_pass_every_check(): void
    {
        $amina = User::factory()->create();
        $juma = User::factory()->create();
        $L = $this->ledger;

        $L->post('capital', 'capital', [Ledger::debit($this->acct('1220'), Money::cents('50,000')), Ledger::credit($this->acct('3100'), Money::cents('50,000'))]);
        $L->post('float', 'float_topup', [Ledger::debit($this->acct('1120'), Money::cents('10,000')), Ledger::credit($this->acct('1220'), Money::cents('10,000'))]);
        // J1 deposit 1,000 and the M-PESA charge
        $this->deposit($amina, '1,000', 'stk:J1');
        $L->post('charge:J1', 'rail_charge', [Ledger::debit($this->acct('5100'), 500), Ledger::credit($this->acct('1110'), 500)]);
        // J2 free transfer 500
        $L->post('p2p:J2', 'transfer', [Ledger::debit($this->acct('2100', $amina), 50000), Ledger::credit($this->acct('2100', $juma), 50000)]);
        // J3 withdrawal 300 + 20 fee + 3 excise
        $hold = $L->hold($this->acct('2100', $amina), 32300, 'w:J3');
        $L->capture($hold, 'b2c:J3', 'withdrawal', [
            Ledger::debit($this->acct('2100', $amina), 32300), Ledger::credit($this->acct('1120'), 30000),
            Ledger::credit($this->acct('4100'), 2000), Ledger::credit($this->acct('2300'), 300),
        ]);
        // J6 sweep 995 to the trust account through in-transit
        $L->post('sweep:J6a', 'sweep', [Ledger::debit($this->acct('1150'), 99500), Ledger::credit($this->acct('1110'), 99500)]);
        $L->post('sweep:J6b', 'sweep', [Ledger::debit($this->acct('1210'), 99500), Ledger::credit($this->acct('1150'), 99500)]);

        $this->assertSame(17700, $this->acct('2100', $amina)->natural());
        $this->assertSame(50000, $this->acct('2100', $juma)->natural());
        $this->assertSame(0, (int) LedgerPosting::sum('amount'));

        $results = app(Invariants::class)->run();
        $this->assertTrue(Invariants::passed($results), json_encode($results, JSON_PRETTY_PRINT));
        $this->artisan('ledger:check')->assertSuccessful();
    }

    public function test_the_check_catches_rows_written_around_the_ledger(): void
    {
        $amina = User::factory()->create();
        $this->deposit($amina, '100', 'stk:T1');
        $this->deposit($amina, '200', 'stk:T2');
        $this->artisan('ledger:check')->assertSuccessful();

        // someone inserts a posting directly: money from nowhere
        $journal = LedgerJournal::first();
        DB::table('ledger_postings')->insert(['journal_id' => $journal->id, 'account_id' => $this->acct('2100', $amina)->id, 'amount' => -50000, 'currency' => 'KES', 'created_at' => now()]);
        $failed = collect(app(Invariants::class)->run())->where('status', 'fail')->pluck('check');
        $this->assertContains('Every journal balances', $failed);
        $this->assertContains('All postings sum to zero', $failed);
        $this->assertContains('Account balances match their postings', $failed);
        $this->assertContains('Journal hash chain intact', $failed);
        $this->artisan('ledger:check')->assertFailed();
    }

    public function test_a_journal_slipped_into_the_chain_is_detected(): void
    {
        $amina = User::factory()->create();
        $this->deposit($amina, '100', 'stk:C1');
        DB::table('ledger_journals')->insert([
            'uuid' => 'fake-uuid', 'idempotency_key' => 'fake', 'payload_hash' => str_repeat('0', 64), 'type' => 'deposit', 'currency' => 'KES',
            'hash' => str_repeat('a', 64), 'prev_hash' => DB::table('ledger_chain')->value('last_hash'), 'posted_at' => now()->format('Y-m-d H:i:s.u'),
        ]);

        $failed = collect(app(Invariants::class)->run())->where('status', 'fail')->pluck('check');
        $this->assertContains('Journal hash chain intact', $failed);
        $this->assertContains('Every journal has two or more lines', $failed);
    }

    public function test_safeguarding_fails_when_customer_money_is_not_covered(): void
    {
        $amina = User::factory()->create();
        // a wallet credited from Sharet's own equity with no real money behind it
        $this->ledger->post('gift', 'promo', [Ledger::debit($this->acct('3100'), 10000), Ledger::credit($this->acct('2100', $amina), 10000)]);

        $failed = collect(app(Invariants::class)->run())->where('status', 'fail')->pluck('check');
        $this->assertContains('Safeguarded money covers what customers are owed', $failed);
    }
}
