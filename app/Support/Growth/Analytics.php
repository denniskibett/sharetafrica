<?php

namespace App\Support\Growth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Privacy rules and helpers for the cross-site analytics (tables in database/schema/2026_11_10_growth_hub.sql):
 * no IPs or personal details stored, a daily-rotating visitor hash, Do-Not-Track / Global Privacy Control
 * honoured, bots skipped, paths stored without queries and with token-like segments masked.
 */
class Analytics
{
    public const TABLES = ['growth_page_views', 'growth_events', 'growth_seo_checks', 'growth_todos'];

    /** Has database/schema/2026_11_10_growth_hub.sql been applied? */
    public static function ready(): bool
    {
        return Cache::remember('growth-ready', now()->addMinutes(10), function () {
            try {
                foreach (self::TABLES as $t) {
                    if (! Schema::hasTable($t)) {
                        return false;
                    }
                }

                return true;
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public static function skip(Request $request): bool
    {
        return self::isBot($request->userAgent()) || $request->header('DNT') === '1' || $request->header('Sec-GPC') === '1';
    }

    /** Daily-rotating anonymous id: hash of IP + browser + date + app key. The IP itself is never stored. */
    public static function visitor(Request $request): string
    {
        return substr(hash('sha256', $request->ip().'|'.$request->userAgent().'|'.now()->toDateString().'|'.config('app.key')), 0, 16);
    }

    /** Path only, no query; segments that look like tokens, long ids or emails are masked. */
    public static function path(?string $path): string
    {
        $path = parse_url((string) $path, PHP_URL_PATH) ?: '/';
        $parts = array_map(function ($seg) {
            $seg = rawurldecode($seg);

            return preg_match('/^[A-Za-z0-9_-]{24,}$|^[0-9a-f-]{32,36}$|@|^\+?\d{9,}$/', $seg) ? ':token' : Str::limit($seg, 80, '');
        }, array_filter(explode('/', $path), 'strlen'));

        return Str::limit('/'.implode('/', $parts), 250, '');
    }

    public static function device(?string $ua, int $vw): string
    {
        $ua = strtolower((string) $ua);
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet') || ($vw >= 600 && $vw < 1024 && str_contains($ua, 'android'))) {
            return 'tablet';
        }

        return str_contains($ua, 'mobi') || ($vw > 0 && $vw < 600) ? 'mobile' : 'desktop';
    }

    public static function browser(?string $ua): ?string
    {
        $ua = (string) $ua;
        foreach (['Edg/' => 'Edge', 'OPR/' => 'Opera', 'Opera Mini' => 'Opera Mini', 'SamsungBrowser' => 'Samsung', 'UCBrowser' => 'UC Browser',
            'Chrome/' => 'Chrome', 'CriOS' => 'Chrome', 'Firefox/' => 'Firefox', 'FxiOS' => 'Firefox', 'Safari/' => 'Safari'] as $needle => $name) {
            if (str_contains($ua, $needle)) {
                return $name;
            }
        }

        return null;
    }

    public static function isBot(?string $ua): bool
    {
        return $ua === null || $ua === ''
            || (bool) preg_match('/bot|crawl|spider|slurp|preview|headless|lighthouse|pingdom|uptime|curl|wget|python|http-client|facebookexternalhit|whatsapp/i', $ua);
    }

    public static function isSearch(?string $host): bool
    {
        return (bool) preg_match('/(^|\.)(google|bing|duckduckgo|yahoo|yandex|ecosia|baidu|brave)(\.|$)/i', (string) $host);
    }

    public static function country(Request $request): ?string
    {
        $c = strtoupper((string) ($request->header('CF-IPCountry') ?? $request->header('X-Country-Code') ?? ''));

        return preg_match('/^[A-Z]{2}$/', $c) && $c !== 'XX' ? $c : null;
    }

    /** Free text from the browser: tags stripped, nothing that looks like an email or phone number, length capped. */
    public static function clean(mixed $value, int $max): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }
        $value = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
        if ($value === '' || preg_match('/@|\+?\d[\d\s-]{8,}/', $value)) {
            return null;
        }

        return Str::limit($value, $max, '');
    }

    /** Marketing channel for a source / medium pair: paid | organic | social | email | sms | referral | direct. */
    public static function channel(?string $source, ?string $medium): string
    {
        $source = strtolower((string) $source);
        $medium = strtolower((string) $medium);

        return match (true) {
            (bool) preg_match('/cpc|ppc|paid|cpm|display|^ads?$|sponsored|boost/', $medium) => 'paid',
            in_array($medium, ['email', 'newsletter', 'e-mail'], true) => 'email',
            $medium === 'sms' || $source === 'sms' => 'sms',
            $medium === 'organic' || self::isSearch($source) => 'organic',
            in_array($medium, ['social', 'social_media'], true)
                || (bool) preg_match('/(^|\.)(facebook|fb|instagram|ig|whatsapp|wa|tiktok|twitter|t|x|linkedin|lnkd|youtube|threads|telegram|snapchat|pinterest)(\.|$)/', $source) => 'social',
            $source === '' || $source === 'direct' => 'direct',
            default => 'referral',
        };
    }

    public const CHANNELS = [
        'organic' => 'Organic search', 'paid' => 'Paid ads', 'social' => 'Social', 'referral' => 'Other sites',
        'direct' => 'Direct', 'email' => 'Email', 'sms' => 'SMS',
    ];
}
