<?php

namespace App\SecurityKit;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds a small "Security" button (bottom-left) to the app's own HTML pages, for people allowed to see
 * /admin/security-report only. Shows the site's open critical + high count. SECURITY_BADGE=false turns it off.
 */
class SecurityBadge
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        try {
            if (! $request->isMethod('get') || $request->ajax() || $request->header('X-Inertia') || $request->header('X-Livewire')
                || $request->routeIs('securitykit.report') || ! $request->user()
                || ! filter_var(KycLog::setting('SECURITY_BADGE', true), FILTER_VALIDATE_BOOLEAN)) {
                return $response;
            }
            $type = (string) $response->headers->get('Content-Type');
            if ($response->getStatusCode() !== 200 || ($type !== '' && ! str_contains($type, 'text/html'))
                || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse
                || $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
                return $response;
            }
            $session = $request->hasSession() ? $request->session() : null;
            $key = 'securitykit.admin.'.$request->user()->getAuthIdentifier();
            $admin = $session?->get($key);
            if ($admin === null || ($admin['t'] ?? 0) < time() - 600) {
                $admin = ['ok' => SecurityReportController::isAdmin($request->user()), 't' => time()];
                $session?->put($key, $admin);
            }
            if (! $admin['ok']) {
                return $response;
            }
            $html = $response->getContent();
            if (! is_string($html) || ($pos = strripos($html, '</body>')) === false) {
                return $response;
            }
            $n = 0;
            $file = SecurityReportController::reportFile();
            if (is_readable($file)) {
                $s = (json_decode((string) file_get_contents($file), true)['summary'] ?? []);
                $n = (int) ($s['critical'] ?? 0) + (int) ($s['high'] ?? 0);
            }
            $bg = $n ? '#B3261E' : '#1A1214';
            $label = $n ? "Security · $n to fix" : 'Security';
            $badge = '<a href="'.e(route('securitykit.report')).'" data-securitykit title="Server and sign-in security for this site" '
                .'style="position:fixed;left:12px;bottom:12px;z-index:2147483000;background:'.$bg.';color:#fff;'
                .'font:600 12px/1 system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;padding:8px 11px;border-radius:999px;'
                .'text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,.25);opacity:.92">&#128737; '.e($label).'</a>';
            $response->setContent(substr_replace($html, $badge, $pos, 0));
            $response->headers->remove('Content-Length');
        } catch (\Throwable $e) {
            // never break a page over a badge
        }

        return $response;
    }
}
