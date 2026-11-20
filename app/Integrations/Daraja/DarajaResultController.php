<?php

namespace App\Integrations\Daraja;

use App\Integrations\RailResult;
use App\Models\RailRequest;
use App\Ramp\RampOrders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Safaricom's result for a B2C / B2B payout (ResultURL), and the queue timeout (QueueTimeOutURL). No session or CSRF;
 * the secret path is the guard. Keeps every payload on the request; a success books through RampOrders::apply()
 * (idempotent), a timeout is never a failure: the money may still move, so the payout stays waiting.
 */
class DarajaResultController
{
    public function result(Request $request, string $token, RampOrders $orders): JsonResponse
    {
        abort_unless(hash_equals(DarajaRail::callbackToken(), $token), 404);
        $r = $request->input('Result', []);
        $rail = $this->find($r['ConversationID'] ?? null, $r['OriginatorConversationID'] ?? null);

        if ($rail) {
            $this->keep($rail, 'results', $r);
            $params = collect($r['ResultParameters']['ResultParameter'] ?? [])->pluck('Value', 'Key');
            $amount = $params->get('TransactionAmount') ?? $params->get('Amount');
            $result = match (true) {
                (string) ($r['ResultCode'] ?? '') !== '0' => RailResult::failed($r['ResultDesc'] ?? 'M-PESA declined the payout.', $rail->provider_reference),
                $amount !== null && (int) round(((float) $amount) * 100) !== $rail->amount => RailResult::unknown('M-PESA reported a different amount: left for reconciliation.'),
                default => RailResult::succeeded((string) ($r['TransactionID'] ?? $params->get('TransactionReceipt')), $rail->provider_reference),
            };
            $result->final() ? $orders->apply($rail, $result) : report(new \RuntimeException("Daraja result for {$rail->reference}: {$result->message}"));
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function timeout(Request $request, string $token): JsonResponse
    {
        abort_unless(hash_equals(DarajaRail::callbackToken(), $token), 404);
        $body = $request->all();
        if ($rail = $this->find($body['ConversationID'] ?? null, $body['OriginatorConversationID'] ?? null)) {
            $this->keep($rail, 'timeouts', $body);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function find(?string $conversation, ?string $originator): ?RailRequest
    {
        $rail = $conversation ? RailRequest::where('rail', 'daraja')->where('provider_reference', $conversation)->first() : null;
        if (! $rail && $originator && preg_match('/^SHARET-(\d+)-/', $originator, $m)) {
            $rail = RailRequest::where('rail', 'daraja')->whereKey((int) $m[1])->first();
            $rail = $rail && DarajaRail::originator($rail) === $originator ? $rail : null;
        }

        return $rail;
    }

    private function keep(RailRequest $rail, string $kind, array $payload): void
    {
        $meta = $rail->meta ?? [];
        $meta[$kind] = array_merge($meta[$kind] ?? [], [['at' => now()->toIso8601String(), 'body' => $payload]]);
        $rail->forceFill(['meta' => $meta])->save();
    }
}
