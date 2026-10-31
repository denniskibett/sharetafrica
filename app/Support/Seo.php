<?php

namespace App\Support;

/**
 * One source for what search engines and AI assistants learn about Sharet:
 * the public pages (sitemap, llms.txt) and the organisation facts (JSON-LD).
 * Wording follows the site's own pages; keep the two in step.
 */
class Seo
{
    /** Public pages: route => [view, summary for llms.txt, sitemap priority] */
    public const PAGES = [
        'home' => ['index', 'Send money to anyone in East Africa (M-PESA, MTN, Airtel, Vodacom or bank) from one Sharet wallet. Internal transfers are free; the person you pay never has to sign up.', '1.0'],
        'personal' => ['personal', 'Personal wallet: send to anyone in East Africa, zero internal fees, recipient needs no account. Includes a worked cost comparison.', '0.9'],
        'merchants' => ['merchants', 'Merchants: accept payments from every rail (M-PESA, MTN, Airtel, cards, banks) into one wallet and settle externally on demand.', '0.9'],
        'trade' => ['trade', 'Trade: pay suppliers in China, the UAE, India and Europe, receive from international buyers, embedded trade finance, settle locally in KES, TZS or UGX.', '0.8'],
        'developers' => ['developers', 'Developers: REST API for the Sharet ledger with idempotent writes, HMAC-signed webhooks, RFC 7807 errors and a free sandbox for Kenya, Tanzania and Uganda rails.', '0.8'],
        'company' => ['company', 'Company: founded 2026 in Nairobi, named team, regulatory posture and the companies Sharet runs.', '0.6'],
        'contact' => ['contact', 'Contact: merchant onboarding, personal wallets, trade accounts, developer access, press and careers.', '0.6'],
    ];

    /** Businesses Sharet owns and runs (also shown on /company#companies). */
    public const COMPANIES = [
        ['Breve', 'https://breve.sharet.africa/', 'Business management for African businesses.'],
        ['Aimzone Ventures', 'https://loans.sharet.africa/', 'Emergency funding and trade finance in Kenya.'],
        ['scharge', 'https://scharge.sharet.africa/', 'Digital property management, starting in Kenya.'],
        ['aiyotickets', 'https://aiyotickets.com/', 'Event and sports tickets, experiences and invitations, paid with M-Pesa.'],
        ['eServiz', 'https://eserviz.aiyotickets.com/', 'Government errands, done for you.'],
        ['Oteli', 'https://thewillishotel.co.ke/', 'Hotel management.'],
    ];

    public static function organization(): array
    {
        $base = rtrim(config('app.url'), '/');
        $place = fn ($type, $name) => ['@type' => $type, 'name' => $name];

        return [
            '@type' => ['Organization', 'FinancialService'],
            '@id' => $base.'/#org',
            'name' => 'Sharet Africa',
            'alternateName' => 'Sharet',
            'url' => $base.'/',
            'logo' => asset('images/logo/apple-touch-icon.png'),
            'image' => asset('images/og/sharet-og.png'),
            'description' => 'Cheaper payments across East Africa. One wallet and one ledger across M-PESA, MTN, Airtel, Vodacom and banks.',
            'foundingDate' => '2026',
            'email' => 'hello@sharet.africa',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Nairobi', 'addressRegion' => 'Nairobi', 'addressCountry' => 'KE'],
            'geo' => ['@type' => 'GeoCoordinates', 'latitude' => -1.286389, 'longitude' => 36.817223],
            'areaServed' => [
                $place('City', 'Nairobi'), $place('Country', 'Kenya'), $place('Country', 'Uganda'), $place('Country', 'Tanzania'),
                $place('Place', 'East Africa'), $place('Continent', 'Africa'),
            ],
            'currenciesAccepted' => 'KES, UGX, TZS',
            'contactPoint' => [
                ['@type' => 'ContactPoint', 'contactType' => 'customer support', 'email' => 'support@sharet.africa', 'areaServed' => ['KE', 'UG', 'TZ'], 'availableLanguage' => 'en'],
                ['@type' => 'ContactPoint', 'contactType' => 'press', 'email' => 'press@sharet.africa'],
            ],
            'subOrganization' => array_map(fn ($c) => ['@type' => 'Organization', 'name' => $c[0], 'url' => $c[1], 'description' => $c[2]], self::COMPANIES),
        ];
    }

    /** JSON-LD graph for a public page. */
    public static function graph(string $title, string $description): array
    {
        $base = rtrim(config('app.url'), '/');
        $url = url()->current();

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                self::organization(),
                ['@type' => 'WebSite', '@id' => $base.'/#website', 'url' => $base.'/', 'name' => 'Sharet Africa', 'inLanguage' => 'en-KE', 'publisher' => ['@id' => $base.'/#org']],
                ['@type' => 'WebPage', '@id' => $url.'#page', 'url' => $url, 'name' => $title, 'description' => $description, 'inLanguage' => 'en-KE',
                    'isPartOf' => ['@id' => $base.'/#website'], 'about' => ['@id' => $base.'/#org'], 'primaryImageOfPage' => asset('images/og/sharet-og.png')],
            ],
        ];
    }
}
