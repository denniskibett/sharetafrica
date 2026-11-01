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
            $meta = collect($cb['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');
            $code = (string) ($cb['ResultCode'] ?? '');
            $result = match (true) {
                $code !== '0' => RailResult::failed(DarajaRail::reason($code, $cb['ResultDesc'] ?? null), $rail->provider_reference),
                (int) round(((float) $meta->get('Amount', 0)) * 100) !== $rail->amount => RailResult::unknown('M-PESA reported a different amount: left for reconciliation.'),
                default => RailResult::succeeded((string) $meta->get('MpesaReceiptNumber'), $rail->provider_reference),
            };
            $result->final() ? $orders->apply($rail, $result) : report(new \RuntimeException("Daraja callback for {$rail->reference}: {$result->message}"));
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
