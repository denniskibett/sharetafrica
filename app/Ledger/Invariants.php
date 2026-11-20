<?php

namespace App\Ledger;

use App\Models\LedgerAccount;
use Illuminate\Support\Facades\DB;

/**
 * The checks behind `php artisan ledger:check` (hourly). Each returns ['check', 'status' => ok|fail|warn, 'detail'].
 * They recompute everything from the raw postings, so they catch a bug in Ledger as well as a row written around it.
 */
class Invariants
{
    public function run(): array
    {
        return [
            $this->journalsBalance(),
            $this->everyJournalHasLines(),
            $this->ledgerSumsToZero(),
            $this->cachedBalancesMatch(),
            $this->holdsMatch(),
            $this->noForbiddenNegatives(),
            $this->hashChainIntact(),
            $this->safeguarding(),
            $this->staleRampOrders(),
        ];
    }

    public static function passed(array $results): bool
    {
        return collect($results)->doesntContain('status', 'fail');
    }

    private function journalsBalance(): array
    {
        $bad = DB::table('ledger_postings')->select('journal_id', 'currency')->groupBy('journal_id', 'currency')
            ->havingRaw('SUM(amount) <> 0')->pluck('journal_id');

        return $this->result('Every journal balances', $bad->isEmpty(), $bad->isEmpty() ? 'all journals sum to 0.00' : 'unbalanced journals: '.$bad->take(10)->implode(', '));
    }

    private function everyJournalHasLines(): array
    {
        $bad = DB::table('ledger_journals as j')
            ->leftJoin('ledger_postings as p', 'p.journal_id', '=', 'j.id')
            ->select('j.id')->groupBy('j.id')->havingRaw('COUNT(p.id) < 2')->pluck('j.id');

        return $this->result('Every journal has two or more lines', $bad->isEmpty(), $bad->isEmpty() ? 'ok' : 'journals with fewer than 2 lines: '.$bad->take(10)->implode(', '));
    }

    private function ledgerSumsToZero(): array
    {
        $bad = DB::table('ledger_postings')->select('currency', DB::raw('SUM(amount) as total'))->groupBy('currency')
            ->havingRaw('SUM(amount) <> 0')->get();

        return $this->result('All postings sum to zero', $bad->isEmpty(), $bad->isEmpty() ? 'debits = credits in every currency'
            : $bad->map(fn ($r) => "{$r->currency} off by ".Money::format((int) $r->total))->implode('; '));
    }

    private function cachedBalancesMatch(): array
    {
        $sums = DB::table('ledger_postings')->select('account_id', DB::raw('SUM(amount) as total'))->groupBy('account_id')->pluck('total', 'account_id');
        $bad = LedgerAccount::all()->filter(fn ($a) => (int) ($sums[$a->id] ?? 0) !== $a->balance);

        return $this->result('Account balances match their postings', $bad->isEmpty(), $bad->isEmpty() ? 'all cached balances agree'
            : $bad->take(10)->map(fn ($a) => $a->label().': cached '.Money::format($a->balance).', postings '.Money::format((int) ($sums[$a->id] ?? 0)))->implode('; '));
    }

    private function holdsMatch(): array
    {
        $open = DB::table('ledger_holds')->where('status', 'open')->select('account_id', DB::raw('SUM(amount) as total'))->groupBy('account_id')->pluck('total', 'account_id');
        $bad = LedgerAccount::all()->filter(fn ($a) => (int) ($open[$a->id] ?? 0) !== $a->held);

        return $this->result('Held amounts match open holds', $bad->isEmpty(), $bad->isEmpty() ? 'ok'
            : $bad->take(10)->map(fn ($a) => $a->label().': held '.Money::format($a->held).', open holds '.Money::format((int) ($open[$a->id] ?? 0)))->implode('; '));
    }

    private function noForbiddenNegatives(): array
    {
        $bad = LedgerAccount::where('allow_negative', false)->get()->filter(fn ($a) => $a->available() < 0);

        return $this->result('No real-money or customer account below zero', $bad->isEmpty(), $bad->isEmpty() ? 'ok'
            : $bad->take(10)->map(fn ($a) => $a->label().': available '.Money::format($a->available()))->implode('; '));
    }

    private function hashChainIntact(): array
    {
        $prev = null;
        $broken = null;
        $count = 0;
        DB::table('ledger_journals')->orderBy('id')->chunk(500, function ($journals) use (&$prev, &$broken, &$count) {
            $lines = DB::table('ledger_postings')->whereIn('journal_id', $journals->pluck('id'))->get(['journal_id', 'account_id', 'amount'])->groupBy('journal_id');
            foreach ($journals as $j) {
                $count++;
                $ls = ($lines[$j->id] ?? collect())->map(fn ($l) => ['account_id' => (int) $l->account_id, 'amount' => (int) $l->amount])->all();
                $expected = Ledger::hash($prev, $j->uuid, $j->idempotency_key, $j->type, $j->currency, $this->stamp($j->posted_at), $ls);
                if ($j->prev_hash !== $prev || ! hash_equals($expected, $j->hash)) {
                    $broken = $j->id;

                    return false;
                }
                $prev = $j->hash;
            }
        });
        $chain = DB::table('ledger_chain')->where('id', 1)->value('last_hash');
        if (! $broken && $chain !== $prev) {
            return $this->result('Journal hash chain intact', false, 'the chain head does not match the last journal');
        }

        return $this->result('Journal hash chain intact', $broken === null, $broken === null ? "{$count} journals verified" : "chain breaks at journal {$broken}");
    }

    private function safeguarding(): array
    {
        $sum = function (array $prefixes, string $type) {
            return LedgerAccount::where('type', $type)->get()
                ->filter(fn ($a) => collect($prefixes)->contains(fn ($p) => str_starts_with($a->code, $p)))
                ->sum(fn ($a) => $a->natural());
        };
        $assets = $sum(config('ledger.safeguarded_assets'), 'asset');
        $owed = $sum(config('ledger.customer_liabilities'), 'liability');

        return $this->result('Safeguarded money covers what customers are owed', $assets >= $owed,
            'safeguarded '.Money::format($assets).', owed '.Money::format($owed).', buffer '.Money::format($assets - $owed));
    }

    private function staleRampOrders(): array
    {
        $cutoff = now()->subMinutes((int) config('ledger.ramp_order_max_minutes'));
        $stale = LedgerAccount::where('code', '2200')->where('balance', '<>', 0)->where('updated_at', '<', $cutoff)->count();

        return ['check' => 'Ramp orders clear within '.config('ledger.ramp_order_max_minutes').' minutes', 'status' => $stale ? 'warn' : 'ok',
            'detail' => $stale ? "{$stale} order account(s) still hold money: check their status" : 'ok'];
    }

    private function stamp(string $postedAt): string
    {
        // databases may return "…:00" or "…:00.000000"; the hash used the 6-digit form
        return str_contains($postedAt, '.') ? str_pad($postedAt, 26, '0') : $postedAt.'.000000';
    }

    private function result(string $check, bool $ok, string $detail): array
    {
        return ['check' => $check, 'status' => $ok ? 'ok' : 'fail', 'detail' => $detail];
    }
}
