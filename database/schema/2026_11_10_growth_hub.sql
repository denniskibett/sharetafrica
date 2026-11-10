-- sharet.africa: Growth hub (Administration › Growth). Hand-applied SQL, not a migration. Safe to re-run.
--   mysql shar_main < database/schema/2026_11_10_growth_hub.sql
-- Until it is applied the collector (POST /t/x) records nothing and the hub shows a set-up note.
-- Nothing existing is altered: sharet.africa's own page_views / page_clicks stay as they are.
--
-- Privacy: no cookies and no IP addresses. `visitor` is a hash of IP + browser + date + APP_KEY, so it changes
-- every day and cannot be traced back to a person. Paths are stored without query strings and token-like
-- segments are masked. Visitors who send Do-Not-Track or Global Privacy Control are never recorded.

-- Page views from the seven sites (public/js/sharet-analytics.js data-site="…")
CREATE TABLE IF NOT EXISTS `growth_page_views` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(20) NOT NULL,                  -- sharet | breve | aiyotickets | eserviz | scharge | loans | shoope
  `visitor` char(16) NOT NULL,
  `view_key` char(16) NOT NULL,                 -- random per page load, to attach time on page later
  `path` varchar(255) NOT NULL,
  `referrer_host` varchar(120) DEFAULT NULL,    -- external sites only
  `utm_source` varchar(80) DEFAULT NULL,
  `utm_medium` varchar(80) DEFAULT NULL,
  `utm_campaign` varchar(120) DEFAULT NULL,
  `country` char(2) DEFAULT NULL,
  `device` varchar(10) NOT NULL,                -- mobile | tablet | desktop
  `browser` varchar(20) DEFAULT NULL,
  `viewport_w` smallint unsigned DEFAULT NULL,
  `duration_ms` int unsigned DEFAULT NULL,      -- visible time on the page
  `scroll_pct` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `growth_page_views_view_key_unique` (`view_key`),
  KEY `growth_page_views_site_created_index` (`site`, `created_at`),
  KEY `growth_page_views_site_visitor_index` (`site`, `visitor`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conversions (name = conversion, label = signup | lead | purchase | booking | loan_application | api_keys | …),
-- CTA clicks (name = click, label = the data-sa-click value) and contact / outbound clicks.
-- source / medium / campaign / landing_path: the visitor's first touch that browser session on that site.
CREATE TABLE IF NOT EXISTS `growth_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `label` varchar(190) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `visitor` char(16) DEFAULT NULL,
  `source` varchar(120) DEFAULT NULL,           -- utm_source, else referring host, else 'direct'
  `medium` varchar(80) DEFAULT NULL,
  `campaign` varchar(120) DEFAULT NULL,
  `landing_path` varchar(255) DEFAULT NULL,
  `device` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `growth_events_site_name_created_index` (`site`, `name`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Latest SEO audit per site (php artisan growth:seo-audit, daily 05:30). Each run replaces that site's rows.
CREATE TABLE IF NOT EXISTS `growth_seo_checks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(20) NOT NULL,
  `kind` varchar(10) NOT NULL,                  -- home | robots | sitemap | page
  `url` varchar(500) NOT NULL,
  `status` smallint unsigned DEFAULT NULL,      -- HTTP status, NULL = no answer
  `response_ms` int unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description_length` smallint unsigned DEFAULT NULL,
  `canonical` varchar(500) DEFAULT NULL,
  `og_tags` varchar(255) DEFAULT NULL,          -- og:/twitter: properties found, comma separated
  `jsonld_types` varchar(255) DEFAULT NULL,     -- schema.org @type values, comma separated
  `h1_count` smallint unsigned DEFAULT NULL,
  `noindex` tinyint(1) NOT NULL DEFAULT 0,
  `sitemap_urls` int unsigned DEFAULT NULL,     -- sitemap rows only
  `findings` text,                              -- JSON [{"level":"red|amber|green","text":"…"}]
  `checked_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `growth_seo_checks_site_index` (`site`, `kind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The owner's per-site checklist (Search Console, sitemap submitted, Bing, Business Profile, directories, tracker)
CREATE TABLE IF NOT EXISTS `growth_todos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(20) NOT NULL,
  `item` varchar(40) NOT NULL,                  -- key from App\Support\Growth\SeoTodos::ITEMS
  `done` tinyint(1) NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `growth_todos_site_item_unique` (`site`, `item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
