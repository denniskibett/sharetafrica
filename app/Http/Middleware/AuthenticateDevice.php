<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mobile app requests: "Authorization: Bearer <device token>" plus "X-Device-Id" (the phone it was issued to).
 * Tokens are stored hashed, expire after 90 days of no use (each use slides it forward), and are revoked on
 * sign-out or from Security → Devices. A disabled or deleted account is refused.
 */
class AuthenticateDevice
{
    public const DAYS = 90;

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $device = (string) $request->header('X-Device-Id');
        $row = $token ? DB::table('device_tokens')->where('token_hash', hash('sha256', $token))->first() : null;

        if (! $row || $row->revoked_at || now()->gt($row->expires_at) || ! hash_equals($row->device_id, $device)) {
            return response()->json(['error' => 'unauthenticated', 'message' => 'Sign in again on this phone.'], 401);
        }
        $user = User::find($row->user_id);
        if (! $user || $user->status === false) {
            return response()->json(['error' => 'account_inactive', 'message' => 'This account is not active. Contact support@sharet.africa.'], 403);
        }
        if (! $row->last_used_at || now()->diffInMinutes($row->last_used_at, true) >= 5) {
            DB::table('device_tokens')->where('id', $row->id)->update(['last_used_at' => now(), 'last_ip' => $request->ip(), 'expires_at' => now()->addDays(self::DAYS)]);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('device_token_id', $row->id);

        return $next($request);
    }
}
