<?php

namespace App\Integrations\Daraja;

use App\Integrations\RailResult;
use App\Models\RailRequest;
use App\Ramp\RampOrders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Where Safaricom posts the result of an STK push (no session, no CSRF; the secret path is the guard). Finds our
 * request by CheckoutRequestID, checks the amount, and hands the answer to RampOrders::apply(), which is idempotent,
 * so a repeated callback or one racing a status check posts once.
 */
class DarajaCallbackController
{
    public function __invoke(Request $request, string $token, RampOrders $orders): JsonResponse
    {
        abort_unless(hash_equals(DarajaRail::callbackToken(), $token), 404);
        $cb = $request->input('Body.stkCallback', []);
        $rail = RailRequest::where('rail', 'daraja')->where('provider_reference', $cb['CheckoutRequestID'] ?? '-')->first();

        if ($rail) {
            // keep every payload Safaricom sends, whatever we do with it
            $meta = $rail->meta ?? [];
            $meta['callbacks'] = array_merge($meta['callbacks'] ?? [], [['at' => now()->toIso8601String(), 'body' => $cb]]);
            $rail->forceFill(['meta' => $meta])->save();
            $items = collect($cb['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');
            $code = (string) ($cb['ResultCode'] ?? '');
            $result = match (true) {
                $code !== '0' => RailResult::failed(DarajaRail::reason($code, $cb['ResultDesc'] ?? null), $rail->provider_reference),
                (int) round(((float) $items->get('Amount', 0)) * 100) !== $rail->amount => RailResult::unknown('M-PESA reported a different amount: left for reconciliation.'),
                default => RailResult::succeeded((string) $items->get('MpesaReceiptNumber'), $rail->provider_reference),
            };
            try {
                match (true) {
                    ! $result->final() => report(new \RuntimeException("Daraja callback for {$rail->reference}: {$result->message}")),
                    // M-PESA confirms money we had already given up on: book it, never drop it
                    $rail->status === 'failed' && $result->status === 'succeeded' => $orders->lateCollect($rail, $result),
                    default => $orders->apply($rail, $result),
                };
            } catch (\App\Ledger\LedgerException $e) {
                // Ledger frozen: the payload is kept above; `php artisan ramp:resolve` books it once the ledger reopens
                throw_unless($e->reason === 'ledger_frozen', $e);
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
