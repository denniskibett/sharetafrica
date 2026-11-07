<?php

namespace App\Http\Controllers;

use App\Api\ApiError;
use App\Api\Settlements;
use App\Models\ApiSettlement;
use Illuminate\Http\Request;

/**
 * Dashboard › Settlements: live API batches above the threshold wait here for a second person from the same business
 * (docs/v2/INTEGRATION.md §2). Approving or rejecting needs a recently re-entered password.
 */
class SettlementApprovalsController extends Controller
{
    public function __construct(private Settlements $settlements) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $rows = $user->company_id
            ? ApiSettlement::whereHas('key', fn ($q) => $q->where('company_id', $user->company_id))->with(['user:id,name', 'approver:id,name', 'key'])->latest()->limit(50)->get()
            : collect();

        return view('settlements.index', ['rows' => $rows, 'me' => $user]);
    }

    public function approve(Request $request, ApiSettlement $settlement)
    {
        return $this->act(fn () => $this->settlements->approve($settlement, $request->user()), 'Approved. The payments are on their way.');
    }

    public function reject(Request $request, ApiSettlement $settlement)
    {
        return $this->act(fn () => $this->settlements->reject($settlement, $request->user()), 'Rejected. Nothing was paid.');
    }

    private function act(callable $fn, string $ok)
    {
        try {
            $fn();
        } catch (ApiError $e) {
            return back()->withErrors(['settlement' => $e->detail]);
        }

        return back()->with('status', $ok);
    }
}
