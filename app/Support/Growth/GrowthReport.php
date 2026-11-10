<?php

namespace App\Support\Growth;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Numbers behind Administration › Growth, for one period and one site (or all seven). Works on MySQL and SQLite.
 *
 * Definitions used everywhere on the hub:
 *  - a "visit" is one visitor on one site on one day (the anonymous visitor id rotates daily by design);
 *  - its landing page and source are those of the first page view of that visit;
 *  - a conversion's source / landing page is the visitor's first touch that browser session (sent by the site);
 *  - conversion rate = conversions ÷ visits.
 */
class GrowthReport
{
    public readonly Carbon $from;

    public readonly Carbon $to;

    public readonly int $days;

    /** Either the last $days days, or an explicit from–to date range (the API). */
    public function __construct(int $days, public readonly ?string $site = null, ?Carbon $from = null, ?Carbon $to = null)
    {
        $this->to = ($to ?? now())->copy()->endOfDay();
        $this->from = ($from ?? now()->subDays($days - 1))->copy()->startOfDay();
        $this->days = (int) $this->from->diffInDays($this->to->copy()->startOfDay()) + 1;
    }

    public function views(): Builder
    {
        return DB::table('growth_page_views')->whereBetween('created_at', [$this->from, $this->to])
            ->when($this->site, fn ($q) => $q->where('site', $this->site));
    }

    public function conversions(): Builder
    {
        return DB::table('growth_events')->where('name', 'conversion')->whereBetween('created_at', [$this->from, $this->to])
            ->when($this->site, fn ($q) => $q->where('site', $this->site));
    }

    /** Visits per site, with the landing page and source of each visit, aggregated. Memoised. */
    public function landings(): Collection
    {
        return $this->landings ??= DB::table('growth_page_views as v')
            ->joinSub($this->views()->selectRaw('min(id) as id')->groupBy('site', 'visitor', DB::raw('date(created_at)')), 'f', 'f.id', '=', 'v.id')
            ->selectRaw('v.site, v.path, v.utm_source, v.utm_medium, v.utm_campaign, v.referrer_host, count(*) as n')
            ->groupBy('v.site', 'v.path', 'v.utm_source', 'v.utm_medium', 'v.utm_campaign', 'v.referrer_host')
            ->get()
            ->map(function ($r) {
                $source = $r->utm_source ?: ($r->referrer_host ?: 'direct');
                $medium = $r->utm_medium ?: ($r->referrer_host ? (Analytics::isSearch($r->referrer_host) ? 'organic' : 'referral') : null);

                return (object) ['site' => $r->site, 'path' => $r->path, 'source' => $source, 'medium' => $medium, 'campaign' => $r->utm_campaign,
                    'channel' => Analytics::channel($source, $medium), 'visits' => (int) $r->n];
            });
    }

    private ?Collection $landings = null;

    private ?Collection $conversionRows = null;

    /** Conversions aggregated by site, name, first-touch source/medium/campaign and landing page. Memoised. */
    public function conversionRows(): Collection
    {
        return $this->conversionRows ??= $this->conversions()
            ->selectRaw("site, label, coalesce(source, 'direct') as source, medium, campaign, landing_path, count(*) as n")
            ->groupBy('site', 'label', 'source', 'medium', 'campaign', 'landing_path')->get()
            ->map(function ($r) {
                $r->n = (int) $r->n;
                $r->channel = Analytics::channel($r->source, $r->medium);

                return $r;
            });
    }

    /** One row per site: visits, page views, conversions, rate, organic share, last beacon ever. */
    public function perSite(): Collection
    {
        $views = $this->views()->selectRaw('site, count(*) as views')->groupBy('site')->pluck('views', 'site');
        $visits = $this->landings()->groupBy('site')->map->sum('visits');
        $organic = $this->landings()->where('channel', 'organic')->groupBy('site')->map->sum('visits');
        $paid = $this->landings()->where('channel', 'paid')->groupBy('site')->map->sum('visits');
        $conv = $this->conversionRows()->groupBy('site')->map->sum('n');
        $last = DB::table('growth_page_views')->selectRaw('site, max(created_at) as at')->groupBy('site')->pluck('at', 'site');

        return collect(Sites::all())->when($this->site, fn ($c) => $c->only([$this->site]))->map(function ($s, $key) use ($views, $visits, $organic, $paid, $conv, $last) {
            $v = (int) ($visits[$key] ?? 0);
            $c = (int) ($conv[$key] ?? 0);

            return (object) [
                'key' => $key, 'name' => $s['name'], 'url' => $s['url'], 'product' => $s['product'], 'color' => $s['color'],
                'visits' => $v, 'views' => (int) ($views[$key] ?? 0), 'conversions' => $c,
                'rate' => $v ? round($c / $v * 100, 2) : null,
                'organic' => $v ? round(($organic[$key] ?? 0) / $v * 100) : null,
                'paid' => $v ? round(($paid[$key] ?? 0) / $v * 100) : null,
                'last_seen' => isset($last[$key]) ? Carbon::parse($last[$key]) : null,
            ];
        });
    }

    /** Per day (per week over 90 days): views, visits, conversions. */
    public function series(): Collection
    {
        $daily = $this->views()->selectRaw('date(created_at) as d, count(*) as views, count(distinct visitor) as visits')->groupBy('d')->get()->keyBy('d');
        $conv = $this->conversions()->selectRaw('date(created_at) as d, count(*) as n')->groupBy('d')->pluck('n', 'd');
        $series = collect(range(0, $this->days - 1))->map(function ($i) use ($daily, $conv) {
            $d = $this->from->copy()->addDays($i)->toDateString();

            return ['date' => $d, 'views' => (int) ($daily[$d]->views ?? 0), 'visits' => (int) ($daily[$d]->visits ?? 0), 'conversions' => (int) ($conv[$d] ?? 0)];
        });

        return $this->days > 90
            ? $series->chunk(7)->map(fn ($w) => ['date' => $w->first()['date'], 'views' => $w->sum('views'), 'visits' => $w->sum('visits'), 'conversions' => $w->sum('conversions')])->values()
            : $series->values();
    }

