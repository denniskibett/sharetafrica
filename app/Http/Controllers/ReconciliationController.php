<?php

namespace App\Http\Controllers;

use App\Models\ReconDay;
use App\Models\StatementLine;
use App\Recon\Reconciler;
use App\Support\Table;
use Illuminate\Http\Request;

/**
 * Staff reconciliation: days per rail, one day's statement lines, and the actions that close breaks
 * (book from statement, park in suspense) and sign the day off (maker, then a different checker). JSON after first load.
 */
class ReconciliationController extends Controller
{
    public function __construct(private Reconciler $reconciler) {}

    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('reconciliation.index', ['rail' => config('rails.default')]);
        }
        $days = ReconDay::query();

        return Table::respond($request, $days, fn (ReconDay $d) => $d->present(), [], ['day' => 'day'], [
            'open_breaks' => StatementLine::whereIn('status', StatementLine::OPEN)->count(),
            'waiting' => ReconDay::where('status', 'prepared')->count(),
            'signed' => ReconDay::where('status', 'signed_off')->count(),
            'unsigned' => ReconDay::where('status', '!=', 'signed_off')->count(),
        ], '-day');
    }

    public function run(Request $request)
    {
        $data = $request->validate(['day' => ['required', 'date', 'before_or_equal:today']]);
        $day = $this->reconciler->run(config('rails.default'), $data['day']);

        return response()->json(['day' => $day->present(), 'message' => 'Statement imported and matched.']);
    }

    public function show(Request $request, ReconDay $day)
    {
        if (! $request->expectsJson()) {
            return view('reconciliation.show', ['day' => $day->present(), 'me' => $request->user()->id]);
        }
        $lines = StatementLine::where('rail', $day->rail)->whereDate('day', $day->day)
            ->when($request->query('status') === 'open', fn ($q) => $q->whereIn('status', StatementLine::OPEN))
            ->when($request->query('status') === 'done', fn ($q) => $q->whereNotIn('status', StatementLine::OPEN));

        return Table::respond($request, $lines, fn (StatementLine $l) => $l->present(), ['line_ref', 'receipt', 'party'], ['time' => 'occurred_at'],
            $day->fresh()->present() + ['can_sign' => $day->status === 'prepared' && $day->prepared_by !== $request->user()->id], 'time');
    }

    public function fix(Request $request, StatementLine $line)
    {
        $line = $this->reconciler->fixFromStatement($line, $request->user());

        return response()->json(['line' => $line->present(), 'message' => 'Booked from the statement.']);
    }

    public function park(Request $request, StatementLine $line)
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:255']]);
        $line = $this->reconciler->park($line, $request->user(), $data['note'] ?? null);

        return response()->json(['line' => $line->present(), 'message' => 'Parked in suspense.']);
    }

    public function prepare(Request $request, ReconDay $day)
    {
        return response()->json(['day' => $this->reconciler->prepare($day, $request->user())->present(), 'message' => 'Prepared. A different person must now sign it off.']);
    }

    public function signOff(Request $request, ReconDay $day)
    {
        return response()->json(['day' => $this->reconciler->signOff($day, $request->user())->present(), 'message' => 'Day signed off.']);
    }
}
