<?php

namespace App\Console\Commands;

use App\Support\Growth\Analytics;
use App\Support\Growth\SeoAudit;
use App\Support\Growth\Sites;
use Illuminate\Console\Command;

/** Administration › Growth › SEO: fetch each Sharet-family site's home page, robots.txt, sitemap and a sample of pages. */
class GrowthSeoAudit extends Command
{
    protected $signature = 'growth:seo-audit {site? : One site key (default: all seven)} {--sample= : Sitemap pages to check per site}';

    protected $description = 'SEO audit of the Sharet-family sites for the Growth hub';

    public function handle(SeoAudit $audit): int
    {
        if (! Analytics::ready()) {
            $this->error('Growth tables missing: apply database/schema/2026_11_10_growth_hub.sql first.');

            return self::FAILURE;
        }
        $sites = $this->argument('site') ? [$this->argument('site')] : Sites::keys();
        foreach ($sites as $site) {
            if (! in_array($site, Sites::keys(), true)) {
                $this->error("Unknown site $site. Use one of: ".implode(', ', Sites::keys()));

                return self::FAILURE;
            }
            $rows = $audit->run($site, $this->option('sample') !== null ? (int) $this->option('sample') : null);
            $f = collect($rows)->flatMap(fn ($r) => json_decode($r['findings'], true));
            $this->line(sprintf('%-12s %2d URLs checked · %d red · %d amber · %d green', $site, count($rows),
                $f->where('level', 'red')->count(), $f->where('level', 'amber')->count(), $f->where('level', 'green')->count()));
        }

        return self::SUCCESS;
    }
}
