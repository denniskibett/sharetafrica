<?php

namespace App\Http\Controllers;

use App\Ledger\Money;
use App\Models\LedgerAccount;
use App\Models\LedgerJournal;
use App\Models\LedgerPosting;
use App\Support\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Staff ledger area (read-only): accounts and trial balance, one account's statement, one journal.
 * Lists answer JSON (dataTable); pages render once and never reload. Money is only posted by App\Ledger\Ledger.
 */
class LedgerController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('ledger.index');
        }

        $query = LedgerAccount::query()
            ->when($request->query('book'), fn ($q, $book) => $q->where('book', $book))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type));

        $totals = DB::table('ledger_postings')
            ->selectRaw('COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as debits, COALESCE(SUM(CASE WHEN amount < 0 THEN -amount ELSE 0 END), 0) as credits')
            ->first();
        $check = Cache::get('ledger:last-check');

        return Table::respond($request, $query, fn (LedgerAccount $a) => $a->present() + [
            'account' => ['label' => $a->label(), 'url' => route('ledger.show', $a->id)],
            'kind' => Table::pill(ucfirst($a->type), match ($a->type) {
                'asset' => 'info', 'liability' => 'warning', 'income' => 'success', 'expense' => 'error', default => 'neutral'
            }),
        ], ['code', 'name', 'owner_key'], ['code' => 'code', 'name' => 'name'], [
            'debits' => Money::format((int) $totals->debits),
            'credits' => Money::format((int) $totals->credits),
            'balanced' => (int) $totals->debits === (int) $totals->credits ? 'Balanced' : 'NOT BALANCED',
            'check' => $check ? (collect($check['results'])->contains('status', 'fail') ? 'Failed' : 'Passed').' · '.\Illuminate\Support\Carbon::parse($check['at'])->diffForHumans() : 'Not run yet',
        ], 'code');
    }

    public function show(Request $request, LedgerAccount $account)
    {
        if (! $request->expectsJson()) {
            return view('ledger.show', ['account' => $account->present() + ['label' => $account->label()]]);
        }

        $query = LedgerPosting::query()->with('journal')->where('account_id', $account->id);

        return Table::respond($request, $query, fn (LedgerPosting $p) => [
            'id' => $p->id,
            'date' => $p->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i'),
            'journal' => $p->journal->type,
            'journal_url' => route('ledger.journal', $p->journal->uuid),
            'memo' => $p->journal->memo,
            'debit' => $p->amount > 0 ? Money::format($p->amount) : '',
            'credit' => $p->amount < 0 ? Money::format(-$p->amount) : '',
        ], [], ['date' => 'id'], [
            'balance' => Money::format($account->fresh()->natural()),
            'held' => Money::format($account->held),
            'available' => Money::format($account->fresh()->available()),
            'count' => $account->postings()->count(),
        ], '-date');
    }

    public function journal(LedgerJournal $journal)
    {
        return response()->json($journal->present());
    }
}
