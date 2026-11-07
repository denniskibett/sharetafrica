<?php

namespace App\Http\Controllers;

use App\Api\Connections;
use App\Models\ApiConnection;
use Illuminate\Http\Request;

/**
 * The person's side of connected accounts (docs/v2/INTEGRATION.md §6): the consent page /connect/{token}, and
 * Account › Connected apps where they can disconnect at any time.
 */
class ConnectConsentController extends Controller
{
    public function __construct(private Connections $connections) {}

    public function show(Request $request, string $token)
    {
        $c = ApiConnection::where('token', $token)->with(['platform.company'])->firstOrFail();
        if (! $request->user()) {
            session()->put('url.intended', route('connect.show', $token));
        }

        return view('connect.show', ['c' => $c, 'platform' => $this->platformName($c)]);
    }

    public function approve(Request $request, string $token)
    {
        $data = $request->validate(['pin' => ['required', 'string', 'max:8']]);
        $c = $this->connections->approve(ApiConnection::where('token', $token)->firstOrFail(), $request->user(), $data['pin']);

        return $c->return_url
            ? redirect()->away($c->return_url.(str_contains($c->return_url, '?') ? '&' : '?').http_build_query(['connection' => $c->public_id, 'status' => 'active']))
            : redirect()->route('connect.show', $token);
    }

    public function decline(Request $request, string $token)
    {
        $c = $this->connections->decline(ApiConnection::where('token', $token)->firstOrFail(), $request->user());

        return $c->return_url
            ? redirect()->away($c->return_url.(str_contains($c->return_url, '?') ? '&' : '?').http_build_query(['connection' => $c->public_id, 'status' => 'declined']))
            : redirect()->route('connect.show', $token);
    }

    public function index(Request $request)
    {
        $rows = ApiConnection::where('account_user_id', $request->user()->id)->where('status', 'active')->with('platform.company')->latest('approved_at')->get();

        return view('connect.index', ['rows' => $rows, 'name' => fn (ApiConnection $c) => $this->platformName($c)]);
    }

    public function revoke(Request $request, ApiConnection $connection)
    {
        abort_unless($connection->account_user_id === $request->user()->id, 404);
        $this->connections->revoke($connection);

        return back()->with('status', 'Disconnected. '.$this->platformName($connection).' can no longer act for your account.');
    }

    private function platformName(ApiConnection $c): string
    {
        return $c->platform?->company?->name ?? $c->platform?->name ?? 'This app';
    }
}
