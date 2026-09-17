<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a paginated list of users.
     * Optional filters: status, intent, role, search.
     */
    public function index(Request $request)
    {
        $query = User::query()->with(['company', 'roles']);

        // Filter by onboarding_status
        if ($request->filled('status')) {
            $query->where('onboarding_status', $request->input('status'));
        }

        // Filter by intent (lane)
        if ($request->filled('intent')) {
            $query->where('intent', $request->input('intent'));
        }

        // Filter by Spatie role
        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        // Search by name / email / phone
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show a single user profile.
     */
    public function show(User $user)
    {
        $user->load(['company', 'roles', 'inviter', 'verifier']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Invite a waiting-list user to start onboarding.
     * Sets onboarding_status = 'invited', records the inviter + timestamp.
     */
    public function invite(User $user)
    {
        // Guard: only users on the waiting list can be invited
        if ($user->onboarding_status !== 'waiting_list') {
            return back()->with('error', 'This user is not on the waiting list.');
        }

        // Guard: only an admin/super_admin can invite (belt-and-braces, the route already gates this)
        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403, 'Only administrators can invite users.');
        }

        $user->update([
            'onboarding_status' => 'invited',
            'invited_at'        => now(),
            'invited_by'        => auth()->id(),
        ]);

        return back()->with('success', "{$user->name} has been invited to onboarding.");
    }

    /**
     * Revoke an invite. Sends the user back to the waiting list.
     */
    public function uninvite(User $user)
    {
        if ($user->onboarding_status !== 'invited') {
            return back()->with('error', 'This user has not been invited.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403, 'Only administrators can revoke invites.');
        }

        $user->update([
            'onboarding_status' => 'waiting_list',
            'invited_at'        => null,
            'invited_by'        => null,
        ]);

        return back()->with('success', "{$user->name}'s invite has been revoked.");
    }

    /**
     * Soft-delete a user.
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403, 'Only administrators can delete users.');
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$user->name} has been removed.");
    }

    /**
     * Optional: Restore a soft-deleted user.
     */
    public function restore($id)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', "{$user->name} has been restored.");
    }

    /*
    |--------------------------------------------------------------------------
    | WAITING LIST — focused actions
    |--------------------------------------------------------------------------
    */

    /**
     * Show only the users on the waiting list.
     * Used by the admin waiting-list page.
     */
    public function waitingList(Request $request)
    {
        $users = User::with(['roles', 'inviter'])
            ->where('onboarding_status', 'waiting_list')
            ->latest()
            ->paginate(20);

        return view('admin.waiting_list.index', compact('users'));
    }

    /**
     * Bulk-invite multiple users at once.
     * POST with user_ids[].
     */
    public function bulkInvite(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }

        $count = User::whereIn('id', $request->user_ids)
            ->where('onboarding_status', 'waiting_list')
            ->update([
                'onboarding_status' => 'invited',
                'invited_at'        => now(),
                'invited_by'        => auth()->id(),
            ]);

        return back()->with('success', "{$count} users invited to onboarding.");
    }

    /*
    |--------------------------------------------------------------------------
    | REVIEW — approve or reject after onboarding
    |--------------------------------------------------------------------------
    */

    /**
     * Approve a user who has completed their onboarding form.
     * Pivots them to their chosen lane + role.
     */
    public function approve(User $user)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }

        if ($user->onboarding_status !== 'in_progress') {
            return back()->with('error', 'This user is not awaiting review.');
        }

        $intent = $user->intent;
        $role = match ($intent) {
            'merchant'   => 'merchant',
            'business'   => 'business',
            'techie'     => 'techie',
            'individual' => 'individual',
            default      => null,
        };

        if (!$role) {
            return back()->with('error', 'This user has not chosen a lane.');
        }

        $user->pivotTo($intent, $role);
        $user->update(['verified_by' => auth()->id()]);

        return back()->with('success', "{$user->name} is now active.");
    }

    /**
     * Reject a user's onboarding.
     */
    public function reject(Request $request, User $user)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'onboarding_status' => 'rejected',
        ]);

        // Optional: store the reason if you add a column later
        // $user->update(['rejection_reason' => $request->reason]);

        return back()->with('success', "{$user->name}'s onboarding has been rejected.");
    }
}
