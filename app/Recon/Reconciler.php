<?php

namespace App\Recon;

use App\Integrations\Contracts\ProvidesStatements;
use App\Integrations\RailResult;
use App\Integrations\Rails;
use App\Ledger\Ledger;
use App\Ledger\Money;
use App\Models\LedgerJournal;
use App\Models\RailRequest;
use App\Models\ReconDay;
use App\Models\StatementLine;
use App\Models\User;
use App\Ramp\RampOrders;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Daily reconciliation (plan section 09, U3): every line on a provider's statement must match a ledger entry, and every
 * ledger entry for that rail must appear on the statement. Breaks are fixed from the statement (a missed callback),
 * parked in suspense (money that isn't ours to place yet), or explained. A day is signed off by two different people.
 */
class Reconciler
{
    private const JOURNAL_FOR_ROLE = ['collect' => 'collected', 'payout' => 'paid', 'refund' => 'refunded'];

    public function __construct(private Rails $rails, private Ledger $ledger, private RampOrders $orders) {}

    /** Import the statement and match every open line. Safe to run again: lines are stored once, matches only improve. */
    public function run(string $rail, string $day): ReconDay
    {
        $adapter = $this->rails->for($rail);
        if (! $adapter instanceof ProvidesStatements) {
            throw new \RuntimeException("{$rail} cannot give statements yet.");
        }

        foreach ($adapter->statement($day) as $line) {
            $request = $line['provider_reference']
                ? RailRequest::where('rail', $rail)->where('provider_reference', $line['provider_reference'])->first()
                : null;
            StatementLine::firstOrCreate(['rail' => $rail, 'line_ref' => $line['line_ref']], [
                'day' => $day, 'direction' => $line['direction'], 'amount' => $line['amount'], 'currency' => config('ledger.currency'),
                'party' => $line['party'], 'receipt' => $line['receipt'], 'occurred_at' => $line['occurred_at'], 'raw' => $line['raw'] ?? null,
                'rail_request_id' => $request?->id, 'status' => 'unknown',
            ]);
        }

        StatementLine::where('rail', $rail)->whereDate('day', $day)->whereIn('status', StatementLine::OPEN)->each(fn ($l) => $this->match($l));

        return $this->summarise($rail, $day);
    }

    /** A line the provider paid but we never booked (the callback never arrived): book it from the statement. */
    public function fixFromStatement(StatementLine $line, User $by): StatementLine
    {
        if ($line->status !== 'missed') {
            throw ValidationException::withMessages(['line' => 'Only lines that are on the statement but not booked can be fixed this way.']);
        }
        $request = RailRequest::findOrFail($line->rail_request_id);
        if ($request->final() && $request->status !== 'succeeded') {
            $line->update(['note' => "Our record says {$request->status}; the provider says paid. Escalated: needs a person to decide."]);
            throw ValidationException::withMessages(['line' => $line->note]);
        }

        $this->orders->apply($request, RailResult::succeeded($line->receipt, $line->line_ref));
        $this->match($line->fresh());
        $line->refresh();
        if ($line->status === 'matched') {
            $line->update(['status' => 'fixed', 'resolved_by' => $by->id, 'resolved_at' => now(), 'note' => 'Booked from the statement (callback never arrived).']);
        }
        $this->summarise($line->rail, $line->day->toDateString());

        return $line->fresh();
    }

    /** Money the provider moved that matches nothing of ours: record it in suspense so the books equal reality. */
    public function park(StatementLine $line, User $by, ?string $note = null): StatementLine
    {
        return DB::transaction(function () use ($line, $by, $note) {
            $l = StatementLine::whereKey($line->id)->lockForUpdate()->firstOrFail();
            if ($l->status !== 'unknown') {
                throw ValidationException::withMessages(['line' => 'Only unidentified lines can be parked in suspense.']);
            }
            $rail = $this->ledger->account($this->rails->account($l->rail, $l->direction === 'in' ? 'collect' : 'payout'));
            $legs = $l->direction === 'in'
                ? [Ledger::debit($rail, $l->amount), Ledger::credit($this->ledger->account('2900'), $l->amount)]
                : [Ledger::debit($this->ledger->account('2910'), $l->amount), Ledger::credit($rail, $l->amount)];
            $journal = $this->ledger->post("suspense:{$l->rail}:{$l->line_ref}", 'suspense', $legs, [
                'memo' => ($l->direction === 'in' ? 'Unidentified money in from ' : 'Unexplained money out to ').$l->party.' · '.$l->receipt,
                'created_by' => $by->id,
            ]);
            $l->update(['status' => 'parked', 'journal_id' => $journal->id, 'resolved_by' => $by->id, 'resolved_at' => now(),
                'note' => $note ?: ($l->direction === 'in' ? 'In 2900 until the sender is identified, then credited or refunded.' : 'In 2910 until explained.')]);
            $this->summarise($l->rail, $l->day->toDateString());

            return $l;
        });
    }

    /** The maker: says the day is complete. Re-imports the statement first, so nothing new slips past. No open breaks allowed. */
    public function prepare(ReconDay $day, User $by): ReconDay
    {
        $this->run($day->rail, $day->day->toDateString());

        return DB::transaction(function () use ($day, $by) {
            $d = ReconDay::whereKey($day->id)->lockForUpdate()->firstOrFail();
            if ($d->status !== 'open') {
                throw ValidationException::withMessages(['day' => 'This day is already '.str_replace('_', ' ', $d->status).'.']);
            }
            if ($d->openBreaks() > 0) {
                throw ValidationException::withMessages(['day' => 'Fix or park every break before preparing the day.']);
            }
            $d->update(['status' => 'prepared', 'prepared_by' => $by->id, 'prepared_at' => now()]);

            return $d;
        });
    }

    /** The checker: a different person signs the day off, after one more import of the statement. */
    public function signOff(ReconDay $day, User $by): ReconDay
    {
        $this->run($day->rail, $day->day->toDateString());

        return DB::transaction(function () use ($day, $by) {
            $d = ReconDay::whereKey($day->id)->lockForUpdate()->firstOrFail();
            if ($d->status !== 'prepared') {
                throw ValidationException::withMessages(['day' => 'The day must be prepared before it is signed off.']);
            }
            if ($d->prepared_by === $by->id) {
                throw ValidationException::withMessages(['day' => 'A different person must sign off: you prepared this day.']);
            }
            if ($d->openBreaks() > 0) {
                throw ValidationException::withMessages(['day' => 'New breaks appeared after it was prepared. Fix them first.']);
            }
            $d->update(['status' => 'signed_off', 'signed_off_by' => $by->id, 'signed_off_at' => now()]);

            return $d;
        });
    }

    private function match(StatementLine $line): void
    {
        $request = $line->rail_request_id ? RailRequest::find($line->rail_request_id) : null;
        if (! $request) {
            $line->update(['status' => 'unknown']);

            return;
        }
        if ($request->amount !== $line->amount) {
            $line->update(['status' => 'amount', 'note' => 'Statement '.Money::format($line->amount).', our request '.Money::format($request->amount).'.']);

            return;
        }
        $role = $request->meta['role'] ?? null;
        $journal = LedgerJournal::where('idempotency_key', "ramp:{$request->meta['order']}:".(self::JOURNAL_FOR_ROLE[$role] ?? '-'))->first();
        $line->update($journal ? ['status' => 'matched', 'journal_id' => $journal->id] : ['status' => 'missed']);
    }

    private function summarise(string $rail, string $day): ReconDay
    {
        $lines = StatementLine::where('rail', $rail)->whereDate('day', $day)->get();
        $onStatement = $lines->pluck('rail_request_id')->filter()->all();
        $booked = LedgerJournal::whereIn('type', ['ramp_collect', 'ramp_payout', 'ramp_refund'])->whereDate('posted_at', $day)->get();
        $notOnStatement = $booked->filter(function (LedgerJournal $j) use ($rail, $onStatement) {
            $role = ['ramp_collect' => 'collect', 'ramp_payout' => 'payout', 'ramp_refund' => 'refund'][$j->type];
            $uuid = explode(':', $j->idempotency_key)[1] ?? '';
            $req = RailRequest::where('reference', "ramp:{$uuid}:{$role}")->first();

            return $req && $req->rail === $rail && ! in_array($req->id, $onStatement, true);
        })->count();

        $day = ReconDay::where('rail', $rail)->whereDate('day', $day)->first() ?? ReconDay::create(['rail' => $rail, 'day' => $day]);
        $day->update(['last_run_at' => now(), 'summary' => [
            'lines' => $lines->count(),
            'matched' => $lines->where('status', 'matched')->count(),
            'fixed' => $lines->where('status', 'fixed')->count(),
            'parked' => $lines->where('status', 'parked')->count(),
            'breaks' => $lines->whereIn('status', StatementLine::OPEN)->count(),
            'not_on_statement' => $notOnStatement,
            'in' => Money::format($lines->where('direction', 'in')->sum('amount')),
            'out' => Money::format($lines->where('direction', 'out')->sum('amount')),
        ]]);

        return $day->fresh();
    }
}
