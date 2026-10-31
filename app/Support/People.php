<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Shared vocabulary for people: lanes (users.intent), onboarding status pills, and sign-up counts
 * used by the dashboard, User management and Reports.
 */
class People
{
    public const LANES = [
        'individual' => 'Personal',
        'merchant' => 'Merchant',
        'business' => 'Business',
        'techie' => 'Developer',
    ];

    public const STATUS = [
        'waiting_list' => ['Waiting list', 'warning'],
        'invited' => ['Invited', 'info'],
        'in_progress' => ['Onboarding', 'info'],
        'active' => ['Active', 'success'],
    ];

    public static function status(?string $status): array
    {
        [$label, $tone] = self::STATUS[$status] ?? [ucfirst(str_replace('_', ' ', (string) $status)) ?: 'Unknown', 'neutral'];

        return Table::pill($label, $tone);
    }

    public static function lane(?string $intent): string
    {
        return self::LANES[$intent] ?? 'Not chosen';
    }

    /** Sign-ups this month vs last month (for stat deltas), optionally for one lane. */
    public static function growth(?string $lane = null): array
    {
        $q = fn () => User::query()->when($lane, fn ($q) => $q->where('intent', $lane));
        $now = $q()->where('created_at', '>=', now()->startOfMonth())->count();
        $before = $q()->whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->count();

        return [$now, Table::delta($now, $before)];
    }

    /** Daily sign-ups for the last $days days: [labels, counts]. */
    public static function signupSeries(int $days = 30, ?string $lane = null): array
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $rows = User::query()->when($lane, fn ($q) => $q->where('intent', $lane))
            ->where('created_at', '>=', $from)
            ->selectRaw('date(created_at) as d, count(*) as n')->groupBy('d')->pluck('n', 'd');

        $labels = $counts = [];
        foreach (range($days - 1, 0) as $ago) {
            $d = now()->subDays($ago);
            $labels[] = $d->format('j M');
            $counts[] = (int) ($rows[$d->toDateString()] ?? 0);
        }

        return [$labels, $counts];
    }
}
