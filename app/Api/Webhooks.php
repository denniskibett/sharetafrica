<?php

namespace App\Api;

use App\Models\ApiPayment;
use App\Models\ApiWebhook;
use App\Models\ApiWebhookDelivery;
use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Webhooks for the public API (docs/v2/INTEGRATION.md §8).
 * Every delivery is signed: "Sharet-Signature: t=<unix>,v1=<hex HMAC-SHA256 of "t.body" with the endpoint's secret>".
 * Deliveries are recorded first and sent after the database commits (never inside a money transaction); failures are
 * retried with backoff for 24 hours by "php artisan api:webhooks-retry" (scheduled every minute).
 */
final class Webhooks
{
    public const RETRY_FOR_HOURS = 24;

    /** Record one delivery per matching endpoint and send them once the current transaction commits. */
    public function fire(User $user, string $environment, string $event, array $data): void
    {
        $hooks = ApiWebhook::where('user_id', $user->id)->where('environment', $environment)->where('enabled', true)->get()
            ->filter(fn (ApiWebhook $h) => in_array($event, $h->events ?? [], true));

        foreach ($hooks as $hook) {
            $id = 'evt_'.Str::lower(Str::random(20));
            $delivery = $hook->deliveries()->create([
                'event' => $event, 'event_id' => $id,
                'payload' => ['id' => $id, 'type' => $event, 'created' => now()->toIso8601String(), 'data' => $data],
                'status' => 'pending', 'next_attempt_at' => now(),
            ]);
            DB::afterCommit(fn () => $this->deliver($delivery));
        }
    }

    /** A ramp order changed: if an API payment rides on it, update its status and tell the business. */
    public function orderMoved(RampOrder $order): void
    {
        $p = ApiPayment::where('ramp_order_id', $order->id)->first();
        if (! $p) {
            return;
        }
        $status = Payments::statusOf($order);
        if ($status === $p->status) {
            return;
        }
        $p->update(['status' => $status, 'failure' => $status === 'failed' ? ($order->failure ?: 'The payment could not be completed.') : $p->failure]);
        if (in_array($status, ['paid', 'failed', 'refunded'], true)) {
            $this->fire($p->user, $p->environment, 'payment.'.$status, Payments::present($p->fresh()));
        }
    }

    public function deliver(ApiWebhookDelivery $d): void
    {
        $d->refresh();
        if ($d->status !== 'pending') {
            return;
        }
        $body = json_encode($d->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $t = time();
        $status = null;
        try {
            $res = Http::timeout(5)->withHeaders([
                'Content-Type' => 'application/json',
                'Sharet-Signature' => "t={$t},v1=".self::sign($d->webhook->secret, $t, $body),
                'Sharet-Event' => $d->event,
            ])->withBody($body, 'application/json')->post($d->webhook->url);
            $status = $res->status();
        } catch (Throwable) {
            $status = 0;
        }

        $attempts = $d->attempts + 1;
        if ($status >= 200 && $status < 300) {
            $d->update(['attempts' => $attempts, 'status' => 'delivered', 'last_status' => $status, 'delivered_at' => now(), 'next_attempt_at' => null]);
        } elseif ($d->created_at->lt(now()->subHours(self::RETRY_FOR_HOURS))) {
            $d->update(['attempts' => $attempts, 'status' => 'failed', 'last_status' => $status, 'next_attempt_at' => null]);
        } else {
            $d->update(['attempts' => $attempts, 'last_status' => $status, 'next_attempt_at' => now()->addMinutes(min(60, 2 ** min($attempts, 6)))]);
        }
    }

    /** Send every pending delivery that is due. Returns how many were tried. */
    public function retryDue(): int
    {
        $n = 0;
        ApiWebhookDelivery::where('status', 'pending')->where('next_attempt_at', '<=', now())->orderBy('id')->limit(200)->get()
            ->each(function (ApiWebhookDelivery $d) use (&$n) {
                $this->deliver($d);
                $n++;
            });

        return $n;
    }

    public static function sign(string $secret, int $t, string $body): string
    {
        return hash_hmac('sha256', $t.'.'.$body, $secret);
    }

    /** Endpoints must be public HTTPS URLs (no localhost or private networks). */
    public static function urlAllowed(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? '') !== 'https' || $host === '' || $host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return true;
    }
}
