<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiProblem;
use App\Support\Growth\Analytics;
use App\Support\Growth\GrowthReport;
use App\Support\Growth\Sites;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/v1/growth/summary?from=YYYY-MM-DD&to=YYYY-MM-DD[&site=key] — the localized Growth tab in each app.
 * Authenticated with the business's own Sharet API key (scope read). A key only ever sees the site(s) whose
 * config('growth.sites.*') sharet_id / company_id is its business; nothing about other sites or other businesses.
 * Shape: docs/v2/GROWTH.md ("growth.summary.v1").
 */
class GrowthController extends Controller
{
    public const MAX_DAYS = 366;

    public function summary(Request $request)
    {
        $key = $request->attributes->get('api_key');
        $mine = Sites::forCompany($key->company);
        if ($mine->isEmpty()) {
            return ApiProblem::make(403, 'growth_no_site', 'No website linked', 'This key\'s business has no website in the Sharet Growth hub. Ask Sharet to link it.');
        }

        $v = validator($request->query(), [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'site' => ['nullable', 'string'],
        ]);
        if ($v->fails()) {
            return ApiProblem::make(422, 'invalid_date_range', 'Invalid parameters', $v->errors()->first(), ['errors' => $v->errors()]);
        }
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : now();
        $from = $request->query('from') ? Carbon::parse($request->query('from')) : $to->copy()->subDays(29);
        if ($from->gt($to) || $from->diffInDays($to) >= self::MAX_DAYS) {
            return ApiProblem::make(422, 'invalid_date_range', 'Invalid date range', 'from must be on or before to, and the range at most '.self::MAX_DAYS.' days.');
        }
        if (($want = $request->query('site')) !== null && ! $mine->contains($want)) {
            return ApiProblem::make(403, 'growth_other_site', 'Not your website', 'This key can read: '.$mine->implode(', ').'.');
        }
        if (! Analytics::ready()) {
            return ApiProblem::make(503, 'growth_unavailable', 'Growth data unavailable', 'The Growth hub is not set up on Sharet yet. Try again later.');
        }

        $sites = $want ? collect([$want]) : $mine;

        return response()->json([
            'object' => 'growth.summary',
            'version' => 1,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'sites' => $sites->map(fn ($site) => $this->site($site, $from, $to))->values(),
        ])->header('Cache-Control', 'private, max-age=300');
    }

    private function site(string $site, Carbon $from, Carbon $to): array
    {
        $r = new GrowthReport(30, $site, $from, $to);
        $s = $r->perSite()->first();
        $channels = $r->channels();
        $total = max(1, $channels->sum('visits'));
        $labels = (array) config('growth.conversions');
        $seo = DB::table('growth_seo_checks')->where('site', $site)->get();
        $findings = $seo->flatMap(fn ($c) => collect(json_decode((string) $c->findings, true) ?: [])
            ->map(fn ($f) => $f + ['url' => $c->url]));

        return [
            'key' => $site,
            'name' => $s->name,
            'url' => $s->url,
            'last_data_at' => $s->last_seen?->toIso8601String(),
            'totals' => [
                'visits' => $s->visits,
                'page_views' => $s->views,
                'conversions' => $s->conversions,
                'conversion_rate' => $s->rate,
                'organic_share' => $s->organic,
                'paid_share' => $s->paid,
            ],
            'daily' => $r->series()->map(fn ($d) => ['date' => $d['date'], 'page_views' => $d['views'], 'visits' => $d['visits'], 'conversions' => $d['conversions']])->values(),
            'conversions' => $r->bySiteAndName()->map(fn ($c) => ['name' => $c->name, 'label' => $labels[$c->name] ?? $c->name, 'count' => $c->n])->values(),
            'channels' => $channels->map(fn ($c) => ['channel' => $c->channel, 'label' => Analytics::CHANNELS[$c->channel] ?? $c->channel,
                'visits' => $c->visits, 'share' => round($c->visits / $total * 100, 1), 'conversions' => $c->conversions, 'conversion_rate' => $c->rate])->values(),
            'sources' => $r->bySource(10)->map(fn ($c) => ['source' => $c->source, 'medium' => $c->medium, 'campaign' => $c->campaign,
                'visits' => $c->visits, 'conversions' => $c->conversions, 'conversion_rate' => $c->rate])->values(),
            'landing_pages' => $r->byLanding(10)->map(fn ($c) => ['path' => $c->path, 'visits' => $c->visits, 'conversions' => $c->conversions, 'conversion_rate' => $c->rate])->values(),
            'seo' => [
                'checked_at' => $seo->max('checked_at') ? Carbon::parse($seo->max('checked_at'))->toIso8601String() : null,
                'sitemap_urls' => $seo->firstWhere('kind', 'sitemap')?->sitemap_urls,
                'counts' => ['red' => $findings->where('level', 'red')->count(), 'amber' => $findings->where('level', 'amber')->count(), 'green' => $findings->where('level', 'green')->count()],
                'fix_first' => $findings->whereIn('level', ['red', 'amber'])->sortBy(fn ($f) => $f['level'] === 'red' ? 0 : 1)->take(10)
                    ->map(fn ($f) => ['level' => $f['level'], 'url' => $f['url'], 'text' => $f['text']])->values(),
            ],
        ];
    }
}
