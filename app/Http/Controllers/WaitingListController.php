<?php

namespace App\Http\Controllers;

use App\Mail\SharetMail;
use App\Models\User;
use App\Models\WaitingListEntry;
use App\Support\Outbox;
use App\Support\People;
use App\Support\Table;
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

    public function index(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('waiting-list.index');
        }

        $query = User::where('onboarding_status', 'waiting_list')->with('waitingListEntry')
            ->when($request->filled('lane'), fn ($q) => $q->where('intent', $request->query('lane')));
        $waiting = User::where('onboarding_status', 'waiting_list');

        return Table::respond($request, $query, fn (User $p) => [
            'id' => $p->id,
            'person' => Table::person($p),
            'phone' => $p->phone,
            'lane' => People::lane($p->intent),
            'source' => ucfirst(str_replace(['-', '_'], ' ', $p->waitingListEntry?->source ?? 'sign-up')),
            'status' => People::status('waiting_list'),
            'joined' => Table::date($p->created_at),
        ], ['name', 'email', 'phone'], ['name' => 'name', 'joined' => 'created_at'], [
            'waiting' => (clone $waiting)->count(), 'waiting_hint' => 'Need an invite',
            'week' => (clone $waiting)->where('created_at', '>=', now()->subDays(7))->count(), 'week_hint' => 'Joined in the last 7 days',
            'invited' => User::where('onboarding_status', 'invited')->count(), 'invited_hint' => 'Invited, not set up yet',
            'converted' => User::where('onboarding_status', 'active')->whereNotNull('invited_at')->count(), 'converted_hint' => 'Invited and now active',
        ], extra: ['lanes' => People::LANES]);
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
