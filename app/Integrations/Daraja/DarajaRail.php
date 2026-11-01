<?php

namespace App\Integrations\Daraja;

use App\Integrations\Contracts\PaymentRail;
use App\Integrations\RailResult;
use App\Models\RailRequest;
use App\Services\Integrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Safaricom Daraja (docs/v2/RAILS.md). Credentials come from the M-Pesa integration (live when it is on and filled,
 * else the sandbox: Integrations::mpesa()). Collections are STK push (Lipa na M-PESA online), answered by callback
 * (DarajaCallbackController) or an STK query. Payouts are B2C to phones and B2B to paybills, tills and banks, answered by
 * result callback (DarajaResultController); they need the initiator name and security credential in the integration.
 */
class DarajaRail implements PaymentRail
{
    public function name(): string
    {
        return 'daraja';
    }

    public function collect(RailRequest $request): RailResult
    {
        [$c, $base] = $this->connection();
        $time = now('Africa/Nairobi')->format('YmdHis');
        $till = ($c['kind'] ?? 'paybill') === 'till';
        $res = $this->http($base)->post('/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $c['shortcode'],
            'Password' => base64_encode($c['shortcode'].$c['passkey'].$time),
            'Timestamp' => $time,
            'TransactionType' => $till ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
            'Amount' => intdiv($request->amount, 100), // M-PESA takes whole shillings; orders are whole shillings
            'PartyA' => $request->party,
            'PartyB' => $till && filled($c['party_b'] ?? null) ? $c['party_b'] : $c['shortcode'],
            'PhoneNumber' => $request->party,
            'CallBackURL' => route('rails.daraja.callback', self::callbackToken()),
            'AccountReference' => 'Sharet',
            'TransactionDesc' => 'Sharet '.substr(strtoupper(str_replace('ramp:', '', $request->reference)), 0, 8),
        ]);

