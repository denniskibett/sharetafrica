<?php

namespace App\Console\Commands;

use App\Ledger\Ledger;
use App\Ledger\Money;
use App\Models\LedgerJournal;
use App\Models\RailRequest;
use App\Models\RampOrder;
use Illuminate\Console\Command;

/**
 * Leaves only real money in the ledger, for every account, the ledger's way (reversals; nothing is deleted).
 *   php artisan sharet:clean-simulated                     report only
 *   php artisan sharet:clean-simulated --apply             reverse every order that moved through the simulator
 *   php artisan sharet:clean-simulated --transfers --apply also reverse Sharet-to-Sharet transfers (no rail at all)
 *
 * An order with a real M-PESA receipt (any rail other than the simulator that succeeded) is never touched. Orders are
 * undone newest first, so money passed on is pulled back before the top-up that funded it; anything the ledger refuses
 * (it would leave a wallet below zero) is skipped and listed. Undone orders get status 'reversed' (hidden from customers).
 * Accounts stay as they are.
 */
class SharetCleanSimulated extends Command
{
    protected $signature = 'sharet:clean-simulated {--apply : Make the changes (otherwise only report)} {--transfers : Also reverse Sharet-to-Sharet transfers}';

    protected $description = 'Reverse all simulated money for every account, keeping only real M-PESA money';

    public function handle(Ledger $ledger): int
    {
        $rows = [];
        $plan = [];
        foreach (RampOrder::with('user:id,email')->where('status', '!=', 'reversed')->orderByDesc('id')->get() as $o) {
            $rails = RailRequest::whereIn('id', array_filter([$o->collect_request_id, $o->payout_request_id]))->get();
            $journals = LedgerJournal::where('source_type', $o->getMorphClass())->where('source_id', $o->id)->whereNull('reverses_id')->orderByDesc('id')->get()   // newest first: a refund is undone before the payment it refunded
                ->reject(fn ($j) => LedgerJournal::where('reverses_id', $j->id)->exists());
            $posted = LedgerJournal::where('source_type', $o->getMorphClass())->where('source_id', $o->id)->whereNull('reverses_id')->exists();
            $kind = match (true) {
                $rails->contains(fn ($r) => $r->rail !== 'simulator' && $r->status === 'succeeded') => 'real',
                $rails->isEmpty() => 'transfer',
                default => 'simulated',
            };
            $undo = $posted && ($kind === 'simulated' || ($kind === 'transfer' && $this->option('transfers')));
            $undo && $plan[] = [$o, $journals];
            $rows[] = [$o->id, $o->user?->email ?? '—', $o->status, Money::format($o->total), $rails->pluck('rail')->unique()->implode('+') ?: '—',
                match (true) {
                    $kind === 'real' => 'keep (real M-PESA)',
                    ! $posted => 'nothing posted',
                    $undo => 'reverse',
                    default => 'keep (transfer: add --transfers to reverse)',
                }];
        }

        $this->table(['Order', 'Account', 'Status', 'Total', 'Rails', 'Action'], $rows ?: [['—', '', '', '', '', 'no orders']]);
        $this->line(count($plan).' order(s) to reverse.');
        if (! $this->option('apply')) {
            $this->warn('Report only. Run again with --apply to make these changes.');

            return self::SUCCESS;
        }

        $done = 0;
        $skipped = [];
        foreach ($plan as [$o, $journals]) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($ledger, $o, $journals) {
                    foreach ($journals as $j) {
                        $ledger->reverse($j, 'Simulated money removed: only real money kept', null);
                    }
                    $o->forceFill(['status' => 'reversed'])->saveQuietly();   // quietly: no SMS or receipt
                });
                $done++;
            } catch (\Throwable $e) {
                $skipped[] = "#{$o->id}: ".$e->getMessage();
            }
        }
        $this->info("Reversed {$done} order(s).");
        foreach ($skipped as $s) {
            $this->warn('Skipped '.$s);
        }
        $this->line('Run php artisan ledger:check next.');

        return $skipped ? self::FAILURE : self::SUCCESS;
    }
}
