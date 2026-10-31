<?php

namespace App\Helpers;

use PragmaRX\Countries\Package\Countries;
use Illuminate\Support\Facades\Cache;

class CountryHelper
{
    /**
     * Get countries instance
     */
    public static function countries(): Countries
    {
        return new Countries();
    }

    /**
     * Get all countries with full PragmaRX fields and caching
     */
    public static function all($locale = 'en'): array
    {
        return Cache::remember("countries.all.{$locale}", now()->addMonth(), function () use ($locale) {
            return self::countries()->all()
                ->sortBy(fn($c) => $c->name->common ?? '')
                ->map(fn($country) => self::formatCountry($country, $locale))
                ->values()
                ->toArray();
        });
    }

    /**
     * Get a single country by code with full fields
     */
    public static function get(string $code, string $locale = 'en'): ?array
    {
        $cacheKey = "country.{$code}.{$locale}";

        return Cache::remember($cacheKey, now()->addMonth(), function () use ($code, $locale) {
            $country = self::countries()->where('cca2', strtoupper($code))->first()
                ?? self::countries()->where('cca3', strtoupper($code))->first();

            return $country ? self::formatCountry($country, $locale) : null;
        });
    }

    /**
     * Format a PragmaRX Country model into array with all possible fields
     */
    protected static function formatCountry($country, $locale = 'en'): array
    {
        // Currency info
        $currencyCode = null;
        $currencyName = null;
        $currencySymbol = null;

        if (isset($country->currencies) && !$country->currencies->isEmpty()) {
            $currencyCode = $country->currencies->keys()->first();
            $currencyData = $country->currencies->first();

            if (is_array($currencyData)) {
                $currencyName = $currencyData['name'] ?? null;
                $currencySymbol = $currencyData['symbol'] ?? null;
            } elseif (is_object($currencyData)) {
                $currencyName = $currencyData->name ?? null;
                $currencySymbol = $currencyData->symbol ?? null;
            }
        }

        return [
            // Identification
            'code' => $country->cca2 ?? '',
            'iso3' => $country->cca3 ?? '',
            'numeric_code' => $country->ccn3 ?? '',
            'name' => $country->name->common ?? '',
            'official_name' => $country->name->official ?? '',
            'native_name' => $country->name->native->{$locale}->common ?? $country->name->common,

            // Capital and calling
            'capital' => $country->capital[0] ?? '',

            'calling_code' => !empty($country->calling_codes) ? '+' . $country->calling_codes[0] : '',

            // Currency
            'currency_code' => $currencyCode,
            'currency_name' => $currencyName,
            'currency_symbol' => $currencySymbol,

            // Geography
            'region' => $country->region ?? '',
            'subregion' => $country->subregion ?? '',
            'latitude' => $country->latlng[0] ?? null,
            'longitude' => $country->latlng[1] ?? null,
            'area' => $country->area ?? 0,
            'population' => $country->population ?? 0,
            'demonym' => $country->demonym ?? '',

            // Flags
            'flag_emoji' => $country->flag->emoji ?? '',
            'flag_svg' => $country->flag->svg ?? '',

            // Languages
            'languages' => $country->languages?->toArray() ?? [],

            // Timezones
            'timezones' => $country->timezones?->toArray() ?? [],

            // Borders
            'borders' => $country->borders?->toArray() ?? [],

            // Top-level translations
            'translations' => $country->translations ?? [],

            // Native currency
            'currency_native' => $currencySymbol ?? $currencyCode,

            // Subdivisions / States
            'states' => isset($country->states) ? array_map(
                fn($s) => $s['name'] ?? null,
                $country->states
            ) : [],

            // Extra identifiers
            'cioc' => $country->cioc ?? '',
            'tld' => $country->tld ?? [],
            'independent' => $country->independent ?? true,
            'un_member' => $country->un_member ?? true,
        ];
    }

    /**
     * Timezones optionally by country code
     */
    public static function timezones(?string $countryCode = null): array
    {
        if ($countryCode) {
            $country = self::get($countryCode);
            return $country['timezones'] ?? [];
        }

        return Cache::remember('all.timezones', now()->addMonth(), function () {
            $all = \DateTimeZone::listIdentifiers();
            $grouped = [];

            foreach ($all as $tz) {
                [$region, $city] = array_pad(explode('/', $tz, 2), 2, $tz);
                $grouped[$region][$tz] = str_replace('_', ' ', $city);
            }

            ksort($grouped);
            return $grouped;
        });
    }


    public static function states(string $countryCode): array
    {
        $country = self::get($countryCode);
        return $country['states'] ?? [];
    }


    public static function currencies(): array
    {
        return Cache::remember('currencies.all', now()->addMonth(), function () {
            $countries = self::countries()->all();
            $currencies = [];

            foreach ($countries as $country) {
                if (!isset($country->currencies) || $country->currencies->isEmpty()) {
                    continue;
                }

                $currencyCode = $country->currencies->keys()->first();
                if (!$currencyCode || isset($currencies[$currencyCode])) {
                    continue;
                }

                $currencyData = $country->currencies->first();
                $currencyName = null;
                $currencySymbol = null;

                if (is_array($currencyData)) {
                    $currencyName = $currencyData['name'] ?? null;
                    $currencySymbol = $currencyData['symbol'] ?? $currencyCode;
                } elseif (is_object($currencyData)) {
                    $currencyName = $currencyData->name ?? null;
                    $currencySymbol = $currencyData->symbol ?? $currencyCode;
                }

                if ($currencyName) {
                    $currencies[$currencyCode] = [
                        'code' => $currencyCode,
                        'name' => $currencyName,
                        'symbol' => $currencySymbol,
                        'symbol_native' => $currencySymbol,
                        'countries' => [], // will populate next
                    ];
                }
            }

            // Associate countries with each currency
            foreach ($countries as $country) {
                if (!isset($country->currencies) || $country->currencies->isEmpty()) {
                    continue;
                }

                $currencyCode = $country->currencies->keys()->first();
                if ($currencyCode && isset($currencies[$currencyCode])) {
                    $currencies[$currencyCode]['countries'][] = [
                        'code' => $country->cca2 ?? '',
                        'name' => $country->name->common ?? '',
                    ];
                }
            }

            ksort($currencies);
            return $currencies;
        });
    }

    public static function currencyOptions($includeEmpty = true): array
    {
        $currencies = self::currencies(); // keep your currencies() method

        $options = [];

        if ($includeEmpty) {
            $options[''] = 'Select Currency';
        }

        foreach ($currencies as $currency) {
            $options[$currency['code']] = "{$currency['name']} ({$currency['code']}) - {$currency['symbol']}";
        }

        return $options;
    }

    /**
     * Get country options for dropdown
     */
    public static function countryOptions($includeEmpty = true, $locale = 'en'): array
    {
        $countries = self::all($locale);

        $options = [];

        if ($includeEmpty) {
            $options[''] = 'Select Country';
        }

        foreach ($countries as $country) {
            $options[$country['code']] = $country['name'];
        }

        return $options;
    }

}
