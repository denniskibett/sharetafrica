<?php

namespace App\Support;

use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The onboarding funnel: the first time a person reaches each step, kept once per person (funnel_events).
 * Recorded from the models (User, RampOrder) and the app sign-in, so the website and the app both count.
 * Staff accounts are left out when the funnel is read (Admin › Website analytics), not when it is written.
 */
class Funnel
{
    /** In order. Label, and what reaching it means. */
    public const STEPS = [
        'signed_up' => ['Signed up', 'Created an account'],
        'email_verified' => ['Email confirmed', 'Entered the emailed code'],
        'pin_set' => ['PIN set', 'Chose a Sharet PIN'],
        'id_submitted' => ['ID sent', 'Sent their ID and selfie'],
        'verified' => ['Verified', 'Approved to transact'],
        'app_signed_in' => ['Used the app', 'Signed in on the Android app'],
        'first_money_in' => ['Added money', 'First top-up landed in the wallet'],
        'first_money_out' => ['First payment', 'First send, bill or withdrawal went through'],
    ];

    public static function hit(User|int|null $user, string $step, ?string $channel = null): void
    {
        $id = $user instanceof User ? $user->getKey() : $user;
        if (! $id || ! isset(self::STEPS[$step])) {
            return;
        }
        try {
            DB::table('funnel_events')->insertOrIgnore([
                'user_id' => $id, 'step' => $step, 'channel' => $channel ?? self::channel(), 'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e); // tracking never breaks a sign-up or a payment
        }
    }

    /** web or app from the current request; null outside one (queue, scheduler, payment callbacks). */
    public static function channel(): ?string
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }
        $request = request();
        if (! $request || ! $request->route()) {
            return null;
        }
        if ($request->is('api/mobile/*') || $request->is('api/*/mobile/*') || $request->is('api/mobile')) {
            return 'app';
        }

        return $request->is('api/*') ? null : 'web';
    }

    /** From User::saved: the steps a change to the account shows. */
    public static function fromUser(User $user): void
    {
        if ($user->wasRecentlyCreated) {
            self::hit($user, 'signed_up');
        }
        if ($user->email_verified_at && ($user->wasRecentlyCreated || $user->wasChanged('email_verified_at'))) {
            self::hit($user, 'email_verified');
        }
        if ($user->transaction_pin && $user->wasChanged('transaction_pin')) {
            self::hit($user, 'pin_set');
        }
        if ($user->kyc && $user->wasChanged('kyc')) {
            self::hit($user, 'id_submitted');
        }
        if (in_array($user->kyc_level, ['basic', 'standard'], true) && $user->wasChanged('kyc_level')) {
            self::hit($user, 'verified');
        }
    }

    /** From RampOrder::saved: a payment that finished. Top-ups (into the wallet) are money in; everything else out. */
    public static function fromOrder(RampOrder $order): void
    {
        if ($order->status !== 'paid' || ! $order->user_id) {
            return;
        }
        self::hit($order->user_id, $order->destination_type === 'wallet' ? 'first_money_in' : 'first_money_out');
    }

    /** The "How was that?" card: after the first payment out, until they answer or dismiss it. */
    public static function askFeedback(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return DB::table('funnel_events')->where('user_id', $user->id)->where('step', 'first_money_out')->exists()
            && ! DB::table('feedback')->where('user_id', $user->id)->where('topic', 'first_payment')->exists();
    }

    public static function feedback(User $user, ?int $score, ?string $comment, ?string $channel = null): void
    {
        DB::table('feedback')->updateOrInsert(
            ['user_id' => $user->id, 'topic' => 'first_payment'],
            ['score' => $score, 'comment' => $comment !== null && trim($comment) !== '' ? mb_substr(trim($comment), 0, 1000) : null,
                'channel' => $channel ?? self::channel(), 'created_at' => now(), 'updated_at' => now()],
        );
    }

    /**
     * The funnel for people who signed up in the last $days days (staff left out): how many reached each step, as a
     * share of sign-ups and of the step before, and the median time from sign-up (live events only, not backfilled).
     */
    public static function report(int $days): array
    {
        $staff = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', DashboardNav::OPS)->where('model_has_roles.model_type', User::class)->pluck('model_id');
        $cohort = DB::table('funnel_events')->where('step', 'signed_up')->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->whereNotIn('user_id', $staff)->pluck('created_at', 'user_id');
        $ids = $cohort->keys();
        $events = $ids->isEmpty() ? collect() : DB::table('funnel_events')->whereIn('user_id', $ids)->get(['user_id', 'step', 'channel', 'backfilled', 'created_at'])->groupBy('step');

        $total = $cohort->count();
        $prev = $total;
        $rows = [];
        foreach (self::STEPS as $key => [$label, $hint]) {
            $hits = $events[$key] ?? collect();
            $n = $key === 'signed_up' ? $total : $hits->count();
            $hours = $hits->where('backfilled', false)->map(fn ($e) => (strtotime($e->created_at) - strtotime($cohort[$e->user_id])) / 3600)
                ->filter(fn ($h) => $h >= 0)->sort()->values();
            $rows[] = [
                'step' => $key, 'label' => $label, 'hint' => $hint, 'people' => $n,
                'of_signups' => $total ? (int) round($n / $total * 100) : 0,
                'of_previous' => $prev ? (int) round($n / $prev * 100) : 0,
                'median' => $key === 'signed_up' || $hours->isEmpty() ? null : self::span($hours[intdiv($hours->count(), 2)]),
                'app' => $hits->where('channel', 'app')->count(),
            ];
            if ($key !== 'app_signed_in') {
                $prev = $n; // the app is a side step: the next step compares with the one before it
            }
        }
        $drop = collect($rows)->slice(1)->reject(fn ($r) => $r['step'] === 'app_signed_in')->sortBy('of_previous')->first();

        return ['signups' => $total, 'steps' => $rows, 'biggest_drop' => $total ? $drop : null];
    }

    /** First-payment ratings for people who signed up or answered in the last $days days. */
    public static function ratings(int $days): array
    {
        $q = DB::table('feedback')->where('topic', 'first_payment')->where('updated_at', '>=', now()->subDays($days - 1)->startOfDay());
        $scored = (clone $q)->whereNotNull('score');

        return [
            'answers' => (clone $q)->count(),
            'scored' => (clone $scored)->count(),
            'average' => ($a = (clone $scored)->avg('score')) ? round($a, 1) : null,
            'spread' => collect(range(1, 5))->mapWithKeys(fn ($n) => [$n => (clone $scored)->where('score', $n)->count()])->all(),
            'notes' => (clone $q)->whereNotNull('comment')->orderByDesc('updated_at')->limit(8)->get(['score', 'comment', 'channel', 'updated_at']),
        ];
    }

    protected static function span(float $hours): string
    {
        return match (true) {
            $hours < 1 => max(1, (int) round($hours * 60)).' min',
            $hours < 48 => round($hours, $hours < 10 ? 1 : 0).' h',
            default => round($hours / 24).' days',
        };
    }
}
