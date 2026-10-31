<?php

namespace App\Helpers;

use App\Models\System;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Countries\Package\Countries;

class SystemHelper
{
    /**
     * Get system settings with caching
     */
    public static function settings()
    {
        return Cache::rememberForever('system_settings', function () {
            return System::firstOrCreate([], [
                'name' => config('app.name', 'Laravel'),
                'slogan' => 'Your trusted application',
                'timezone' => config('app.timezone', 'UTC'),
                'date_format' => 'd-m-Y',
                'time_format' => 'H:i:s',
                'currency' => 'KES',
                'currency_symbol' => 'KSh',
                'primary_color' => '#3A57E8',
                'secondary_color' => '#08B1BA',
                'pagination_limit' => 15,
                'maintenance_mode' => false,
                'settings' => [
                    'notifications' => [
                        'email_notifications' => true,
                        'push_notifications' => true,
                        'sms_notifications' => false,
                        'notification_sound' => true,
                    ],
                    'security' => [
                        'two_factor_auth' => false,
                        'login_attempts' => 5,
                        'session_timeout' => 30,
                        'password_expiry' => 90,
                    ],
                    'integrations' => [
                        'google_analytics' => '',
                        'google_maps_key' => '',
                        'mail_driver' => 'smtp',
                        'mail_host' => '',
                        'mail_port' => '587',
                        'mail_username' => '',
                        'mail_password' => '',
                    ],
                    'backup' => [
                        'auto_backup' => true,
                        'backup_frequency' => 'daily',
                        'backup_retention' => 30,
                        'backup_to_cloud' => false,
                    ],
                    'company' => [
                        'website' => '',
                        'phone' => '',
                        'email' => '',
                        'address' => '',
                        'about' => '',
                        'mission' => '',
                        'vision' => '',
                        'values' => '',
                    ],
                    'currency_position' => 'before',
                ],
                'website_pages' => [
                    'home' => ['enabled' => true,'title' => 'Home','slug' => '', 'show_in_menu' => true,'order' => 1],
                    'about' => ['enabled' => true,'title' => 'About Us','slug' => 'about', 'show_in_menu' => true,'order' => 2],
                    'services' => ['enabled' => true,'title' => 'Services','slug' => 'services', 'show_in_menu' => true,'order' => 3],
                    'contact' => ['enabled' => true,'title' => 'Contact Us','slug' => 'contact', 'show_in_menu' => true,'order' => 4],
                ],
                'social_media' => [
                    'facebook' => ['enabled' => false,'url' => '', 'icon' => 'ri-facebook-fill','name' => 'Facebook','color' => '#1877F2','order' => 1],
                    'twitter' => ['enabled' => false,'url' => '', 'icon' => 'ri-twitter-fill','name' => 'Twitter','color' => '#1DA1F2','order' => 2],
                    'instagram' => ['enabled' => false,'url' => '', 'icon' => 'ri-instagram-fill','name' => 'Instagram','color' => '#E4405F','order' => 3],
                    'linkedin' => ['enabled' => false,'url' => '', 'icon' => 'ri-linkedin-fill','name' => 'LinkedIn','color' => '#0A66C2','order' => 4],
                ],
            ]);
        });
    }

    /**
     * Clear system settings cache
     */
    public static function clearCache()
    {
        Cache::forget('system_settings');
    }

    /**
     * Get specific setting
     */
    public static function get($key, $default = null)
    {
        $settings = self::settings();

        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $value = $settings;

            foreach ($keys as $k) {
                if (is_object($value) && isset($value->$k)) {
                    $value = $value->$k;
                } elseif (is_array($value) && array_key_exists($k, $value)) {
                    $value = $value[$k];
                } else {
                    return $default;
                }
            }

            return $value;
        }

