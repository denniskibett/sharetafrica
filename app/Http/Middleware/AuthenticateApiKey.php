<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

/** Sharet API: "Authorization: Bearer sk_live_…". Disabled or unknown keys get 401. */
class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = ApiKey::findByPlain($request->bearerToken());

        if (! $key || ! $key->enabled) {
            return response()->json(['error' => 'invalid_api_key', 'message' => 'Send a valid, enabled key as "Authorization: Bearer sk_…".'], 401);
        }

        if (! $key->last_used_at || $key->last_used_at->lt(now()->subMinute())) {
            $key->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $request->attributes->set('api_key', $key);
        // A company's key acts for the company's own Sharet wallet; a personal key for its owner (INTEGRATION.md §1)
        $account = $key->company ? $key->company->walletUser() : $key->user;

        // Acting for a connected account (INTEGRATION.md §6): only one that approved this platform, within its scopes
        if (filled($header = $request->header('Sharet-Account'))) {
            $connection = app(\App\Api\Connections::class)->forRequest($key, $account, (string) $header);
            $request->attributes->set('api_connection', $connection);
            $account = $connection->account;
        }
        $request->setUserResolver(fn () => $account);

        return $next($request);
    }
}
