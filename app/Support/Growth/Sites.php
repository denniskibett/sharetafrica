<?php

namespace App\Support\Growth;

/** The six Sharet-family sites (config/growth.php) and which origins may post analytics as each. */
class Sites
{
    /** @return array<string, array{name:string,url:string,origins:array,product:string,color:string}> */
    public static function all(): array
    {
        return (array) config('growth.sites', []);
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function name(string $site): string
    {
        return self::all()[$site]['name'] ?? ucfirst($site);
    }

    /** Allowed origins for a site: the configured ones plus SHARET_ANALYTICS_EXTRA_ORIGINS ("site=origin,…"). */
    public static function origins(string $site): array
    {
        $origins = self::all()[$site]['origins'] ?? [];
        foreach (array_filter(array_map('trim', explode(',', (string) config('growth.extra_origins')))) as $pair) {
            [$key, $origin] = array_pad(explode('=', $pair, 2), 2, null);
            if ($key === $site && $origin) {
                $origins[] = $origin;
            }
        }

        return array_values(array_unique(array_map(fn ($o) => strtolower(rtrim($o, '/')), $origins)));
    }

    public static function siteForOrigin(?string $origin): ?string
    {
        if (! $origin) {
            return null;
        }
        $origin = strtolower(rtrim($origin, '/'));
        foreach (self::keys() as $site) {
            if (in_array($origin, self::origins($site), true)) {
                return $site;
            }
        }

        return null;
    }

    /** Site keys owned by a Sharet business (config sharet_id, else company_id). Empty for personal keys. */
    public static function forCompany(?object $company): \Illuminate\Support\Collection
    {
        if (! $company) {
            return collect();
        }

        return collect(self::all())->filter(fn ($s) => (! empty($s['sharet_id']) && strcasecmp((string) $s['sharet_id'], (string) ($company->sharet_id ?? '')) === 0)
            || (! empty($s['company_id']) && (int) $s['company_id'] === (int) $company->id))->keys()->values();
    }
}
