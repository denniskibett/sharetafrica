<?php

namespace App\Http\Controllers;

use App\Models\SupportCase;
use App\Support\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The support desk's cases queue (design: DashSupport). Sorted by SLA time left; staff take a case, work it and close
 * it with a note. Support never moves money from here.
 */
class SupportController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('support.index');
        }
        $query = SupportCase::with(['order', 'customer', 'owner', 'closer'])
            ->when($request->query('status', 'open') === 'open', fn ($q) => $q->whereIn('status', SupportCase::OPEN))
            ->when($request->query('status') === 'closed', fn ($q) => $q->where('status', 'closed'))
            ->when($request->boolean('mine'), fn ($q) => $q->where('owner_id', $request->user()->id));
        $open = SupportCase::whereIn('status', SupportCase::OPEN);

        return Table::respond($request, $query, fn (SupportCase $c) => $c->present(), ['number', 'summary'], ['sla' => 'sla_due_at', 'opened' => 'created_at'], [
            'open' => (clone $open)->count(),
            'breached' => (clone $open)->where('sla_due_at', '<', now())->count(),
            'due_soon' => (clone $open)->whereBetween('sla_due_at', [now(), now()->addHour()])->count(),
            'unassigned' => (clone $open)->whereNull('owner_id')->count(),
        ], 'sla');
    }

    public function assign(Request $request, SupportCase $case)
    {
        abort_if($case->status === 'closed', 422, 'This case is closed.');
        $case->update(['owner_id' => $request->user()->id, 'status' => $case->status === 'new' ? 'open' : $case->status]);

        return response()->json(['message' => $case->number.' is yours.', 'case' => $case->fresh()->present()]);
    }

    /** The open, unassigned case with the least SLA time left. */
    public function next(Request $request)
    {
        $case = DB::transaction(function () use ($request) {
            $c = SupportCase::whereIn('status', SupportCase::OPEN)->whereNull('owner_id')->orderBy('sla_due_at')->lockForUpdate()->first();
            $c?->update(['owner_id' => $request->user()->id, 'status' => 'open']);

            return $c;
        });

        return response()->json($case ? ['message' => $case->number.' is yours.', 'case' => $case->fresh()->present()] : ['message' => 'No unassigned cases.', 'case' => null]);
    }

    public function close(Request $request, SupportCase $case)
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:1000']], ['note.required' => 'Say what was done for the customer.']);
        $case->update(['status' => 'closed', 'close_note' => $data['note'], 'closed_by' => $request->user()->id, 'closed_at' => now(),
            'owner_id' => $case->owner_id ?? $request->user()->id]);

        return response()->json(['message' => $case->number.' closed.', 'case' => $case->fresh()->present()]);
    }
}