    /** Visits, conversions and rate per marketing channel (paid, organic, social, …). */
    public function channels(): Collection
    {
        $rows = [];
        foreach ($this->landings() as $r) {
            $rows[$r->channel] ??= (object) ['channel' => $r->channel, 'visits' => 0, 'conversions' => 0];
            $rows[$r->channel]->visits += $r->visits;
        }
        foreach ($this->conversionRows() as $r) {
            $rows[$r->channel] ??= (object) ['channel' => $r->channel, 'visits' => 0, 'conversions' => 0];
            $rows[$r->channel]->conversions += $r->n;
        }

        return collect($rows)->map(fn ($r) => $this->withRate($r))->sortByDesc('visits')->values();
    }

    /** Conversions by site × conversion name. */
    public function bySiteAndName(): Collection
    {
        return $this->conversionRows()->groupBy(fn ($r) => $r->site.'|'.$r->label)
            ->map(fn ($rows) => (object) ['site' => $rows->first()->site, 'name' => $rows->first()->label, 'n' => $rows->sum('n')])
            ->sortByDesc('n')->values();
    }

    /** Visits and conversions per site + source/medium/campaign (conversions whose first touch was before the period count with 0 visits). */
    public function bySource(int $limit = 25): Collection
    {
        $key = fn ($r) => implode('|', [$r->site, $r->source, $r->medium, $r->campaign]);
        $row = fn ($f) => (object) ['site' => $f->site, 'source' => $f->source, 'medium' => $f->medium, 'campaign' => $f->campaign, 'channel' => $f->channel, 'visits' => 0, 'conversions' => 0];
        $rows = [];
        foreach ($this->landings() as $r) {
            $rows[$key($r)] ??= $row($r);
            $rows[$key($r)]->visits += $r->visits;
        }
        foreach ($this->conversionRows() as $r) {
            $rows[$key($r)] ??= $row($r);
            $rows[$key($r)]->conversions += $r->n;
        }

        return collect($rows)->map(fn ($r) => $this->withRate($r))
            ->sortBy([['conversions', 'desc'], ['visits', 'desc']])->take($limit)->values();
    }

    /** Visits and conversions per site + landing page. */
    public function byLanding(int $limit = 25): Collection
    {
        $conv = $this->conversionRows()->groupBy(fn ($r) => $r->site.'|'.$r->landing_path)->map->sum('n');

        return $this->landings()->groupBy(fn ($r) => $r->site.'|'.$r->path)->map(function ($rows, $k) use ($conv) {
            return $this->withRate((object) ['site' => $rows->first()->site, 'path' => $rows->first()->path, 'visits' => $rows->sum('visits'), 'conversions' => (int) ($conv[$k] ?? 0)]);
        })->sortBy([['conversions', 'desc'], ['visits', 'desc']])->take($limit)->values();
    }

    /** Funnel per site: visits → engaged (2+ pages or 30s+ on a page) → converted visits. */
    public function funnel(): Collection
    {
        $visits = DB::query()->fromSub($this->views()
            ->selectRaw('site, visitor, date(created_at) as d, count(*) as n, max(coalesce(duration_ms, 0)) as mx')
            ->groupBy('site', 'visitor', 'd'), 'vd')
            ->selectRaw('site, count(*) as visits, sum(case when n >= 2 or mx >= 30000 then 1 else 0 end) as engaged')
            ->groupBy('site')->get()->keyBy('site');
        $converted = DB::query()->fromSub($this->conversions()->whereNotNull('visitor')
            ->selectRaw('site, visitor, date(created_at) as d')->groupBy('site', 'visitor', 'd'), 'cd')
            ->selectRaw('site, count(*) as n')->groupBy('site')->pluck('n', 'site');

        return $this->perSite()->map(fn ($s) => (object) [
            'key' => $s->key, 'name' => $s->name, 'color' => $s->color,
            'visits' => (int) ($visits[$s->key]->visits ?? 0),
            'engaged' => (int) ($visits[$s->key]->engaged ?? 0),
            'converted' => (int) ($converted[$s->key] ?? 0),
        ])->values();
    }

    /** Pages with real traffic and no conversion that started or happened there. */
    public function leakyPages(int $minViews = 30, int $limit = 10): Collection
    {
        $converting = $this->conversionRows()->flatMap(fn ($r) => [$r->site.'|'.$r->landing_path])
            ->merge($this->conversions()->selectRaw('site, path')->distinct()->get()->map(fn ($r) => $r->site.'|'.$r->path))
            ->flip();

        return $this->views()->selectRaw('site, path, count(*) as views, avg(case when duration_ms between 1000 and 1800000 then duration_ms end) as avg_ms')
            ->groupBy('site', 'path')->having('views', '>=', $minViews)->orderByDesc('views')->limit(200)->get()
            ->reject(fn ($r) => isset($converting[$r->site.'|'.$r->path]))
            ->take($limit)->values();
    }

    /** Every site's last beacon, for "is the tracker installed?" */
    public static function lastSeen(): Collection
    {
        return DB::table('growth_page_views')->selectRaw('site, max(created_at) as at')->groupBy('site')->pluck('at', 'site')
            ->map(fn ($at) => Carbon::parse($at));
    }

    private function withRate(object $r): object
    {
        $r->rate = $r->visits ? round($r->conversions / $r->visits * 100, 2) : null;

        return $r;
    }
}
