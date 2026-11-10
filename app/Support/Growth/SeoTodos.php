<?php

namespace App\Support\Growth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** The owner's per-site checklist on the SEO tab (growth_todos). Items are fixed here; rows hold done + a note. */
class SeoTodos
{
    public const ITEMS = [
        'tracker' => ['Tracker live', 'Set SHARET_ANALYTICS_URL=https://sharet.africa on the site so its public layout loads sharet-analytics.js (the hub shows "last data" once it works).'],
        'search_console' => ['Search Console: domain verified', 'Add a Domain property in Google Search Console and verify it with the DNS TXT record.'],
        'sitemap' => ['Sitemap submitted', 'Search Console › Sitemaps: submit /sitemap.xml, then request indexing of the key pages.'],
        'bing' => ['Bing Webmaster Tools', 'Import the site from Search Console (one click once Search Console is verified).'],
        'business_profile' => ['Google Business Profile', 'Only where people can visit or the business serves an area; use the real address and phone already on the site.'],
        'directories' => ['Directories & backlinks', 'List the site in Kenyan business directories and the niche lists below; link it from the sister sites.'],
        'pixels' => ['GA4 / Meta pixel (optional)', 'Only if you will run Google or Meta ads: set GA4_MEASUREMENT_ID / META_PIXEL_ID where the site supports them.'],
    ];

    /** Niche hint for the directories item. */
    public const NICHE = [
        'sharet' => 'Fintech and developer lists (payments API directories, GitHub README links, Kenyan startup lists).',
        'breve' => 'SME tools lists, accountant and bookkeeper communities, Kenyan small-business groups.',
        'aiyotickets' => 'Event calendars and "what\'s on in Nairobi" listings, venue and organiser pages.',
        'eserviz' => 'Kenyan business directories, motoring / land / KRA help forums and "how to" guides that link to the service pages; a Business Profile if there is an office customers visit.',
        'scharge' => 'Property and real-estate directories, landlord and agent associations, property forums.',
        'loans' => 'Boda boda SACCO and rider groups, e-mobility lists, motorbike dealer partners.',
        'shoope' => 'Online-seller communities (Instagram / TikTok / WhatsApp sellers), e-commerce lists.',
    ];

    /** [site => [item => (object) done, note, updated_at]] for the given sites. */
    public static function forSites(array $sites): Collection
    {
        $rows = DB::table('growth_todos')->whereIn('site', $sites)->get()->groupBy('site');

        return collect($sites)->mapWithKeys(fn ($site) => [$site => collect(self::ITEMS)->map(function ($def, $item) use ($rows, $site) {
            $r = ($rows[$site] ?? collect())->firstWhere('item', $item);

            return (object) ['item' => $item, 'label' => $def[0], 'hint' => $item === 'directories' ? $def[1].' '.(self::NICHE[$site] ?? '') : $def[1],
                'done' => (bool) ($r->done ?? false), 'note' => $r->note ?? null, 'updated_at' => $r->updated_at ?? null];
        })]);
    }
}
