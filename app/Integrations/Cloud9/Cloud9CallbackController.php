<?php

namespace App\Integrations\Cloud9;

use App\Integrations\RailResult;
use App\Models\RailRequest;
use App\Ramp\RampOrders;
use App\Services\Integrations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Where Cloud9 posts PAYIN and PAYOUT webhooks (no session, no CSRF). Believed only when X-Cloud9-Callback-Secret
 * matches the event's secret in the Cloud9 integration; then the payment is read again from Cloud9 by its id before
 * anything is booked. RampOrders::apply() is idempotent, so Cloud9's retries and resends post once.
 */
class Cloud9CallbackController
{
    public function __invoke(Request $request, RampOrders $orders, Cloud9Rail $rail): JsonResponse
    {
        $event = strtoupper((string) $request->input('event_type'));
        $c = (array) Integrations::credentials('cloud9');
        $secret = match ($event) {
            'PAYIN' => $c['payin_secret'] ?? null,
            'PAYOUT' => $c['payout_secret'] ?? null,
            default => null,
        };
        if (blank($secret) || ! hash_equals((string) $secret, (string) $request->header('X-Cloud9-Callback-Secret'))) {
            return response()->json(['error' => 'Unknown callback.'], 401);
        }

        $kind = $event === 'PAYIN' ? 'collect' : 'payout';
        $req = RailRequest::where('rail', 'cloud9')->where('kind', $kind)->where('provider_reference', (string) $request->input('id'))->first();
        if (! $req) {
            // A paybill deposit straight to Sharet's Cloud9 account number, or a payment we don't know: reconciliation
            // finds it on the statement and staff place it (credit a customer or park in suspense)
            report(new \RuntimeException('Cloud9 '.$event.' for an unknown id '.$request->input('id')));

            return response()->json(['ok' => true]);
        }

        $meta = $req->meta ?? [];
        $meta['callbacks'] = array_merge($meta['callbacks'] ?? [], [['at' => now()->toIso8601String(), 'body' => $request->except([])]]);
        $req->forceFill(['meta' => $meta])->save();

        // Read it again from Cloud9 by id; the webhook body only stands in if Cloud9 can't be reached
        try {
            $result = $rail->status($req);
        } catch (\Throwable $e) {
            report($e);
            $result = RailResult::unknown();
        }
        if ($result->status === 'unknown') {
            $result = Cloud9Rail::result($request->all(), $kind);
        }
        if ($result->status === 'succeeded' && $kind === 'collect' && (int) round(((float) $request->input('amount')) * 100) !== $req->amount) {
            $result = RailResult::unknown('Cloud9 reported a different amount: left for reconciliation.');
        }

        try {
            match (true) {
                ! $result->final() => null,
                // Money confirmed after we had given up on it: book it, never drop it
                $req->status === 'failed' && $result->status === 'succeeded' && $kind === 'collect' => $orders->lateCollect($req, $result),
                default => $orders->apply($req, $result),
            };
        } catch (\App\Ledger\LedgerException $e) {
            // Ledger frozen: the payload is kept above; ramp:resolve books it once the ledger reopens
            throw_unless($e->reason === 'ledger_frozen', $e);
        }

        return response()->json(['ok' => true]);
    }
}
