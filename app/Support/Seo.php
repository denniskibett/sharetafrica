<?php

namespace App\Support;

/**
 * One source for what search engines and AI assistants learn about Sharet:
 * the public pages (sitemap, llms.txt) and the organisation facts (JSON-LD).
 * Wording follows the site's own pages; keep the two in step.
 */
class Seo
{
    /** Public pages: route => [view, summary for llms.txt, sitemap priority, optional llms.txt label] */
    public const PAGES = [
        'home' => ['index', 'Send money to anyone in East Africa (M-PESA, MTN, Airtel, Vodacom or bank) from one Sharet wallet. Internal transfers are free; the person you pay never has to sign up.', '1.0'],
        'personal' => ['personal', 'Personal wallet: send to anyone in East Africa, zero internal fees, recipient needs no account. Includes a worked cost comparison.', '0.9'],
        'merchants' => ['merchants', 'Merchants: accept payments from every rail (M-PESA, MTN, Airtel, cards, banks) into one wallet and settle externally on demand.', '0.9'],
        'trade' => ['trade', 'Trade: pay suppliers in China, the UAE, India and Europe, receive from international buyers, embedded trade finance, settle locally in KES, TZS or UGX.', '0.8'],
        'developers' => ['developers', 'Developers: REST API for the Sharet ledger with idempotent writes, HMAC-signed webhooks, RFC 7807 errors and a free sandbox for Kenya, Tanzania and Uganda rails.', '0.8'],
        'docs' => ['docs/index', 'API docs: payment requests (pay link + M-PESA prompt), payouts to M-PESA, Airtel Money, T-Kash, paybills, tills and banks, batch settlements, split settlement, connected accounts, signed webhooks, errors and the sandbox.', '0.8', 'API docs'],
        'landing.mpesa-api' => ['landing/mpesa-api', 'M-PESA API for Kenya: ask a customer to pay and an M-PESA prompt (STK push) goes to their phone; the money lands in your Sharet wallet and a signed webhook tells your server.', '0.8', 'M-PESA API (STK push)'],
        'landing.gateway' => ['landing/payment-gateway-kenya', 'Payment gateway for Kenya: hosted pay links that customers pay by M-PESA or from a Sharet wallet, with webhooks, split settlement and payouts from the same balance.', '0.8', 'Payment gateway Kenya'],
        'landing.bulk' => ['landing/bulk-payments-kenya', 'Bulk payments and B2C payouts in Kenya: pay up to 500 people in one API call to M-PESA, Airtel Money, T-Kash, paybills, tills, banks or Sharet wallets, with approvals for large batches.', '0.8', 'Bulk payouts (B2C)'],
        'company' => ['company', 'Company: founded 2026 in Nairobi, named team, regulatory posture and the companies Sharet runs.', '0.6'],
        'privacy' => ['privacy', 'Privacy: what Sharet collects, why, who sees it, how long it is kept, and your rights under the Kenya Data Protection Act, 2019.', '0.3'],
        'contact' => ['contact', 'Contact: merchant onboarding, personal wallets, trade accounts, developer access, press and careers.', '0.6'],
    ];

    /** Businesses Sharet owns and runs: name, site, what it does, sector (shown on /company#companies). */
    public const COMPANIES = [
        ['Breve', 'https://breve.sharet.africa/', 'Business management for African businesses.', 'Business software'],
        ['Aimzone Ventures', 'https://loans.sharet.africa/', 'Emergency funding and trade finance in Kenya.', 'Finance'],
        ['scharge', 'https://scharge.sharet.africa/', 'Digital property management, starting in Kenya.', 'Property'],
        ['aiyotickets', 'https://aiyotickets.com/', 'Event and sports tickets, experiences and invitations, paid with M-Pesa.', 'Tickets & experiences'],
        ['eServiz', 'https://eserviz.aiyotickets.com/', 'Government errands, done for you.', 'Government services'],
        ['Oteli', 'https://thewillishotel.co.ke/', 'Hotel management, running The Willis Hotel.', 'Hospitality'],
    ];

