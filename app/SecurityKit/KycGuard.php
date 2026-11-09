<?php

namespace App\SecurityKit;

use Closure;
use Illuminate\Http\Request;

/**
 * Watches POSTs to login / register / password / one-time-code endpoints: refuses them when KycLog::verdict()
 * says the network or account is guessing, and logs whether each one worked.
 */
class KycGuard
{
    private const RX = '#(^|/)(login|signin|sign-in|register|signup|sign-up|otp|verify-otp|verify-code|confirm-email|two-factor-challenge|forgot-password|reset-password|password/email|password/reset)(/|$)#i';

    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('post') || ! preg_match(self::RX, $request->path(), $m)) {
            return $next($request);
        }
        $kind = $this->kind(strtolower($m[2]));
        $identity = $this->identity($request);

        $v = KycLog::verdict($identity, $kind);
        if (! $v['allow']) {
            KycLog::record("$kind.blocked", $identity, ['reasons' => $v['reasons']]);
            $msg = 'Too many failed attempts from your network. Please wait an hour and try again, or contact support.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 429);
            }

            return back()->withInput($request->except(['password', 'password_confirmation', 'code', 'otp']))
                ->withErrors(['email' => $msg, 'phone' => $msg, 'code' => $msg]);
        }

        $response = $next($request);

        $status = $response->getStatusCode();
        $errors = $request->hasSession() && $request->session()->has('errors') && $request->session()->get('errors')->any();
        $failed = $errors || in_array($status, [401, 403, 419, 422, 429], true) || $status >= 500;
        KycLog::record($kind.($failed ? '.fail' : '.ok'), $identity, ['status' => $status]);

        return $response;
    }

    private function kind(string $word): string
    {
        return match (true) {
            in_array($word, ['login', 'signin', 'sign-in'], true) => 'login',
            in_array($word, ['register', 'signup', 'sign-up'], true) => 'register',
            in_array($word, ['forgot-password', 'reset-password', 'password/email', 'password/reset'], true) => 'password',
            default => 'otp',
        };
    }

    private function identity(Request $request): ?string
    {
        foreach (['email', 'phone', 'username', 'login', 'mobile', 'phone_number', 'msisdn'] as $k) {
            if (is_string($v = $request->input($k)) && $v !== '') {
                return mb_substr($v, 0, 190);
            }
        }

        return $request->user()?->email;
    }
}
