<?php

namespace App\Http\Controllers\Mobile;

use App\Api\Tariffs;
use App\Business\Batches;
use App\Business\Statement;
use App\Business\Team;
use App\Http\Controllers\Controller;
use App\Ledger\Money;
use App\Models\BusinessBatch;
use App\Models\BusinessMember;
use App\Models\Company;
use App\Ramp\RampOrders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Business in the app (routes/api.php, /api/mobile/v1/business/…): the company wallet, bulk payouts with a maker and a
 * checker, the team and its roles, and statements. Same device tokens as the personal app; approving a batch is a
 * money call (Idempotency-Key, PIN, ledger freeze). Docs: docs/v2/MOBILE-API.md § Business.
 */
class BusinessController extends Controller
{
    public function __construct(private Batches $batches, private RampOrders $orders) {}

    /** Business home: approvals first, then the balance. {business: null} when this person is on no business team. */
    public function home(Request $request, Tariffs $tariffs): JsonResponse
    {
        $m = Team::membership($request->user());
        if (! $m) {
            return response()->json(['business' => null]);
        }
        $company = Company::findOrFail($m->company_id);
        $wallet = $company->walletUser();
        $waiting = BusinessBatch::where('company_id', $company->id)->where('status', 'awaiting_approval')->latest('id')->limit(20)->get();
        $mine = $waiting->filter(fn ($b) => $this->batches->canApprove($m, $b));
        $account = \App\Support\AccountNumber::for($wallet, create: false);

        return response()->json(['business' => [
            'name' => $company->name, 'sharet_id' => $wallet->sharet_id, 'handle' => \App\Support\Handle::for($wallet), 'verified' => (bool) $company->verified_at,
            'role' => $m->role, 'approval_limit' => ($l = Team::approvalLimit($m)) !== null && Team::can($m, 'approve') ? Money::format($l) : null,
            'balance' => Money::format($this->orders->walletBalance($wallet)), 'currency' => config('ledger.currency'),
            'account_number' => $account ? $account + ['formatted' => \App\Support\AccountNumber::format($account['account'])] : null,
            'volume_discount_percent' => $tariffs->discountBp($wallet) / 100,
            'to_approve' => $mine->count(),
            'waiting' => $waiting->map(fn ($b) => $this->batches->present($b, $m, lines: false))->values(),
            'can' => ['prepare' => Team::can($m, 'prepare'), 'approve' => Team::can($m, 'approve'), 'team' => Team::can($m, 'team'), 'trade' => Team::can($m, 'trade')],
            'rules' => ['two_checkers_above' => Money::format((int) config('business.two_checkers_above')), 'new_payee_hours' => (int) config('business.new_payee_hours')],
        ]]);
    }

    public function batches(Request $request): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');
        $page = BusinessBatch::where('company_id', $m->company_id)
            ->when($request->query('status'), fn ($q, $s) => $q->whereIn('status', explode(',', (string) $s)))->latest('id')->paginate(20);

