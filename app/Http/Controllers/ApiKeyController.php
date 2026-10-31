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

        return view('api-keys.index', ['isAdmin' => $request->user()->hasAnyRole(DashboardNav::ADMIN)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'environment' => 'required|in:test,live',
        ]);

        $key = new ApiKey($data + ['user_id' => $request->user()->id, 'company_id' => $request->user()->company_id, 'enabled' => true]);
        $plain = $key->issue();
        $key->save();

        return response()->json(['message' => 'Key created. Copy it now: it will not be shown again.', 'plain' => $plain, 'key' => $this->present($key->load('user:id,name', 'company:id,name'))], 201);
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
            'enabled' => $k->enabled,
            'owner' => $k->company?->name ?? $k->user?->name,
            'created_at' => $k->created_at?->timezone('Africa/Nairobi')->format('j M Y'),
            'last_used_at' => $k->last_used_at?->diffForHumans() ?? 'Never',
        ];
    }
}
