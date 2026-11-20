<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Administration › Businesses: staff review the businesses people register in Business settings (KYB) and approve
 * or reject them. Approving is what lets a business's live API keys (sk_live_) take payments (docs/v2/INTEGRATION.md §2).
 * Any account holder can own a business; staff cannot approve a business they belong to.
 */
class BusinessesController extends Controller
{
    public const TABS = ['pending' => 'Waiting', 'rejected' => 'Rejected', 'approved' => 'Verified'];

    public function index(Request $request)
    {
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'pending';
        $scope = fn (string $t) => match ($t) {
            'approved' => Company::whereNotNull('verified_at'),
            'rejected' => Company::whereNull('verified_at')->where('settings->kyb_review->status', 'rejected'),
            default => Company::whereNull('verified_at')->where(fn ($q) => $q->whereNull('settings->kyb_review->status')->orWhere('settings->kyb_review->status', '!=', 'rejected')),
        };
        $businesses = $scope($tab)->with('users:id,name,email,company_id', 'verifier:id,name')->orderBy('updated_at', $tab === 'pending' ? 'asc' : 'desc')->paginate(25)->withQueryString();
        $businesses->getCollection()->each(fn (Company $c) => $c->setAttribute('kyb_progress', CompanySettingsController::progressOf($c)));
        $counts = collect(self::TABS)->map(fn ($l, $t) => $scope($t)->count());

        return view('admin.businesses.index', ['businesses' => $businesses, 'tab' => $tab, 'counts' => $counts, 'tabs' => self::TABS, 'me' => $request->user()]);
    }

    /** Approve or reject (a rejection needs a reason the owner will see; rejecting a verified business revokes it). Logged. */
    public function decide(Request $request, Company $company): JsonResponse
    {
        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'reason' => 'required_if:decision,reject|nullable|string|max:300',
        ], ['reason.required_if' => 'Say what the business should fix: the owner will see this.']);
        abort_if($company->hasUser($request->user()), 403, 'You cannot review a business you belong to.');

        $data['decision'] === 'approve' ? $company->verify($request->user()) : $company->reject($request->user(), $data['reason']);
        \App\Security\Lockdown::log('staff '.$data['decision'].'d a business', ['company' => $company->id, 'reason' => $data['reason'] ?? null]);

        return response()->json(['message' => $data['decision'] === 'approve' ? "{$company->name} is verified. Its live keys can take payments." : "{$company->name} was sent back to its owner."]);
    }
}
