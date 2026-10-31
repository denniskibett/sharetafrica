<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Website analytics from our own tables (page_views, page_clicks). "Visitors" are counted per day,
 * because the anonymous visitor id changes daily by design.
 */
class AnalyticsController extends Controller
{
    protected const RANGES = [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 365 => 'Last 12 months'];

    public function index(Request $request)
    {
        $days = $this->days($request);
        $from = now()->subDays($days - 1)->startOfDay();
        $views = DB::table('page_views')->where('created_at', '>=', $from);

        $totals = (clone $views)->selectRaw('count(*) as views, avg(case when duration_ms between 1000 and 1800000 then duration_ms end) as avg_ms, avg(scroll_pct) as avg_scroll')->first();
        $visitorDays = DB::query()->fromSub((clone $views)->selectRaw('visitor, date(created_at) as d, count(*) as n')->groupBy('visitor', 'd'), 'vd')
            ->selectRaw('count(*) as visitors, sum(case when n = 1 then 1 else 0 end) as bounces')->first();

        $daily = (clone $views)->selectRaw('date(created_at) as d, count(*) as views, count(distinct visitor) as visitors')->groupBy('d')->orderBy('d')->get()->keyBy('d');
        $series = collect(range($days - 1, 0))->map(function ($ago) use ($daily) {
            $d = now()->subDays($ago)->toDateString();

            return ['date' => $d, 'views' => (int) ($daily[$d]->views ?? 0), 'visitors' => (int) ($daily[$d]->visitors ?? 0)];
        });

        $top = fn ($column, $limit = 8) => (clone $views)->whereNotNull($column)->selectRaw("$column as label, count(*) as views")
            ->groupBy($column)->orderByDesc('views')->limit($limit)->get();

        return view('admin.analytics.index', [
            'days' => $days,
            'ranges' => self::RANGES,
            'kpi' => [
                'views' => (int) $totals->views,
                'visitors' => (int) $visitorDays->visitors,
                'per_visitor' => $visitorDays->visitors ? round($totals->views / $visitorDays->visitors, 1) : 0,
                'avg_time' => $totals->avg_ms ? $this->duration((int) $totals->avg_ms) : '—',
                'bounce' => $visitorDays->visitors ? round($visitorDays->bounces / $visitorDays->visitors * 100) : 0,
                'scroll' => $totals->avg_scroll ? round($totals->avg_scroll) : 0,
            ],
            'series' => $series,
            'pages' => (clone $views)->selectRaw('path, count(*) as views, count(distinct visitor) as visitors,
                    avg(case when duration_ms between 1000 and 1800000 then duration_ms end) as avg_ms, avg(scroll_pct) as scroll')
                ->groupBy('path')->orderByDesc('views')->limit(15)->get(),
            'referrers' => $top('referrer_host'),
            'campaigns' => $top('utm_campaign'),
            'sources' => $top('utm_source'),
            'devices' => $top('device', 3),
            'browsers' => $top('browser', 6),
            'countries' => $top('country', 8),
            'clicks' => DB::table('page_clicks')->where('created_at', '>=', $from)->whereNotNull('target')->where('target', '!=', '')
                ->selectRaw('target, path, count(*) as clicks')->groupBy('target', 'path')->orderByDesc('clicks')->limit(10)->get(),
            'duration' => fn ($ms) => $ms ? $this->duration((int) $ms) : '—',
        ]);
    }

    public function heatmap(Request $request)
    {
        $days = $this->days($request);
        $paths = DB::table('page_clicks')->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('path, count(*) as clicks')->groupBy('path')->orderByDesc('clicks')->limit(50)->get();

        return view('admin.analytics.heatmap', [
            'days' => $days,
            'ranges' => self::RANGES,
            'paths' => $paths,
            'path' => $request->query('path', $paths->first()->path ?? '/'),
            'device' => in_array($request->query('device'), ['desktop', 'tablet', 'mobile']) ? $request->query('device') : 'desktop',
        ]);
    }

    /** Click points for one page/device, as fractions of page width/height (JSON for the heatmap canvas). */
    public function clicks(Request $request)
    {
        $rows = DB::table('page_clicks')
            ->where('path', (string) $request->query('path', '/'))
            ->where('device', (string) $request->query('device', 'desktop'))
            ->where('created_at', '>=', now()->subDays($this->days($request)))
            ->latest('id')->limit(5000)->get(['x_permille', 'y', 'page_h']);

        return response()->json([
            'count' => $rows->count(),
            'points' => $rows->map(fn ($r) => [round($r->x_permille / 1000, 4), round($r->y / max(1, $r->page_h), 4)]),
        ]);
    }

    protected function days(Request $request): int
    {
        $days = (int) $request->query('days', 30);

        return array_key_exists($days, self::RANGES) ? $days : 30;
    }

    protected function duration(int $ms): string
    {
        $s = (int) round($ms / 1000);

        return $s >= 60 ? intdiv($s, 60).'m '.str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT).'s' : $s.'s';
    }
}
