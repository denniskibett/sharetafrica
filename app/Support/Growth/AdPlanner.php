<?php

namespace App\Support\Growth;

use Illuminate\Support\Collection;

/**
 * Ad center: "what to push", computed from GrowthReport. Every rule is written out on the page (RULES) and the
 * planner says "not enough data" instead of guessing when the period has fewer than growth.min_visitors visits
 * or growth.min_conversions conversions.
 */
class AdPlanner
{
    /** Ad channels we can buy, and the traffic sources that are evidence for each. */
    public const AD_CHANNELS = [
        'google' => ['Google Search', '/(^|\.)(google|bing|duckduckgo|yahoo|ecosia|brave)(\.|$)|^(google|bing)$/'],
        'meta' => ['Meta (Facebook, Instagram, WhatsApp)', '/(^|\.)(facebook|fb|instagram|ig|whatsapp|wa|m\.facebook|l\.facebook)(\.|$)|^(facebook|instagram|whatsapp|fb|ig|meta)$/'],
        'tiktok' => ['TikTok', '/tiktok/'],
    ];

    /** Starting split when the data cannot tell yet (search first: people already looking for the product). */
    public const DEFAULT_CHANNEL_SPLIT = ['google' => 50, 'meta' => 35, 'tiktok' => 15];

    public const RULES = [
        'sites' => '70% of the budget follows results: each site gets a share in proportion to its conversions in the period. The other 30% is split evenly across every site that is sending data, so newer sites are still tested. Sites with no tracker data get nothing until they do.',
        'channels' => 'Each ad channel is scored by the conversion rate of the traffic that already comes from it (Google and other search engines for Google Search; Facebook, Instagram and WhatsApp for Meta; TikTok for TikTok), smoothed as (conversions + 1) ÷ (visits + 50) so one lucky conversion cannot swing it. 70% of the budget follows those scores, 30% is split evenly.',
        'fallback' => 'Not enough data: an even test split across sites, and 50% Google Search / 35% Meta / 15% TikTok across channels (search first, because those people are already looking for the product). Revisit once the period has enough visits and conversions.',
    ];

    public readonly int $minVisits;

    public readonly int $minConversions;

    public function __construct(public readonly GrowthReport $report)
    {
        $this->minVisits = (int) config('growth.min_visitors', 100);
        $this->minConversions = (int) config('growth.min_conversions', 10);
    }

    public function totals(): array
    {
        $visits = $this->report->landings()->sum('visits');
        $conversions = $this->report->conversionRows()->sum('n');

        return ['visits' => $visits, 'conversions' => $conversions, 'enough' => $visits >= $this->minVisits && $conversions >= $this->minConversions];
    }

    /** Site + landing page + source combinations that convert, best first (2+ conversions). */
    public function topCombos(int $limit = 10): Collection
    {
        $key = fn ($site, $path, $source) => "$site|$path|$source";
        $visits = [];
        foreach ($this->report->landings() as $r) {
            $visits[$key($r->site, $r->path, $r->source)] = ($visits[$key($r->site, $r->path, $r->source)] ?? 0) + $r->visits;
        }
        $rows = [];
        foreach ($this->report->conversionRows() as $r) {
            $k = $key($r->site, $r->landing_path, $r->source);
            $rows[$k] ??= (object) ['site' => $r->site, 'path' => $r->landing_path, 'source' => $r->source, 'channel' => $r->channel, 'conversions' => 0];
            $rows[$k]->conversions += $r->n;
        }

        return collect($rows)->filter(fn ($r) => $r->conversions >= 2)->map(function ($r) use ($visits, $key) {
            $r->visits = $visits[$key($r->site, $r->path, $r->source)] ?? 0;
            $r->rate = $r->visits ? round($r->conversions / $r->visits * 100, 2) : null;

            return $r;
        })->sortBy([['conversions', 'desc'], ['rate', 'desc']])->take($limit)->values();
    }

    /** Channels you can buy or grow, with some conversions and enough visits (a fifth of the threshold, at least 10), best rate first. */
    public function bestChannels(): Collection
    {
        $min = max(10, intdiv($this->minVisits, 5));

        // Direct visits cannot be bought, so they never count as the best channel
        return $this->report->channels()->filter(fn ($c) => $c->channel !== 'direct' && $c->visits >= $min && $c->conversions > 0)->sortByDesc('rate')->values();
    }

    /** Share of visits from organic search vs paid ads vs everything else. */
    public function organicVsPaid(): array
    {
        $ch = $this->report->channels();
        $total = max(1, $ch->sum('visits'));
        $organic = $ch->where('channel', 'organic')->sum('visits');
        $paid = $ch->where('channel', 'paid')->sum('visits');

        return ['organic' => round($organic / $total * 100), 'paid' => round($paid / $total * 100), 'other' => 100 - round($organic / $total * 100) - round($paid / $total * 100),
            'visits' => $ch->sum('visits')];
    }

