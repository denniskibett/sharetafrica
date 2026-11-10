<?php

namespace App\Http\Controllers;

use App\Support\Growth\Sites;
use App\Support\Growth\Analytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Cross-site analytics collector: POST /t/x from public/js/sharet-analytics.js on the seven Sharet-family sites.
 * The body is JSON sent as text/plain (no CORS preflight, no cookies). A beacon is accepted only when its
 * site key is one of config('growth.sites') AND the Origin (or Referer) is one of that site's origins.
 * Always answers 204, whatever happens, so tracking can never break a page. Privacy rules: App\Support\Growth\Analytics
 * (no IP stored, daily-rotating visitor hash, bots / Do-Not-Track / GPC skipped, paths masked, no query strings).
 */
class GrowthCollectController extends Controller
{
    /** Browser events besides page views, clicks and conversions. */
    public const EVENTS = ['outbound', 'phone_click', 'whatsapp_click', 'email_click'];

    /** Beacons per IP per minute before the rest are silently dropped. */
    public const PER_MINUTE = 240;

    public function __invoke(Request $request)
    {
        $data = $this->payload($request);
        $site = $this->allowedSite($request, $data);
        $response = $this->cors(response()->noContent(), $site ? $this->origin($request) : null);

        if (! $site || ! Analytics::ready() || $this->skip($request)
            || ! RateLimiter::attempt('sa-collect:'.sha1((string) $request->ip()), self::PER_MINUTE, fn () => true)) {
            return $response;
        }

        try {
            $path = Analytics::path((string) ($data['path'] ?? '/'));
            $device = Analytics::device($request->userAgent(), (int) ($data['vw'] ?? 0));

            match ($data['type'] ?? null) {
                'view' => $this->view($request, $site, $data, $path, $device),
                'leave' => $this->leave($site, $data),
                'click' => $this->event($request, $site, 'click', Analytics::clean($data['label'] ?? null, 120), $path, $data, $device),
                'conversion' => array_key_exists($name = (string) ($data['name'] ?? ''), (array) config('growth.conversions'))
                    ? $this->event($request, $site, 'conversion', $name, $path, $data, $device) : null,
                'event' => in_array($data['name'] ?? null, self::EVENTS, true)
                    ? $this->event($request, $site, $data['name'], Analytics::clean($data['label'] ?? null, 120), $path, $data, $device) : null,
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('Cross-site analytics beacon failed', ['site' => $site, 'error' => $e->getMessage()]);
        }

        return $response;
    }

    /** CORS preflight. Not needed by sharet-analytics.js (text/plain), but answered for allowed origins anyway. */
    public function preflight(Request $request)
    {
        $origin = Sites::siteForOrigin($this->origin($request)) ? $this->origin($request) : null;

        return $this->cors(response()->noContent(), $origin)->withHeaders($origin ? [
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type',
            'Access-Control-Max-Age' => '86400',
        ] : []);
    }

    protected function view(Request $request, string $site, array $d, string $path, string $device): void
    {
        $key = $this->key($d);
        if (strlen($key) !== 16) {
            return;
        }
        $utm = $this->utm($d['utm'] ?? []);

        DB::table('growth_page_views')->insertOrIgnore([
            'site' => $site,
            'visitor' => Analytics::visitor($request),
            'view_key' => $key,
            'path' => $path,
            'referrer_host' => $this->refHost($site, (string) ($d['ref'] ?? '')),
            'utm_source' => $utm['source'],
            'utm_medium' => $utm['medium'],
            'utm_campaign' => $utm['campaign'],
            'country' => Analytics::country($request),
            'device' => $device,
            'browser' => Analytics::browser($request->userAgent()),
            'viewport_w' => min(65535, max(0, (int) ($d['vw'] ?? 0))) ?: null,
            'created_at' => now(),
        ]);
    }

    protected function leave(string $site, array $d): void
    {
        $key = $this->key($d);
        if (strlen($key) !== 16) {
            return;
        }
        DB::table('growth_page_views')->where('site', $site)->where('view_key', $key)->where('created_at', '>', now()->subHours(6))->update([
            'duration_ms' => min(3_600_000, max(0, (int) ($d['ms'] ?? 0))),
            'scroll_pct' => min(100, max(0, (int) ($d['scroll'] ?? 0))),
        ]);
    }

    /** Conversions, CTA clicks and contact clicks, with the visitor's first touch this session (sent by the browser). */
    protected function event(Request $request, string $site, string $name, ?string $label, string $path, array $d, string $device): void
    {
        if ($name === 'click' && $label === null) {
            return;
        }
        $ft = is_array($d['ft'] ?? null) ? $d['ft'] : [];
        $utm = $this->utm($ft);
        $ref = $this->refHost($site, (string) ($ft['ref'] ?? ''));

        DB::table('growth_events')->insert([
            'site' => $site,
            'name' => $name,
            'label' => $label,
            'path' => $path,
            'visitor' => Analytics::visitor($request),
            'source' => $utm['source'] ?? $ref ?? 'direct',
            'medium' => $utm['medium'] ?? ($ref ? (Analytics::isSearch($ref) ? 'organic' : 'referral') : null),
            'campaign' => $utm['campaign'],
            'landing_path' => isset($ft['landing']) ? Analytics::path((string) $ft['landing']) : $path,
            'device' => $device,
            'created_at' => now(),
        ]);
    }

    /** The site key, when it is known and the beacon really comes from one of that site's origins. */
    protected function allowedSite(Request $request, array $data): ?string
    {
        $site = is_string($data['site'] ?? null) ? $data['site'] : '';

        return $site !== '' && Sites::siteForOrigin($this->origin($request)) === $site ? $site : null;
    }

    /** Origin header, else the scheme://host[:port] of the Referer (some privacy setups drop Origin). */
    protected function origin(Request $request): ?string
    {
        $origin = $request->headers->get('Origin');
        if ($origin && $origin !== 'null') {
            return strtolower(rtrim($origin, '/'));
        }
        $ref = parse_url((string) $request->headers->get('Referer'));
        if (empty($ref['scheme']) || empty($ref['host'])) {
            return null;
        }

        return strtolower($ref['scheme'].'://'.$ref['host'].(isset($ref['port']) ? ':'.$ref['port'] : ''));
    }

    protected function cors($response, ?string $origin)
    {
        $response->headers->set('Vary', 'Origin');
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        return $response;
    }

    protected function skip(Request $request): bool
    {
        return Analytics::isBot($request->userAgent())
            || $request->header('DNT') === '1'
            || $request->header('Sec-GPC') === '1';
    }

    /** The JSON body (sent as text/plain), at most 4 KB; [] when it is anything else. */
    protected function payload(Request $request): array
    {
        $raw = $request->getContent();
        $data = strlen($raw) <= 4096 ? json_decode($raw, true) : null;

        return is_array($data) ? $data : [];
    }

    /** External referring host only (www./m. stripped); the site's own hosts count as no referrer. */
    protected function refHost(string $site, string $ref): ?string
    {
        $host = strtolower((string) parse_url($ref, PHP_URL_HOST));
        if ($host === '') {
            return null;
        }
        $host = preg_replace('/^(www|m|l|lm)\./', '', $host);
        $own = array_map(fn ($o) => preg_replace('/^(www|m)\./', '', (string) parse_url($o, PHP_URL_HOST)), Sites::origins($site));

        return in_array($host, $own, true) ? null : Str::limit($host, 120, '');
    }

    /** utm_source / utm_medium / utm_campaign, cleaned (no emails or phone numbers) and lower-cased. */
    protected function utm(mixed $utm): array
    {
        $utm = is_array($utm) ? $utm : [];
        $get = function (string $k, int $max) use ($utm) {
            $v = Analytics::clean(is_scalar($utm[$k] ?? null) ? $utm[$k] : null, $max);

            return $v === null ? null : strtolower($v);
        };

        return ['source' => $get('source', 80), 'medium' => $get('medium', 80), 'campaign' => $get('campaign', 120)];
    }

    private function key(array $d): string
    {
        return substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($d['key'] ?? '')), 0, 16);
    }
}
