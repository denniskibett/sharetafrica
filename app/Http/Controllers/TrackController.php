<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Receives the website's analytics beacons (resources/js/site.js): page views, time on page and clicks.
 * Always answers 204 so tracking can never break a page. Bots and dashboard pages are ignored.
 */
class TrackController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (! is_array($data) || $this->isBot($request->userAgent())) {
            return response()->noContent();
        }

        $path = '/'.ltrim(Str::limit(parse_url((string) ($data['path'] ?? '/'), PHP_URL_PATH) ?: '/', 250, ''), '/');
        $device = $this->device($request->userAgent(), (int) ($data['vw'] ?? 0));

        match ($data['type'] ?? null) {
            'view' => $this->view($request, $data, $path, $device),
            'leave' => $this->leave($data),
            'click' => $this->click($data, $path, $device),
            default => null,
        };

        return response()->noContent();
    }

    protected function view(Request $request, array $d, string $path, string $device): void
    {
        $key = substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($d['key'] ?? '')), 0, 16);
        if (strlen($key) !== 16) {
            return;
        }

        $refHost = parse_url((string) ($d['ref'] ?? ''), PHP_URL_HOST);
        if ($refHost === $request->getHost()) {
            $refHost = null; // internal navigation is not a referrer
        }
        $utm = (array) ($d['utm'] ?? []);

        DB::table('page_views')->insertOrIgnore([
            'visitor' => substr(hash('sha256', $request->ip().'|'.$request->userAgent().'|'.now()->toDateString().'|'.config('app.key')), 0, 16),
            'view_key' => $key,
            'path' => $path,
            'referrer_host' => $refHost ? Str::limit(preg_replace('/^www\./', '', $refHost), 120, '') : null,
            'utm_source' => isset($utm['source']) ? Str::limit((string) $utm['source'], 80, '') : null,
            'utm_medium' => isset($utm['medium']) ? Str::limit((string) $utm['medium'], 80, '') : null,
            'utm_campaign' => isset($utm['campaign']) ? Str::limit((string) $utm['campaign'], 120, '') : null,
            'country' => $this->country($request),
            'device' => $device,
            'browser' => $this->browser($request->userAgent()),
            'viewport_w' => min(65535, max(0, (int) ($d['vw'] ?? 0))) ?: null,
            'user_id' => $request->user()?->id,
            'created_at' => now(),
        ]);
    }

    protected function leave(array $d): void
    {
        $key = substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($d['key'] ?? '')), 0, 16);
        DB::table('page_views')->where('view_key', $key)->where('created_at', '>', now()->subHours(6))->update([
            'duration_ms' => min(3_600_000, max(0, (int) ($d['ms'] ?? 0))),
            'scroll_pct' => min(100, max(0, (int) ($d['scroll'] ?? 0))),
        ]);
    }

    protected function click(array $d, string $path, string $device): void
    {
        $pageH = (int) ($d['ph'] ?? 0);
        $y = (int) ($d['y'] ?? -1);
        $x = (int) ($d['x'] ?? -1);
        if ($pageH <= 0 || $y < 0 || $y > $pageH || $x < 0 || $x > 1000) {
            return;
        }

        DB::table('page_clicks')->insert([
            'path' => $path,
            'device' => $device,
            'x_permille' => $x,
            'y' => $y,
            'page_h' => $pageH,
            'target' => isset($d['t']) ? Str::limit(trim(preg_replace('/\s+/', ' ', (string) $d['t'])), 120, '') : null,
            'created_at' => now(),
        ]);
    }

    protected function country(Request $request): ?string
    {
        // Cloudflare (or another proxy) country header if the site sits behind one
        $c = strtoupper((string) ($request->header('CF-IPCountry') ?? $request->header('X-Country-Code') ?? ''));

        return preg_match('/^[A-Z]{2}$/', $c) && $c !== 'XX' ? $c : null;
    }

    protected function device(?string $ua, int $vw): string
    {
        $ua = strtolower((string) $ua);
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet') || ($vw >= 600 && $vw < 1024 && str_contains($ua, 'android'))) {
            return 'tablet';
        }

        return str_contains($ua, 'mobi') || ($vw > 0 && $vw < 600) ? 'mobile' : 'desktop';
    }

    protected function browser(?string $ua): ?string
    {
        $ua = (string) $ua;
        foreach (['Edg/' => 'Edge', 'OPR/' => 'Opera', 'SamsungBrowser' => 'Samsung', 'Chrome/' => 'Chrome', 'CriOS' => 'Chrome', 'Firefox/' => 'Firefox', 'FxiOS' => 'Firefox', 'Safari/' => 'Safari'] as $needle => $name) {
            if (str_contains($ua, $needle)) {
                return $name;
            }
        }

        return null;
    }

    protected function isBot(?string $ua): bool
    {
        return $ua === null || (bool) preg_match('/bot|crawl|spider|slurp|preview|headless|lighthouse|pingdom|uptime|curl|wget|python|http-client/i', $ua);
    }
}