    /** The sister products every Sharet site links to (name, url, what it does). Sharet Africa Ltd is the parent. */
    public const SISTERS = [
        ['Breve', 'https://breve.sharet.africa', 'Invoicing and M-Pesa payments for small businesses.'],
        ['Aiyotickets', 'https://aiyotickets.com', 'Events and tickets in Nairobi, paid with M-Pesa.'],
        ['eServiz', 'https://eserviz.aiyotickets.com', 'Government errands done for you (NTSA, Lands, KRA).'],
        ['Scharge', 'https://scharge.sharet.africa', 'Property and rent management.'],
        ['Sharet Loans', 'https://loans.sharet.africa', 'Boda boda and e-bike loans.'],
        ['Shoope', 'https://shoope.africa', 'Safe payments for online shops: escrow for Instagram, TikTok and Marketplace sellers.'],
    ];

    /** The group companies from Website › Companies, as [name, url, description, sector]; the list above if the table is not there yet. */
    public static function companies(): array
    {
        try {
            $rows = \App\Models\GroupCompany::listed()->get();
            if ($rows->isNotEmpty()) {
                return $rows->map(fn ($c) => [$c->name, $c->url ?: route('companies.show', $c->slug), $c->tagline, $c->sector, $c->slug])->all();
            }
        } catch (\Throwable) {
        }

        return self::COMPANIES;
    }

    public static function organization(): array
    {
        $base = rtrim(config('app.url'), '/');
        $place = fn ($type, $name) => ['@type' => $type, 'name' => $name];

        return [
            '@type' => ['Organization', 'FinancialService'],
            '@id' => $base.'/#org',
            'name' => 'Sharet Africa',
            'legalName' => 'Sharet Africa Ltd',
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
            'subOrganization' => self::subOrganizations($base),
        ];
    }

    /** Website › Companies plus any sister product it doesn't list yet (matched by host), each pointing back to Sharet as parent. */
    public static function subOrganizations(?string $base = null): array
    {
        $base ??= rtrim(config('app.url'), '/');
        $host = fn ($url) => strtolower(preg_replace('/^www\./', '', (string) parse_url($url, PHP_URL_HOST)));
        $orgs = [];
        foreach (self::companies() as $c) {
            $orgs[$host($c[1]) ?: $c[0]] = ['@type' => 'Organization', 'name' => $c[0], 'url' => $c[1], 'description' => $c[2]];
        }
        foreach (self::SISTERS as [$name, $url, $about]) {
            $key = $host($url);
            if (! isset($orgs[$key])) {
                $orgs[$key] = ['@type' => 'Organization', 'name' => $name, 'url' => $url.'/', 'description' => $about];
            } elseif (strcasecmp($orgs[$key]['name'], $name) !== 0) {
                $orgs[$key]['alternateName'] = $name;
            }
        }

        return array_values(array_map(fn ($o) => $o + ['parentOrganization' => ['@id' => $base.'/#org']], $orgs));
    }

    /** FAQPage node from [question => answer] pairs (answers are plain text, shown on the page too). */
    public static function faq(array $qa): array
    {
        return ['@type' => 'FAQPage', '@id' => url()->current().'#faq', 'mainEntity' => array_map(
            fn ($q, $a) => ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]],
            array_keys($qa), array_values($qa))];
    }

    /** BreadcrumbList node: [name => url] in order, home first. */
    public static function breadcrumbs(array $trail): array
    {
        $items = [];
        foreach (array_merge(['Home' => route('home')], $trail) as $name => $url) {
            $items[] = ['@type' => 'ListItem', 'position' => count($items) + 1, 'name' => $name, 'item' => $url];
        }

        return ['@type' => 'BreadcrumbList', '@id' => url()->current().'#breadcrumbs', 'itemListElement' => $items];
    }

    /** The Sharet API as a schema.org WebAPI (facts from /docs). */
    public static function webApi(): array
    {
        $base = rtrim(config('app.url'), '/');

        return [
            '@type' => 'WebAPI', '@id' => $base.'/docs#api', 'name' => 'Sharet API v1',
            'description' => 'REST API for collecting payments by M-PESA prompt (STK push) or Sharet wallet, paying out to M-PESA, Airtel Money, T-Kash, paybills, tills, banks and Sharet wallets, batch settlements, split settlement, connected accounts and signed webhooks. Kenyan shillings.',
            'url' => $base.'/developers', 'documentation' => $base.'/docs', 'termsOfService' => $base.'/privacy',
            'provider' => ['@id' => $base.'/#org'], 'areaServed' => ['@type' => 'Country', 'name' => 'Kenya'],
        ];
    }

    /** JSON-LD graph for a public page. */
    public static function graph(string $title, string $description, array $extra = []): array
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
                ...$extra,
            ],
        ];
    }
}
