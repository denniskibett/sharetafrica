<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Support\People;
use App\Support\Table;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * User management. The page is a shell; the table, stat cards and form options come from the
     * same URL as JSON. Filters: ?status=, ?lane=, ?role=; ?search= matches name, email, phone.
     */
    public function index(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('admin.users.index');
        }

        $query = $this->filtered($request)->with(['company:id,name', 'roles:id,name']);
        $lane = $request->query('lane') ?: null;
        $base = fn () => User::query()->when($lane, fn ($q) => $q->where('intent', $lane));
        [$newThisMonth, $delta] = People::growth($lane);

        return Table::respond($request, $query, fn (User $u) => [
            'id' => $u->id,
            'person' => Table::person($u, route('users.show', $u)),
            'phone' => $u->phone,
            'lane' => People::lane($u->intent),
            'role' => $u->roles->pluck('name')->map(fn ($r) => str_replace('_', ' ', $r))->implode(', ') ?: '—',
            'company' => $u->company?->name,
            'status' => People::status($u->onboarding_status),
            'joined' => Table::date($u->created_at),
            'self' => $u->id === $request->user()->id,
        ], ['name', 'email', 'phone'], ['name' => 'name', 'joined' => 'created_at'], [
            'total' => $base()->count(), 'total_delta' => $delta, 'total_hint' => "$newThisMonth new this month",
            'active' => $base()->where('onboarding_status', 'active')->count(), 'active_hint' => 'Fully set up',
            'waiting' => $base()->where('onboarding_status', 'waiting_list')->count(), 'waiting_hint' => $base()->where('onboarding_status', 'invited')->count().' invited',
            'staff' => $base()->role(['super_admin', 'admin', 'support', 'operations'])->count(), 'staff_hint' => 'Admins, support, operations',
            'companies' => $base()->whereNotNull('company_id')->distinct('company_id')->count('company_id'), 'companies_hint' => 'With a company account',
        ], extra: [
            'roles' => Role::orderBy('name')->get(['id', 'name'])->map(fn ($r) => ['id' => $r->id, 'name' => str_replace('_', ' ', $r->name), 'key' => $r->name]),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'lanes' => People::LANES,
            'statuses' => collect(People::STATUS)->map(fn ($s) => $s[0]),
        ]);
    }

    /** CSV of the current filter (same filters as the table). */
    public function export(Request $request)
    {
        $users = $this->filtered($request)->with('roles:id,name')->latest()->get();

        return response()->streamDownload(function () use ($users) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Phone', 'Lane', 'Roles', 'Status', 'Joined']);
            foreach ($users as $u) {
                fputcsv($out, [$u->name, $u->email, $u->phone, People::lane($u->intent), $u->roles->pluck('name')->implode(' '),
                    People::status($u->onboarding_status)['label'], $u->created_at?->toDateString()]);
            }
            fclose($out);
        }, 'sharet-people-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    protected function filtered(Request $request)
    {
        return User::query()
            ->when($request->filled('status'), fn ($q) => $q->where('onboarding_status', $request->query('status')))
            ->when($request->filled('lane'), fn ($q) => $q->where('intent', $request->query('lane')))
            ->when($request->filled('role'), fn ($q) => $q->role($request->query('role')))
            ->when(($term = trim((string) $request->query('search'))) !== '' && $request->routeIs('users.export'),
                fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%$term%")->orWhere('email', 'like', "%$term%")->orWhere('phone', 'like', "%$term%")));
    }

    /**
     * Admin › People › Add user (JSON for the modal on admin/users/index). Admin-created accounts
     * are active straight away; a lane role (individual, merchant…) also sets users.intent.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'phone' => 'nullable|string|max:32|unique:users,phone',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'company_id' => 'nullable|exists:companies,id',
            'status' => 'nullable|boolean',
        ]);

        $role = \Spatie\Permission\Models\Role::findOrFail($data['role_id']);
        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'company_id' => $data['company_id'] ?? null,
            'status' => (bool) ($data['status'] ?? true),
            'onboarding_status' => 'active',
            'intent' => in_array($role->name, ['individual', 'merchant', 'business', 'techie']) ? $role->name : 'undecided',
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        return response()->json(['success' => true, 'message' => "{$user->name} added as ".str_replace('_', ' ', $role->name).'.']);
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

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot remove your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => "{$user->name} has been removed."]);
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