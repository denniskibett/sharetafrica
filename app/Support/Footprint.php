<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Where and from what someone signed up or sent KYC details: the request metadata the server already receives
 * (IP, browser, languages, country from the edge network, page and time). Kept encrypted in users.kyc → audit,
 * last 50 entries, shown to staff with admin.users. Disclosed on /privacy.
 */
class Footprint
{
    public const KEEP = 50;

    public static function capture(Request $request, string $step): array
    {
        return array_filter([
            'step' => $step,
            'at' => now()->toIso8601String(),
            'ip' => $request->ip(),
            'forwarded_for' => substr((string) $request->header('X-Forwarded-For'), 0, 200) ?: null,
            'country' => strtoupper(substr((string) ($request->header('CF-IPCountry') ?: $request->header('X-Country-Code')), 0, 2)) ?: null,
            'user_agent' => substr((string) $request->userAgent(), 0, 500) ?: null,
            'languages' => substr((string) $request->header('Accept-Language'), 0, 120) ?: null,
            'referer' => substr((string) $request->headers->get('referer'), 0, 300) ?: null,
            'path' => '/'.ltrim($request->path(), '/'),
            'route' => optional($request->route())->getName(),
            'session' => $request->hasSession() ? substr(hash('sha256', $request->session()->getId()), 0, 16) : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    public static function record(User $user, Request $request, string $step): void
    {
        $kyc = Kyc::data($user);
        $kyc['audit'] = array_slice([...($kyc['audit'] ?? []), self::capture($request, $step)], -self::KEEP);
        $user->forceFill(['kyc' => encrypt($kyc)])->saveQuietly();
    }

    /** For the staff profile: newest first, Nairobi time. */
    public static function forStaff(User $user): array
    {
        return collect(Kyc::data($user)['audit'] ?? [])->reverse()->values()->map(fn ($e) => $e + [
            'when' => \Illuminate\Support\Carbon::parse($e['at'])->timezone('Africa/Nairobi')->format('j M Y, H:i:s'),
        ])->all();
    }
}
