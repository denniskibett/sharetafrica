<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Administration › Verifications: people whose automatic checks did not pass wait here for staff (approve or reject on
 * their page). The Unfreeze tab holds frozen accounts asking to unfreeze: staff compare the new selfie with the
 * verification selfie and approve or reject here (App\Support\Freeze).
 */
class VerificationsController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pending', 'rejected', 'approved', 'unfreeze'], true) ? $request->query('tab') : 'pending';
        $counts = User::whereNotNull('kyc_review')->selectRaw('kyc_review, count(*) n')->groupBy('kyc_review')->pluck('n', 'kyc_review')
            ->put('unfreeze', User::whereNotNull('frozen_at')->whereNotNull('unfreeze_requested_at')->count());
        if ($tab === 'unfreeze') {
            $people = User::whereNotNull('frozen_at')->whereNotNull('unfreeze_requested_at')->orderBy('unfreeze_requested_at')
                ->paginate(25)->withQueryString();

            return view('admin.verifications.index', compact('people', 'tab', 'counts'));
        }
        $people = User::where('kyc_review', $tab)->orderBy('updated_at', $tab === 'pending' ? 'asc' : 'desc')
            ->paginate(25, ['id', 'name', 'email', 'phone', 'kyc_level', 'updated_at'])->withQueryString();

        return view('admin.verifications.index', compact('people', 'tab', 'counts'));
    }

    /** Approve (unfreeze) or reject (stays frozen, the person sees the reason) a waiting unfreeze request. */
    public function unfreeze(Request $request, User $user)
    {
        $data = $request->validate(['decision' => 'required|in:approve,reject', 'reason' => 'required_if:decision,reject|nullable|string|max:300']);
        abort_unless($user->frozen_at && $user->unfreeze_requested_at, 404);
        \App\Support\Freeze::decide($user, $request->user(), $data['decision'], $data['reason'] ?? null);
        \App\Security\Lockdown::log('staff '.($data['decision'] === 'approve' ? 'approved' : 'rejected').' an unfreeze', ['user' => $user->id, 'staff' => $request->user()->id]);

        return redirect()->route('admin.verifications.index', ['tab' => 'unfreeze'])
            ->with('status', $data['decision'] === 'approve' ? $user->name.' is unfrozen.' : $user->name.' stays frozen.');
    }
}