        return $res->json('ResponseCode') === '0'
            ? RailResult::pending($res->json('CheckoutRequestID'))
            : RailResult::failed('M-PESA did not send the prompt: '.($res->json('errorMessage') ?? $res->json('ResponseDescription') ?? 'HTTP '.$res->status()));
    }

    /**
     * Pay out from the shortcode's utility account: B2C to a phone, B2B to a paybill or till, a bank through its paybill.
     * Safaricom answers by result callback (DarajaResultController); this only says whether it was accepted.
     */
    public function payout(RailRequest $request): RailResult
    {
        [$c, $base] = $this->connection();
        if (blank($c['initiator_name'] ?? null) || blank($c['security_credential'] ?? null)) {
            return RailResult::failed('Payouts through M-PESA need the initiator name and security credential in the M-Pesa integration.');
        }
        $to = $this->destination($request);
        $common = [
            'Initiator' => $c['initiator_name'],
            'SecurityCredential' => $c['security_credential'],
            'Amount' => intdiv($request->amount, 100),
            'PartyA' => $c['shortcode'],
            'Remarks' => 'Sharet '.substr(strtoupper(str_replace('ramp:', '', $request->reference)), 0, 8),
            'QueueTimeOutURL' => route('rails.daraja.timeout', self::callbackToken()),
            'ResultURL' => route('rails.daraja.result', self::callbackToken()),
        ];
        $res = $to['type'] === 'phone'
            ? $this->http($base)->post('/mpesa/b2c/v3/paymentrequest', $common + [
                'OriginatorConversationID' => self::originator($request),
                'InitiatorName' => $c['initiator_name'],
                'CommandID' => 'BusinessPayment',
                'PartyB' => $to['party'],
                'Occasion' => 'Sharet',
            ])
            : $this->http($base)->post('/mpesa/b2b/v1/paymentrequest', $common + [
                'CommandID' => $to['type'] === 'till' ? 'BusinessBuyGoods' : 'BusinessPayBill',
                'SenderIdentifierType' => '4',
                'RecieverIdentifierType' => '4',
                'PartyB' => $to['party'],
                'AccountReference' => mb_substr($to['account'] ?? 'Sharet', 0, 13),
            ]);

        return $res->json('ResponseCode') === '0'
            ? RailResult::pending($res->json('ConversationID'))
            : RailResult::failed('M-PESA did not accept the payout: '.($res->json('errorMessage') ?? $res->json('ResponseDescription') ?? 'HTTP '.$res->status()));
    }

    /** Where a payout goes: a phone (B2C), a paybill or till (B2B), or a bank through its paybill (config pricing.bank_paybills). */
    private function destination(RailRequest $request): array
    {
        $type = $request->meta['destination_type'] ?? 'phone';
        $d = (array) ($request->meta['destination'] ?? []);

        return match ($type) {
            'paybill' => ['type' => 'paybill', 'party' => $d['paybill'], 'account' => $d['account'] ?? null],
            'till' => ['type' => 'till', 'party' => $d['till']],
            'bank' => ['type' => 'paybill', 'party' => config('pricing.bank_paybills.'.($d['bank'] ?? ''))
                ?? throw new \RuntimeException('No M-PESA paybill for bank '.($d['bank'] ?? '?')), 'account' => $d['account'] ?? null],
            default => ['type' => 'phone', 'party' => $request->party],
        };
    }

    /** Our id for a payout, echoed back in the result: lets the callback find the request even without the ConversationID. */
    public static function originator(RailRequest $request): string
    {
        return 'SHARET-'.$request->id.'-'.substr(md5($request->reference), 0, 8);
    }

    /** STK query by CheckoutRequestID. The M-PESA receipt only comes by callback, so a query success uses our reference. */
    public function status(RailRequest $request): RailResult
    {
        if ($request->kind === 'payout') {
            // payouts answer only by result callback; the Transaction Status API (also async) is the fallback to add
            return RailResult::unknown('Waiting for the M-PESA result.');
        }
        if (! $request->provider_reference) {
            return RailResult::unknown('The prompt was never acknowledged.');
        }
        [$c, $base] = $this->connection();
        $time = now('Africa/Nairobi')->format('YmdHis');
        $res = $this->http($base)->post('/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $c['shortcode'],
            'Password' => base64_encode($c['shortcode'].$c['passkey'].$time),
            'Timestamp' => $time,
            'CheckoutRequestID' => $request->provider_reference,
        ]);
        $code = $res->json('ResultCode');
        if (in_array((string) $code, ['4999', '500.001.1001'], true) || str_contains(strtolower((string) $res->json('ResultDesc')), 'processing')) {
            return RailResult::pending($request->provider_reference); // the customer has not finished on the phone yet
        }

        return match (true) {
            $code === '0' || $code === 0 => RailResult::succeeded($request->receipt ?: 'Q'.strtoupper(substr(md5($request->provider_reference), 0, 9)), $request->provider_reference),
            $code !== null => RailResult::failed(self::reason((string) $code, $res->json('ResultDesc')), $request->provider_reference),
            default => RailResult::pending($request->provider_reference), // still processing (errorCode 500.001.1001) or no answer
        };
    }

    /** The secret path part of the callback URL, so only Safaricom (who has the URL) can post results. */
    public static function callbackToken(): string
    {
        return substr(hash_hmac('sha256', 'daraja-callback', (string) config('app.key')), 0, 32);
    }

    public static function reason(string $code, ?string $desc): string
    {
        return match ($code) {
            '1032' => 'The customer cancelled the M-PESA prompt.',
            '1037' => 'The M-PESA prompt timed out on the phone.',
            '1' => 'Not enough money in M-PESA.',
            '2001' => 'Wrong M-PESA PIN.',
            default => $desc ?: 'M-PESA declined ('.$code.').',
        };
    }

    /** @return array{0: array, 1: string} credentials and API base URL */
    private function connection(): array
    {
        [$row, $mode] = Integrations::mpesa();
        $c = (array) $row?->credentials;
        if (blank($c['consumer_key'] ?? null) || blank($c['passkey'] ?? null)) {
            throw new \RuntimeException('The M-Pesa integration needs a consumer key, secret, shortcode and passkey.');
        }

        return [$c, $mode === 'live' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke'];
    }

    private function http(string $base): \Illuminate\Http\Client\PendingRequest
    {
        [$c] = $this->connection();
        $token = Cache::remember('daraja:token:'.md5($base.$c['consumer_key']), now()->addMinutes(50), fn () => Http::timeout(20)
            ->withBasicAuth($c['consumer_key'], $c['consumer_secret'])->get($base.'/oauth/v1/generate', ['grant_type' => 'client_credentials'])
            ->throw()->json('access_token'));

        return Http::baseUrl($base)->timeout(30)->withToken($token)->acceptJson();
    }
}
