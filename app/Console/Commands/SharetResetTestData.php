<?php

namespace App\Console\Commands;

use App\Ledger\Ledger;
use App\Models\LedgerJournal;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Clears a test account before launch, the ledger's way: nothing is deleted.
 *   php artisan sharet:reset-test-data                 report only (default)
 *   php artisan sharet:reset-test-data --apply         reverse SIMULATED money, close cases, disable the account
 *   … --email=you@x --keep-account --apply             only reverse the simulated money (a real person who was simulated by mistake)
 *
 * Money that really moved (a real M-PESA receipt) is never reversed automatically: reversing it would make the
 * books say the paybill holds less than it does. Those orders are listed with the decision to take
 * (refund to the payer's phone once payouts are live, or book it as test income with a two-person manual journal).
 */
class SharetResetTestData extends Command
{
    protected $signature = 'sharet:reset-test-data {--email=v2-tester@sharet.test} {--apply : Make the changes (otherwise only report)} {--keep-account : Only reverse simulated money: keep the account and its cases}';

    protected $description = 'Report, then reverse simulated test money, close test cases and disable the test account';

    public function handle(Ledger $ledger): int
    {
        $user = User::withTrashed()->where('email', $this->option('email'))->first();
        if (! $user) {
            $this->info('No test account '.$this->option('email').'. Nothing to do.');

            return self::SUCCESS;
        }

        $orders = RampOrder::where('user_id', $user->id)->orderBy('id')->get();
        $rows = [];
        $simulated = [];
        $toMark = [];   // simulated orders whose money is (or will be) fully reversed: shown to nobody as a transaction
        foreach ($orders as $o) {
            $rails = RailRequest::whereIn('id', array_filter([$o->collect_request_id, $o->payout_request_id]))->get();
            $real = $rails->contains(fn ($r) => $r->rail !== 'simulator' && $r->status === 'succeeded');
            $journals = LedgerJournal::where('source_type', $o->getMorphClass())->where('source_id', $o->id)->whereNull('reverses_id')->orderByDesc('id')->get()   // newest first: a refund is undone before the payment it refunded
                ->reject(fn ($j) => LedgerJournal::where('reverses_id', $j->id)->exists());
            $real || $simulated = array_merge($simulated, $journals->all());
            $posted = LedgerJournal::where('source_type', $o->getMorphClass())->where('source_id', $o->id)->whereNull('reverses_id')->exists();
            if (! $real && $posted && $o->status !== 'reversed') {
                $toMark[] = $o;
            }
            $rows[] = [$o->id, $o->status, \App\Ledger\Money::format($o->total), $rails->pluck('rail')->unique()->implode('+') ?: '—',
                $real ? '<comment>REAL: decide (refund or test-income journal)</comment>' : ($journals->count() ? 'reverse '.$journals->count().' journal(s)' : ($posted && $o->status !== 'reversed' ? 'already reversed: mark as reversed' : 'nothing to do'))];
        }
        $cases = SupportCase::where('user_id', $user->id)->where('status', '!=', 'closed')->get();

        $this->table(['Order', 'Status', 'Total', 'Rails', 'Action'], $rows ?: [['—', '', '', '', 'no orders']]);
        $this->line("Open support cases: {$cases->count()} · Account: ".($user->trashed() ? 'already disabled' : 'active').' · Wallet: '.\App\Ledger\Money::format($ledger->account('2100', $user)->available()));

        if (! $this->option('apply')) {
            $this->warn('Report only. Run again with --apply to make these changes.');

            return self::SUCCESS;
        }

        foreach ($simulated as $j) {
            $ledger->reverse($j, 'Test data reset before launch', null);
        }
        foreach ($toMark as $o) {
            $o->forceFill(['status' => 'reversed'])->saveQuietly();   // quietly: no SMS or receipt for undoing test money
        }
        if ($this->option('keep-account')) {
            $this->info('Reversed '.count($simulated).' simulated journal(s), marked '.count($toMark).' order(s) reversed. Account kept. Wallet now: '.\App\Ledger\Money::format($ledger->account('2100', $user)->available()).'. Run php artisan ledger:check next.');

            return self::SUCCESS;
        }
        $cases->each(fn ($c) => $c->forceFill(['status' => 'closed'])->save());
        $user->forceFill(['status' => false])->saveQuietly();
        $user->trashed() || $user->delete();
        $this->info('Reversed '.count($simulated).' simulated journal(s), closed '.$cases->count().' case(s), disabled the account. Run php artisan ledger:check next.');

        return self::SUCCESS;
    }
}