    /** Budget split across sites: [site => ['name', 'share' %, 'amount', 'why']]. */
    public function siteSplit(int $budget = 0): Collection
    {
        $sites = $this->report->perSite();
        $live = $sites->filter(fn ($s) => $s->visits > 0);
        $enough = $this->totals()['enough'];
        $convTotal = max(1, $live->sum('conversions'));
        $n = max(1, $live->count());

        return $sites->map(function ($s) use ($live, $enough, $convTotal, $n, $budget) {
            $isLive = $live->has($s->key);
            $share = ! $isLive ? 0 : ($enough ? 70 * $s->conversions / $convTotal + 30 / $n : 100 / $n);

            return (object) ['key' => $s->key, 'name' => $s->name, 'color' => $s->color, 'share' => round($share, 1),
                'amount' => $budget ? (int) round($budget * $share / 100, -2) : null,
                'why' => ! $isLive ? 'No tracker data yet: install sharet-analytics.js first.'
                    : ($enough ? "{$s->conversions} conversions from {$s->visits} visits" : 'Even test split (not enough data yet)')];
        })->sortByDesc('share')->values();
    }

    /** Budget split across Google Search / Meta / TikTok. */
    public function channelSplit(int $budget = 0): Collection
    {
        $evidence = collect(self::AD_CHANNELS)->map(fn () => ['visits' => 0, 'conversions' => 0])->all();
        foreach ($this->report->landings() as $r) {
            if ($ch = self::adChannel($r->source)) {
                $evidence[$ch]['visits'] += $r->visits;
            }
        }
        foreach ($this->report->conversionRows() as $r) {
            if ($ch = self::adChannel($r->source)) {
                $evidence[$ch]['conversions'] += $r->n;
            }
        }
        $enough = $this->totals()['enough'] && collect($evidence)->sum('visits') >= $this->minVisits;
        $scores = collect($evidence)->map(fn ($e) => ($e['conversions'] + 1) / ($e['visits'] + 50));
        $sum = max(1e-9, $scores->sum());

        return collect(self::AD_CHANNELS)->map(function ($def, $ch) use ($evidence, $enough, $scores, $sum, $budget) {
            $share = $enough ? 70 * $scores[$ch] / $sum + 30 / count(self::AD_CHANNELS) : self::DEFAULT_CHANNEL_SPLIT[$ch];
            $e = $evidence[$ch];

            return (object) ['key' => $ch, 'name' => $def[0], 'share' => round($share, 1), 'visits' => $e['visits'], 'conversions' => $e['conversions'],
                'rate' => $e['visits'] ? round($e['conversions'] / $e['visits'] * 100, 2) : null,
                'amount' => $budget ? (int) round($budget * $share / 100, -2) : null, 'from_data' => $enough];
        })->sortByDesc('share')->values();
    }

    /** Plain-language recommendations, most useful first: [level push|fix|test|info, title, detail]. */
    public function recommendations(): Collection
    {
        $out = collect();
        $t = $this->totals();
        if (! $t['enough']) {
            $out->push(['info', 'Not enough data to rank yet',
                "This period has {$t['visits']} visits and {$t['conversions']} conversions; the Ad center needs at least {$this->minVisits} visits and {$this->minConversions} conversions before it ranks sites and channels. Until then, run small test campaigns with tagged links (UTM builder below) and check the sites below that send no data."]);
        }

        foreach ($this->report->perSite() as $s) {
            if (! $s->visits && ! $s->last_seen) {
                $out->push(['fix', "{$s->name}: no data yet", "Add sharet-analytics.js with data-site=\"{$s->key}\" to {$s->url} (needs SHARET_ANALYTICS_URL on that site) so its visits and conversions count."]);
            }
        }

        if ($t['enough']) {
            foreach ($this->topCombos(3) as $c) {
                $out->push(['push', Sites::name($c->site)." · {$c->path} from {$c->source}",
                    "{$c->conversions} conversions".($c->rate !== null ? " ({$c->rate}% of {$c->visits} visits)" : '').'. Send more of this traffic to this page: '
                    .($c->channel === 'organic' ? 'bid on the same search terms with Google Search ads.' : ($c->channel === 'social' || $c->channel === 'paid' ? 'raise the budget on this audience and creative.' : 'ask this partner for more placements.'))]);
            }
            if ($best = $this->bestChannels()->first()) {
                $out->push(['push', 'Best channel: '.(Analytics::CHANNELS[$best->channel] ?? $best->channel), "{$best->rate}% of {$best->visits} visits converted. Weight new budget toward it (see the split below)."]);
            }
        }

        foreach ($this->report->leakyPages(30, 3) as $p) {
            $out->push(['fix', Sites::name($p->site)." · {$p->path}: traffic, no conversions", number_format($p->views).' views and not one conversion started or happened there. Add a clear call to action (data-sa-click to measure it), or stop sending paid traffic to it.']);
        }

        $ovp = $this->organicVsPaid();
        if ($ovp['visits'] >= $this->minVisits) {
            $out->push(['info', "Organic {$ovp['organic']}% · paid {$ovp['paid']}%",
                $ovp['organic'] < 20 ? 'Little search traffic yet: finish the SEO to-dos (Search Console, sitemaps) before buying search ads at scale.'
                    : 'Search already brings people: keep the SEO work going and use paid ads to test new audiences.']);
        }

        return $out;
    }

    public static function adChannel(?string $source): ?string
    {
        $source = strtolower((string) $source);
        foreach (self::AD_CHANNELS as $key => [, $pattern]) {
            if ($source !== '' && preg_match($pattern, $source)) {
                return $key;
            }
        }

        return null;
    }
}
