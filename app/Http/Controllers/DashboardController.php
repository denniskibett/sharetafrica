<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Company;
use App\Models\User;
use App\Support\People;
use App\Support\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * /dashboard. The page is a shell; its stat cards, charts and table load as JSON:
 *   GET /dashboard/data          stats for the signed-in person's lane
 *   GET /dashboard/chart/{name}  ApexCharts options (signups, lanes, funnel, money)
 *   GET /dashboard/activity      recent wallet transactions (table JSON)
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isOnWaitingList() || $user->needsOnboarding()) {
            return redirect()->route('onboarding.welcome');
        }

        $lane = $this->lane($user);

        // Customers get the Sharet app home (canvas "Mobile home" / "Web dashboard"); staff the admin overview
        if (in_array($lane, ['admin', 'ops'], true)) {
            return view('dashboard', ['lane' => $lane, 'user' => $user]);
        }

        return view('dashboard.customer', ['user' => $user, 'handle' => \App\Support\Handle::for($user), 'banks' => config('pricing.banks'),
            'wallet' => \App\Ledger\Money::format(app(\App\Ramp\RampOrders::class)->walletBalance($user))]);
    }

    public function data(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['stats' => match ($this->lane($user)) {
            'admin', 'ops' => $this->peopleStats(),
            'techie' => $this->developerStats($user),
            default => $this->walletStats($user),
        }]);
    }

    public function chart(Request $request, string $name): JsonResponse
    {
        $user = $request->user();
        $staff = in_array($this->lane($user), ['admin', 'ops'], true);
        $days = in_array((int) $request->query('days'), [7, 30, 90], true) ? (int) $request->query('days') : 30;

        return response()->json(match (true) {
            $name === 'signups' && $staff => $this->signupsChart($days, $request->query('lane') ?: null),
            $name === 'lanes' && $staff => $this->lanesChart(),
            $name === 'funnel' && $staff => $this->funnelChart(),
            $name === 'money' => $this->moneyChart($user),
            default => abort(404),
        });
    }

    public function activity(Request $request): JsonResponse
    {
        $wallet = $request->user()->getOrCreateWallet();

        return Table::respond($request, $wallet->transactions()->getQuery(), fn ($tx) => [
            'id' => $tx->id,
            'type' => Table::pill($tx->type === 'deposit' ? 'Money in' : 'Money out', $tx->type === 'deposit' ? 'success' : 'info'),
            'amount' => abs($tx->amount) / 100,
            'status' => Table::pill($tx->confirmed ? 'Completed' : 'Pending', $tx->confirmed ? 'success' : 'warning'),
            'date' => $tx->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i'),
        ], [], ['date' => 'created_at', 'amount' => 'amount']);
    }

    /** Menu items for the header search (same as the sidebar). */
    public function menu(Request $request): JsonResponse
    {
        return response()->json(collect(\App\Support\DashboardNav::for($request->user()))->flatMap(fn ($g) => collect($g['items'])->map(fn ($i) => [
            'label' => $i['label'], 'group' => $g['title'], 'url' => $i['url'], 'ready' => $i['ready'],
        ]))->unique('label')->values());
    }

    /** admin | ops | individual | merchant | business | techie */
    protected function lane(User $user): string
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return 'admin';
        }
        if ($user->hasAnyRole(['support', 'operations'])) {
            return 'ops';
        }

        return in_array($user->intent, ['merchant', 'business', 'techie'], true) ? $user->intent : 'individual';
    }

    protected function peopleStats(): array
    {
        [$newThisMonth, $delta] = People::growth();
        $status = User::selectRaw('onboarding_status, count(*) as n')->groupBy('onboarding_status')->pluck('n', 'onboarding_status');

        return [
            'people' => User::count(),
            'people_delta' => $delta,
            'people_hint' => "$newThisMonth new this month",
            'waiting' => (int) ($status['waiting_list'] ?? 0),
            'waiting_hint' => ((int) ($status['invited'] ?? 0)).' invited',
            'active' => (int) ($status['active'] ?? 0),
            'active_hint' => User::where('onboarding_status', 'active')->where('onboarded_at', '>=', now()->startOfMonth())->count().' this month',
            'companies' => Company::count(),
            'companies_hint' => Company::whereNotNull('verified_at')->count().' verified',
        ];
    }

    protected function walletStats(User $user): array
    {
        $tx = fn () => $user->getOrCreateWallet()->transactions();
        $month = fn ($q) => $q->where('created_at', '>=', now()->startOfMonth());

        return [
            'balance' => $user->getWalletBalance(),
            'balance_hint' => 'Available now',
            'in' => $month($tx()->where('type', 'deposit'))->sum('amount') / 100,
            'in_hint' => 'This month',
            'out' => abs($month($tx()->where('type', 'withdraw'))->sum('amount')) / 100,
            'out_hint' => 'This month',
            'count' => $tx()->count(),
            'count_hint' => $month($tx())->count().' this month',
        ];
    }

    protected function developerStats(User $user): array
    {
        $keys = ApiKey::where('user_id', $user->id);

        return [
            'keys' => (clone $keys)->count(),
            'keys_hint' => (clone $keys)->where('enabled', true)->count().' enabled',
            'sandbox' => (clone $keys)->where('environment', 'sandbox')->count(),
            'sandbox_hint' => 'Free test keys',
            'live' => (clone $keys)->where('environment', 'live')->count(),
            'live_hint' => 'Need approval',
            'last_used' => optional((clone $keys)->max('last_used_at'), fn ($d) => Carbon::parse($d)->diffForHumans()) ?? 'Never',
            'last_used_hint' => 'Last API call',
        ];
    }

    protected function signupsChart(int $days, ?string $only = null): array
    {
        $lanes = $only && isset(People::LANES[$only]) ? [$only => People::LANES[$only]] : People::LANES;
        $series = collect($lanes)->map(fn ($label, $lane) => ['name' => $label, 'data' => People::signupSeries($days, $lane)[1]])->values();
        [$labels] = People::signupSeries($days);

        return ['type' => 'area', 'series' => $series, 'xaxis' => ['categories' => $labels, 'tickAmount' => min($days, 10)]];
    }

    protected function lanesChart(): array
    {
        $counts = User::selectRaw('intent, count(*) as n')->groupBy('intent')->pluck('n', 'intent');

        return [
            'type' => 'donut',
            'series' => collect(People::LANES)->keys()->map(fn ($k) => (int) ($counts[$k] ?? 0))->push((int) ($counts[''] ?? 0) + (int) ($counts[null] ?? 0))->values(),
            'labels' => collect(People::LANES)->values()->push('Not chosen'),
            'legend' => ['position' => 'bottom'],
        ];
    }

    protected function funnelChart(): array
    {
        $status = User::selectRaw('onboarding_status, count(*) as n')->groupBy('onboarding_status')->pluck('n', 'onboarding_status');

        return [
            'type' => 'bar',
            'series' => [['name' => 'People', 'data' => collect(People::STATUS)->keys()->map(fn ($k) => (int) ($status[$k] ?? 0))->values()]],
            'xaxis' => ['categories' => collect(People::STATUS)->pluck(0)->values()],
            'plotOptions' => ['bar' => ['horizontal' => true, 'borderRadius' => 4, 'barHeight' => '55%', 'distributed' => true]],
            'legend' => ['show' => false],
        ];
    }

    /** Money in vs out per month, last 6 months. */
    protected function moneyChart(User $user): array
    {
        $wallet = $user->getOrCreateWallet();
        $rows = DB::table('transactions')->where('wallet_id', $wallet->id)->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at', 'type', 'amount'])->map(fn ($r) => (object) ['m' => substr($r->created_at, 0, 7), 'type' => $r->type, 'total' => abs($r->amount)]);
        $months = collect(range(5, 0))->map(fn ($ago) => now()->subMonthsNoOverflow($ago));
        $sum = fn ($type) => $months->map(fn ($m) => round((float) $rows->where('m', $m->format('Y-m'))->where('type', $type)->sum('total') / 100, 2))->values();

        return [
            'type' => 'bar',
            'series' => [['name' => 'Money in', 'data' => $sum('deposit')], ['name' => 'Money out', 'data' => $sum('withdraw')]],
            'colors' => ['#D4FF3D', '#80807A'],
            'xaxis' => ['categories' => $months->map->format('M')->values()],
        ];
    }
}