        return $settings->$key ?? $default;
    }

    // =========================
    // App info
    // =========================

    public static function appName() { return self::get('name', config('app.name')); }
    public static function slogan() { return self::get('slogan', 'Your trusted application'); }

    // =========================
    // Logos & Favicon
    // =========================

    public static function logoUrl($type = 'light')
    {
        $logo = match($type){
            'dark' => self::get('logo_dark'),
            'icon' => self::get('logo_icon'),
            'auth' => null,
            default => self::get('logo')
        };

        if ($type === 'auth') return self::authLogoUrl();

        if ($logo && file_exists(public_path($logo))) {
            return asset($logo);
        }

        return match($type){
            'dark' => asset('public/images/logo/logo-dark.svg'),
            'icon' => asset('public/images/logo/logo-icon.svg'),
            'auth' => asset('public/images/logo/auth-logo.svg'),
            default => asset('public/images/logo/logo.svg'),
        };
    }

    public static function authLogoUrl()
    {
        $logo = self::get('logo');
        if ($logo) {
            if (file_exists(public_path('public/images/logo/' . $logo))) {
                return asset('public/images/logo/' . $logo);
            }
            
            if (file_exists(storage_path('app/public/' . $logo))) {
                return asset('storage/' . $logo);
            }
            
            if (file_exists(public_path($logo))) {
                return asset($logo);
            }
        }
        return asset('images/logo/auth-logo.svg');
    }

    public static function faviconUrl()
    {
        $favicon = self::get('favicon');
        if ($favicon) {
            if (file_exists(public_path('images/' . $favicon))) {
                return asset('images/' . $favicon);
            }
            
            if (file_exists(public_path('images/logo/' . $favicon))) {
                return asset('images/logo/' . $favicon);
            }
            
            if (file_exists(storage_path('app/public/' . $favicon))) {
                return asset('storage/' . $favicon);
            }
            
            if (file_exists(public_path($favicon))) {
                return asset($favicon);
            }
        }
        return asset('favicon.ico');
    }
    
    // =========================
    // Social media
    // =========================

    public static function socialMedia($platform = null)
    {
        $socialMedia = self::get('social_media', []);
        if (!is_array($socialMedia)) $socialMedia = [];

        $enabled = array_filter($socialMedia, fn($i) => ($i['enabled'] ?? false) && !empty($i['url']));
        usort($enabled, fn($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        return $platform ? ($enabled[$platform] ?? null) : $enabled;
    }

    // =========================
    // Pages
    // =========================

    public static function navigationPages()
    {
        $pages = self::get('website_pages', []);
        $enabled = array_filter($pages, fn($p) => ($p['enabled'] ?? false) && ($p['show_in_menu'] ?? false));
        usort($enabled, fn($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));
        return $enabled;
    }

    public static function getPage($slug)
    {
        $pages = self::get('website_pages', []);
        foreach ($pages as $page) {
            if (($page['slug'] ?? '') === $slug && ($page['enabled'] ?? false)) return $page;
        }
        return null;
    }

    public static function company($key = null)
    {
        $company = self::get('settings.company', []);
        return $key ? ($company[$key] ?? null) : $company;
    }

    // =========================
    // Currency & formatting
    // =========================

    public static function currency($amount)
    {
        $currencyCode = self::get('currency', 'KES');
        $symbol = self::get('currency_symbol', 'KSh');
        $position = self::get('settings.currency_position', 'before');

        $currencies = self::currencies();
        if (isset($currencies[$currencyCode])) $symbol = $currencies[$currencyCode]['symbol'] ?? $symbol;

        return match($position){
            'after' => number_format($amount, 2) . ' ' . $symbol,
            'after_space' => number_format($amount, 2) . ' ' . $symbol,
            'before_space' => $symbol . ' ' . number_format($amount, 2),
            default => $symbol . number_format($amount, 2),
        };
    }

    public static function currencies(): array
    {
        return Cache::remember('currencies.all', now()->addMonth(), function () {
            $countries = self::countries()->all();
            $currencies = [];

            foreach ($countries as $country) {
                if (!isset($country->currencies) || $country->currencies->isEmpty()) continue;
                $code = $country->currencies->keys()[0] ?? null;
                if (!$code || isset($currencies[$code])) continue;
                $data = $country->currencies->first();
                $currencies[$code] = [
                    'code' => $code,
                    'name' => $data['name'] ?? $code,
                    'symbol' => $data['symbol'] ?? $code,
                    'countries' => [['code' => $country->cca2 ?? '', 'name' => $country->name->common ?? '']]
                ];
            }

            ksort($currencies);
            return $currencies;
        });
    }

    public static function currencyOptions($includeEmpty = true): array
    {
        $currencies = self::currencies();
        $options = $includeEmpty ? ['' => 'Select Currency'] : [];
        foreach ($currencies as $c) $options[$c['code']] = "{$c['name']} ({$c['code']}) - {$c['symbol']}";
        return $options;
    }

    // =========================
    // Countries
    // =========================

    public static function countries(): Countries
    {
        return new Countries();
    }

    public static function allCountries($locale = 'en'): array
    {
        return Cache::remember("countries.all.{$locale}", now()->addMonth(), function () use ($locale) {
            return self::countries()->all()
                ->sortBy(fn($c) => $c->name->common ?? '')
                ->map(fn($c) => self::formatCountry($c, $locale))
                ->values()
                ->toArray();
        });
    }

    public static function getCountry(string $code, string $locale = 'en'): ?array
    {
        return Cache::remember("country.{$code}.{$locale}", now()->addMonth(), function () use ($code, $locale) {
            $country = self::countries()->where('cca2', strtoupper($code))->first()
                ?? self::countries()->where('cca3', strtoupper($code))->first();
            return $country ? self::formatCountry($country, $locale) : null;
        });
    }

    protected static function formatCountry($country, $locale = 'en'): array
    {
        $currencyCode = $currencyName = $currencySymbol = null;
        if (isset($country->currencies) && !$country->currencies->isEmpty()) {
            $currencyCode = $country->currencies->keys()[0] ?? null;
            $c = $country->currencies->first();
            $currencyName = $c['name'] ?? null;
            $currencySymbol = $c['symbol'] ?? $currencyCode;
        }

        return [
            'code' => $country->cca2 ?? '',
            'iso3' => $country->cca3 ?? '',
            'name' => $country->name->common ?? '',
            'official_name' => $country->name->official ?? '',
            'capital' => $country->capital[0] ?? '',
            'calling_code' => !empty($country->calling_codes) ? '+' . $country->calling_codes[0] : '',
            'currency_code' => $currencyCode,
            'currency_name' => $currencyName,
            'currency_symbol' => $currencySymbol,
            'region' => $country->region ?? '',
            'subregion' => $country->subregion ?? '',
            'latitude' => $country->latlng[0] ?? null,
            'longitude' => $country->latlng[1] ?? null,
            'states' => isset($country->states) ? array_map(fn($s) => $s['name'] ?? null, $country->states) : [],
            'timezones' => $country->timezones?->toArray() ?? [],
        ];
    }

    public static function countryOptions($includeEmpty = true, $locale = 'en'): array
    {
        $countries = self::allCountries($locale);
        $options = $includeEmpty ? ['' => 'Select Country'] : [];
        foreach ($countries as $c) $options[$c['code']] = $c['name'];
        return $options;
    }

    // =========================
    // Utilities
    // =========================

    public static function timezoneOptions()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        $options = [];
        foreach ($timezones as $tz) $options[$tz] = $tz;
        return $options;
    }

    public static function isMaintenanceMode() { return (bool) self::get('maintenance_mode', false); }
    public static function paginationLimit() { return self::get('pagination_limit', 15); }
    public static function primaryColor() { return self::get('primary_color', '#3A57E8'); }
    public static function secondaryColor() { return self::get('secondary_color', '#08B1BA'); }
    public static function contactEmail() { return self::get('contact_email') ?? self::company('email'); }
    public static function contactPhone() { return self::get('contact_phone') ?? self::company('phone'); }
    public static function address() { return self::get('address') ?? self::company('address'); }
    public static function metaDescription() { return self::get('meta_description'); }
    public static function metaKeywords() { return self::get('meta_keywords'); }
    public static function timezone() { return self::get('timezone', config('app.timezone', 'UTC')); }
    public static function dateFormat() { return self::get('date_format', 'Y-m-d'); }
    public static function timeFormat() { return self::get('time_format', 'H:i:s'); }
    public static function currencyCode() { return self::get('currency', 'KES'); }
    public static function currencySymbol() { return self::get('currency_symbol', 'KSh'); }
    public static function customCss() { return self::get('custom_css'); }
    public static function customJs() { return self::get('custom_js'); }
    public static function googleAnalyticsId() { return config('services.google.analytics_id') ?? self::get('settings.integrations.google_analytics'); }
    public static function twoFactorAuthEnabled() { return (bool) self::get('settings.security.two_factor_auth', false); }
}
