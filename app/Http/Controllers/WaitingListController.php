<?php

namespace App\Http\Controllers;

use App\Mail\SharetMail;
use App\Models\User;
use App\Models\WaitingListEntry;
use App\Support\Outbox;
use Illuminate\Http\Request;

/**
 * Waiting list, following the live model: users.onboarding_status = waiting_list (with a
 * waiting_list_entries row saying where they came from). An admin invites them
 * (status → invited, emailed), then they choose their lane in onboarding.
 */
class WaitingListController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Invited or already active: their next step is onboarding / the dashboard
        if (! $user->isOnWaitingList()) {
            return redirect()->route($user->needsOnboarding() ? 'onboarding.welcome' : 'dashboard');
        }

        return view('waiting-list.show', ['user' => $user]);
    }

    public function index()
    {
        $people = User::where('onboarding_status', 'waiting_list')
            ->with('waitingListEntry')
            ->latest()
            ->get(['id', 'name', 'email', 'phone', 'intent', 'created_at']);

        return view('waiting-list.index', ['people' => $people]);
    }

    public function invite(Request $request, User $user)
    {
        abort_unless($user->onboarding_status === 'waiting_list', 422, 'This person is not on the waiting list.');

        $user->update(['onboarding_status' => 'invited', 'invited_at' => now(), 'invited_by' => $request->user()->id]);
        WaitingListEntry::where('user_id', $user->id)->update(['status' => 'invited', 'invited_at' => now(), 'invited_by' => $request->user()->id]);

        Outbox::later($user->email, SharetMail::invited($user));

        return response()->json(['message' => "{$user->name} is invited. We emailed them a link to finish setting up."]);
    }
}