        return response()->json(['data' => $page->getCollection()->map(fn ($b) => $this->batches->present($b, $m, lines: false))->values(),
            'meta' => ['page' => $page->currentPage(), 'last_page' => $page->lastPage()]]);
    }

    public function batch(Request $request, string $id): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');
        $b = $this->batches->refresh($this->find($m, $id));

        return response()->json(['batch' => $this->batches->present($b, $m)]);
    }

    /** The batch report (CSV): paid, returned, both references per line. */
    public function report(Request $request, string $id): Response
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');
        $b = $this->batches->refresh($this->find($m, $id));

        return response($this->batches->csv($b), 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="'.$b->public_id.'.csv"']);
    }

    /** Prepare a batch: lines, or a CSV (name, number or account, amount). It waits for a checker; nothing moves yet. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'lines' => 'required_without:csv|array|max:'.config('business.max_lines'), 'csv' => 'required_without:lines|string|max:100000']);
        $m = Team::membership($request->user());
        $lines = isset($data['csv']) ? Batches::fromCsv($data['csv']) : $data['lines'];
        $b = $this->batches->create(Team::authorize($m, 'prepare'), $data['name'], $lines);

        return response()->json(['batch' => $this->batches->present($b, $m), 'message' => 'Sent for approval.'], 201);
    }

    /** Repeat a batch (e.g. last month's salaries) as a new one waiting for approval. */
    public function repeat(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:80']);
        $m = Team::authorize(Team::membership($request->user()), 'prepare');
        $old = $this->find($m, $id);
        $lines = $old->lines()->get()->map(fn ($l) => ['name' => $l->name, 'to' => $l->to, 'amount' => \App\Api\PaymentInput::amount($l->amount)])->all();
        $b = $this->batches->create($m, $data['name'], $lines);

        return response()->json(['batch' => $this->batches->present($b, $m), 'message' => 'Sent for approval.'], 201);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $request->validate(['pin' => 'required|string']);
        $m = Team::membership($request->user());
        $m = Team::authorize($m, 'approve');
        $b = $this->batches->approve($m, $this->find($m, $id), $request->input('pin'));

        return response()->json(['batch' => $this->batches->present($b, $m), 'message' => $b->status === 'awaiting_approval'
            ? 'Approved. It needs one more checker.' : 'Approved. Paying '.$b->item_count.' people.']);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['reason' => 'required|string|max:200']);
        $m = Team::authorize(Team::membership($request->user()), 'approve');
        $b = $this->batches->reject($m, $this->find($m, $id), $data['reason']);

        return response()->json(['batch' => $this->batches->present($b, $m), 'message' => 'Rejected. Nothing was paid.']);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');
        $b = $this->batches->cancel($m, $this->find($m, $id));

        return response()->json(['batch' => $this->batches->present($b, $m), 'message' => 'Cancelled. Nothing was paid.']);
    }

    /** Saved people: everyone this business has paid before, to pick for a new batch. */
    public function payees(Request $request): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'prepare');

        return response()->json(['payees' => Batches::savedPeople($m->company_id)]);
    }

    public function team(Request $request): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');

        return response()->json(['team' => BusinessMember::where('company_id', $m->company_id)->with('user')->orderByRaw("case role when 'owner' then 0 when 'maker' then 1 when 'checker' then 2 else 3 end")
            ->orderBy('id')->get()->map(fn ($x) => Team::present($x, $m))->values(), 'roles' => Team::ROLES]);
    }

    public function invite(Request $request): JsonResponse
    {
        $data = $request->validate(['who' => 'required|string|max:190', 'role' => 'required|string', 'approval_limit' => 'nullable|string|max:20', 'pin' => 'required|string']);
        $owner = Team::membership($request->user());
        $new = Team::add(Team::authorize($owner, 'team'), $data['who'], $data['role'], $data['approval_limit'] ?? null, $data['pin']);

        return response()->json(['member' => Team::present($new->load('user'), $owner), 'message' => $new->user->name.' added as '.$new->role.'.'], 201);
    }

    public function updateMember(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['role' => 'nullable|string', 'approval_limit' => 'nullable|string|max:20', 'no_limit' => 'boolean', 'pin' => 'required|string']);
        $owner = Team::authorize(Team::membership($request->user()), 'team');
        $member = BusinessMember::where('company_id', $owner->company_id)->findOrFail($id);
        $member = Team::change($owner, $member, $data['role'] ?? null, $data['approval_limit'] ?? null, $request->boolean('no_limit'), $data['pin']);

        return response()->json(['member' => Team::present($member->load('user'), $owner), 'message' => 'Saved.']);
    }

    public function removeMember(Request $request, int $id): JsonResponse
    {
        $request->validate(['pin' => 'required|string']);
        $owner = Team::authorize(Team::membership($request->user()), 'team');
        $member = BusinessMember::where('company_id', $owner->company_id)->findOrFail($id);
        Team::remove($owner, $member, $request->input('pin'));

        return response()->json(['message' => 'Removed from the team.']);
    }

    /** Statement for a month (?month=2026-11), or CSV with ?format=csv. Viewers see this too. */
    public function statement(Request $request): JsonResponse|Response
    {
        $request->validate(['month' => 'nullable|date_format:Y-m', 'format' => 'nullable|in:json,csv']);
        $m = Team::authorize(Team::membership($request->user()), 'view');
        $wallet = Company::findOrFail($m->company_id)->walletUser();
        if ($request->query('format') === 'csv') {
            return response(Statement::csv($wallet, $request->query('month')), 200, ['Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="statement-'.($request->query('month') ?: now('Africa/Nairobi')->format('Y-m')).'.csv"']);
        }

        return response()->json(['statement' => Statement::month($wallet, $request->query('month')), 'balance' => Money::format($this->orders->walletBalance($wallet))]);
    }

    private function find(BusinessMember $m, string $id): BusinessBatch
    {
        return BusinessBatch::where('company_id', $m->company_id)->where('public_id', $id)->firstOrFail();
    }
}
