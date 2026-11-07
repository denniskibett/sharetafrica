<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Support\DashboardNav;
use Illuminate\Http\Request;

/**
 * Sharet API keys. People see their company's keys (or their own if they have no company);
 * admins see every key. The full key is returned once, on create and on regenerate.
 */
class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['keys' => $this->visible($request)->with('user:id,name', 'company:id,name')->latest()->get()->map(fn ($k) => $this->present($k))->values()]);
        }

        $company = $request->user()->company;

        return view('api-keys.index', ['isAdmin' => $request->user()->hasAnyRole(DashboardNav::ADMIN), 'company' => $company,
            'wallet' => $company?->walletUser()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'environment' => 'required|in:test,live',
            'scopes' => 'nullable|array',
            'scopes.*' => 'in:'.implode(',', ApiKey::SCOPES),
        ]);
        $data['scopes'] = array_values(array_unique(array_merge(['read'], $data['scopes'] ?? [])));   // every key can read
        $ips = ApiKey::parseIps($request->input('allowed_ips'));
        if ($ips === null) {
            throw \Illuminate\Validation\ValidationException::withMessages(['allowed_ips' => 'Use IP addresses or ranges like 41.90.12.7 or 196.201.0.0/16, separated by commas.']);
        }
        $data['allowed_ips'] = $ips;

        $key = new ApiKey($data + ['user_id' => $request->user()->id, 'company_id' => $request->user()->company_id, 'enabled' => true]);
        $plain = $key->issue();
        $key->save();

        return response()->json(['message' => 'Key created. Copy it now: it will not be shown again.', 'plain' => $plain, 'key' => $this->present($key->load('user:id,name', 'company:id,name'))], 201);
    }

    /** Change what a key may do and where live payouts may come from. */
    public function settings(Request $request, int $id)
    {
        $key = $this->find($request, $id);
        $data = $request->validate(['scopes' => 'nullable|array', 'scopes.*' => 'in:'.implode(',', ApiKey::SCOPES)]);
        $ips = ApiKey::parseIps($request->input('allowed_ips'));
        if ($ips === null) {
            throw \Illuminate\Validation\ValidationException::withMessages(['allowed_ips' => 'Use IP addresses or ranges like 41.90.12.7 or 196.201.0.0/16, separated by commas.']);
        }
        $key->update(['scopes' => array_values(array_unique(array_merge(['read'], $data['scopes'] ?? []))), 'allowed_ips' => $ips]);

        return response()->json(['message' => 'Saved.', 'key' => $this->present($key->fresh(['user:id,name', 'company:id,name']))]);
    }

    public function toggle(Request $request, int $id)
    {
        $key = $this->find($request, $id);
        $key->update(['enabled' => ! $key->enabled]);

        return response()->json(['message' => $key->enabled ? 'Key enabled.' : 'Key disabled. Requests with it now get 401.', 'key' => $this->present($key)]);
    }

    public function rotate(Request $request, int $id)
    {
        $key = $this->find($request, $id);
        $plain = $key->issue();
        $key->last_used_at = null;
        $key->save();

        return response()->json(['message' => 'New key issued. The old one stopped working. Copy the new one now.', 'plain' => $plain, 'key' => $this->present($key)]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->find($request, $id)->delete();

        return response()->json(['message' => 'Key deleted.']);
    }

    protected function visible(Request $request)
    {
        $user = $request->user();

        return ApiKey::query()->when(! $user->hasAnyRole(DashboardNav::ADMIN), fn ($q) => $user->company_id
            ? $q->where('company_id', $user->company_id)
            : $q->where('user_id', $user->id));
    }

    protected function find(Request $request, int $id): ApiKey
    {
        return $this->visible($request)->findOrFail($id);
    }

    protected function present(ApiKey $k): array
    {
        return [
            'id' => $k->id,
            'name' => $k->name,
            'environment' => $k->environment,
            'masked' => $k->masked(),
            'scopes' => $k->scopes ?? ['read'],
            'allowed_ips' => $k->allowed_ips ?? [],
            'payout_blocked' => $k->environment === 'live' && in_array('payout', $k->scopes ?? [], true) && empty($k->allowed_ips),
            'enabled' => $k->enabled,
            'owner' => $k->company?->name ?? $k->user?->name,
            'created_at' => $k->created_at?->timezone('Africa/Nairobi')->format('j M Y'),
            'last_used_at' => $k->last_used_at?->diffForHumans() ?? 'Never',
        ];
    }
}
