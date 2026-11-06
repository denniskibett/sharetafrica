<?php

namespace App\Http\Middleware;

use App\Security\Lockdown;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * The gate in front of every web and API route (appended to both groups, so the route and session are known):
 *  1. Hostile-looking requests (path traversal, script or SQL injection in the URL) are refused.
 *  2. System lockdown: only super admins on an allowed IP get in; rail callbacks are still received.
 *  3. Ledger freeze: no request that could move money gets through (the ledger refuses too, see Lockdown).
 *  4. Disabled, deleted or locked-out accounts are signed out on their next request.
 *  5. Staff money consoles: allowed IPs only (if set) and a password re-entered within the last 15 minutes.
 * Every refusal is written to the security log.
 */
class SystemGuard
{
    protected const HOSTILE = '~(\.\./|\.\.%2f|%2e%2e|<script|%3cscript|javascript:|\bunion\b.{0,20}\bselect\b|\bselect\b.{1,40}\bfrom\b.{1,40}\binformation_schema\b|/etc/passwd|\bsleep\s*\(\s*\d|\bbenchmark\s*\(|;\s*(drop|truncate)\s+table)~i';

    public function handle(Request $request, Closure $next): Response
    {
        $route = (string) optional($request->route())->getName();
        $alwaysOpen = $this->starts($route, config('security.always_open_routes', [])) || $request->is('up');

        // 1. Hostile URL patterns
        if (preg_match(self::HOSTILE, rawurldecode($request->getRequestUri()))) {
            return $this->refuse($request, 400, 'That request was blocked.', 'hostile request');
        }

        // 2. System lockdown
        if (Lockdown::systemLocked() && ! $alwaysOpen && ! $this->breakGlass($request)) {
            return $this->refuse($request, 503, 'Sharet is temporarily closed while we check something. Your money is safe; please try again later.', 'system lockdown');
        }

        // 3. Ledger freeze: anything but reading on a money route
        if (! $alwaysOpen && ! $request->isMethodSafe() && $this->starts($route, config('security.money_routes', [])) && Lockdown::ledgerFrozen()) {
            return $this->refuse($request, 423, 'Payments are paused for a moment. Your balance is safe; please try again shortly.', 'ledger frozen');
        }

        // 4. Accounts that may no longer use Sharet
        $user = $request->user();
        if ($user && ($user->trashed() || $user->status === false)) {
            Auth::guard('web')->logout();
            $request->hasSession() && $request->session()->invalidate();

            return $this->refuse($request, 403, 'This account is not active. Contact support@sharet.africa.', 'inactive account', ['user' => $user->id]);
        }

        // 5. Staff money consoles
        if ($this->starts($route, config('security.staff_money_routes', []))) {
            $allowed = config('security.staff_allowed_ips', []);
            if ($allowed && ! in_array($request->ip(), $allowed, true)) {
                return $this->refuse($request, 403, 'The money console is only available from the office network.', 'staff console from unknown IP');
            }
            if (! $request->isMethodSafe() && $request->hasSession()
                && now()->timestamp - (int) $request->session()->get('auth.password_confirmed_at', 0) > config('security.staff_reconfirm_seconds', 900)) {
                return $request->expectsJson()
                    ? $this->refuse($request, 423, 'Re-enter your password to continue.', 'staff password not reconfirmed')
                    : redirect()->guest(route('password.confirm'));
            }
        }

        return $next($request);
    }

    /** During a system lockdown: super admins from an allowed IP, plus the pages they need to sign in and reopen. */
    protected function breakGlass(Request $request): bool
    {
        if (! in_array($request->ip(), config('security.lockdown_allow_ips', []), true)) {
            return false;
        }
        $user = $request->user();

        return ($user && $user->hasRole('super_admin')) || $request->is('login', 'logout', 'confirm-password', 'build/*');
    }

    protected function starts(string $route, array $prefixes): bool
    {
        foreach ($prefixes as $p) {
            if ($route !== '' && str_starts_with($route, $p)) {
                return true;
            }
        }

        return false;
    }

    protected function refuse(Request $request, int $status, string $message, string $why, array $context = []): Response
    {
        Lockdown::log("blocked: {$why}", $context + ['status' => $status, 'method' => $request->method()]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => str_replace(' ', '_', $why), 'message' => $message], $status);
        }

        return response()->view('errors.layout', [
            'code' => $status, 'title' => $status === 503 ? 'Temporarily closed' : ($status === 423 ? 'Paused' : 'Not allowed'),
            'message' => $message, 'retry' => in_array($status, [423, 503], true),
        ], $status);
    }
}
