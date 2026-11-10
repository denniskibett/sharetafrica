<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Growth\AdCopy;
use App\Support\Growth\AdPlanner;
use App\Support\Growth\Analytics;
use App\Support\Growth\GrowthReport;
use App\Support\Growth\SeoTodos;
use App\Support\Growth\Sites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Administration › Growth: the seven Sharet-family sites in one place. Tabs: Overview, Conversions, SEO, Ad center;
 * every tab takes ?days= and ?site= (all or one key). Data: growth_* tables (database/schema/2026_11_10_growth_hub.sql),
 * filled by POST /t/x (GrowthCollectController) and php artisan growth:seo-audit.
 */
class GrowthController extends Controller
{
    public const RANGES = [7 => '7 days', 30 => '30 days', 90 => '90 days', 365 => '12 months'];

    public const TABS = ['index' => 'Overview', 'conversions' => 'Conversions', 'seo' => 'SEO', 'ads' => 'Ad center'];

    public function overview(Request $request)
    {
        return $this->page($request, 'overview', fn (GrowthReport $r) => [
            'perSite' => $r->perSite(),
            'series' => $r->series(),
            'channels' => $r->channels(),
        ]);
    }

    public function conversions(Request $request)
    {
        return $this->page($request, 'conversions', fn (GrowthReport $r) => [
            'bySiteName' => $r->bySiteAndName(),
            'bySource' => $r->bySource(),
            'byLanding' => $r->byLanding(),
            'funnel' => $r->funnel(),
            'labels' => (array) config('growth.conversions'),
        ]);
    }

    public function seo(Request $request)
    {
        return $this->page($request, 'seo', function (GrowthReport $r) {
            $keys = $r->site ? [$r->site] : Sites::keys();
            $checks = DB::table('growth_seo_checks')->whereIn('site', $keys)->orderBy('id')->get()
                ->map(function ($c) {
                    $c->findings = collect(json_decode((string) $c->findings, true) ?: []);

                    return $c;
                })->groupBy('site');

            return [
                'keys' => $keys,
                'checks' => $checks,
                'perSite' => $r->perSite(),
                'todos' => SeoTodos::forSites($keys),
                'checkedAt' => DB::table('growth_seo_checks')->whereIn('site', $keys)->max('checked_at'),
            ];
        });
    }

    public function ads(Request $request)
    {
        $budget = max(0, min(100_000_000, (int) $request->query('budget', 0)));

        return $this->page($request, 'ads', function (GrowthReport $r) use ($budget) {
            $plan = new AdPlanner($r);

            return [
                'plan' => $plan,
                'totals' => $plan->totals(),
                'recommendations' => $plan->recommendations(),
                'combos' => $plan->topCombos(),
                'channels' => $r->channels(),
                'ovp' => $plan->organicVsPaid(),
                'siteSplit' => $plan->siteSplit($budget),
                'channelSplit' => $plan->channelSplit($budget),
                'leaky' => $r->leakyPages(),
                'budget' => $budget,
                'copy' => collect($r->site ? [$r->site] : Sites::keys())->mapWithKeys(fn ($k) => [$k => AdCopy::for($k)]),
                'rules' => AdPlanner::RULES,
            ];
        });
    }

    /** Tick / untick an owner to-do (and keep a short note). */
    public function todo(Request $request)
    {
        $data = $request->validate([
            'site' => ['required', Rule::in(Sites::keys())],
            'item' => ['required', Rule::in(array_keys(SeoTodos::ITEMS))],
            'done' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        abort_unless(Analytics::ready(), 409, 'Apply database/schema/2026_11_10_growth_hub.sql first.');

        DB::table('growth_todos')->updateOrInsert(['site' => $data['site'], 'item' => $data['item']], [
            'done' => $request->boolean('done') ? 1 : 0,
            'note' => $data['note'] ?? null,
            'updated_by' => $request->user()->id,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.growth.seo', array_filter(['site' => $request->input('back_site'), 'days' => $request->input('back_days')]))
            ->with('status', Sites::name($data['site']).': '.SeoTodos::ITEMS[$data['item']][0].' saved.');
    }

    private function page(Request $request, string $tab, \Closure $data)
    {
        $days = (int) $request->query('days', 30);
        $days = array_key_exists($days, self::RANGES) ? $days : 30;
        $site = in_array($request->query('site'), Sites::keys(), true) ? $request->query('site') : null;

        $common = ['tab' => $tab, 'days' => $days, 'site' => $site, 'ranges' => self::RANGES, 'tabs' => self::TABS, 'sites' => Sites::all(), 'ready' => Analytics::ready()];
        if (! $common['ready']) {
            return view('admin.growth.setup', $common);
        }

        return view('admin.growth.'.$tab, $common + $data(new GrowthReport($days, $site)));
    }
}
