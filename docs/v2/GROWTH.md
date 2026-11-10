# Growth hub (Administration › Growth)

One first-party tracker on sharet.africa collects page views and conversions from the seven Sharet-family sites.
Staff with `analytics.view` see all of them at `/admin/growth`; each business can read its own site's numbers
through the API to show a localized Growth tab in its own app.

| Piece | Where |
|---|---|
| Tracker | `public/js/sharet-analytics.js` → `https://sharet.africa/js/sharet-analytics.js` |
| Collector | `POST /t/x` (`GrowthCollectController`): no session, no cookies, no CSRF; origin allow-list; always 204 |
| Sites, origins, owners, conversion names | `config/growth.php` |
| Tables | `database/schema/2026_11_10_growth_hub.sql` (hand-applied, not a migration) |
| Hub | `Admin\GrowthController`, `resources/views/admin/growth/*`, `App\Support\Growth\*` |
| SEO audit | `php artisan growth:seo-audit [site] [--sample=5]`, scheduled daily 05:30 (also in Scheduled jobs) |
| API | `GET /api/v1/growth/summary` (`Api\V1\GrowthController`) |

## On each site

In the public layout `<head>`, production only, when `SHARET_ANALYTICS_URL` is set (default `https://sharet.africa`):

```html
<script defer src="https://sharet.africa/js/sharet-analytics.js" data-site="breve"></script>
```

Keys: `sharet`, `breve`, `aiyotickets`, `eserviz`, `scharge`, `loans`, `shoope`. A beacon is kept only when its key
matches the page's origin (`config/growth.php` `origins`). Conversions, on the page shown after the action:

```html
<script>(window.sa=window.sa||[]).push(['conversion','signup'])</script>
```

Accepted names: `config('growth.conversions')` (`signup`, `lead`, `purchase`, `booking`, `loan_application`,
`api_keys`, `invoice_created`, plus a few extras). CTA clicks: `data-sa-click="hero_get_started"`.
The script sends nothing when Do-Not-Track or Global Privacy Control is on, never reads form fields, sends no
query string (only `utm_*`), and keeps the first touch (source, UTM, landing page) in `sessionStorage` per site.

## API: `GET /api/v1/growth/summary` ("growth.summary" v1)

`Authorization: Bearer sk_live_…` (or `sk_test_…`), scope `read`. The key's business must own the site:
`config('growth.sites.<key>.sharet_id')` (or `company_id`). Query: `from`, `to` (`YYYY-MM-DD`, default the last
30 days, at most 366 days), optional `site` (one of the business's own keys). Errors are `application/problem+json`:
401 bad key, 403 `growth_no_site` / `growth_other_site` / missing scope, 422 `invalid_date_range`, 503 `growth_unavailable`.

```json
{
  "object": "growth.summary", "version": 1, "from": "2026-10-12", "to": "2026-11-10", "generated_at": "…",
  "sites": [{
    "key": "breve", "name": "Breve", "url": "https://breve.sharet.africa", "last_data_at": "…|null",
    "totals": { "visits": 120, "page_views": 310, "conversions": 6, "conversion_rate": 5.0, "organic_share": 40, "paid_share": 10 },
    "daily": [{ "date": "2026-10-12", "page_views": 9, "visits": 4, "conversions": 0 }],
    "conversions": [{ "name": "signup", "label": "Sign-up", "count": 5 }],
    "channels": [{ "channel": "organic", "label": "Organic search", "visits": 48, "share": 40.0, "conversions": 3, "conversion_rate": 6.25 }],
    "sources": [{ "source": "google", "medium": "cpc", "campaign": "sme", "visits": 12, "conversions": 2, "conversion_rate": 16.67 }],
    "landing_pages": [{ "path": "/invoicing", "visits": 30, "conversions": 4, "conversion_rate": 13.33 }],
    "seo": { "checked_at": "…|null", "sitemap_urls": 14, "counts": { "red": 0, "amber": 3, "green": 20 },
             "fix_first": [{ "level": "amber", "url": "https://breve.sharet.africa/", "text": "No canonical link." }] }
  }]
}
```

A visit is one visitor on one site on one day (the anonymous id rotates daily); rates are percentages (null when
there were no visits). Responses may be cached for 5 minutes (`Cache-Control: private, max-age=300`).
