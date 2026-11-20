<?php

namespace App\Integrations\Cloud9;

use App\Integrations\Contracts\PaymentRail;
use App\Integrations\Contracts\ProvidesStatements;
use App\Integrations\RailResult;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Services\Integrations;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Cloud9 (developers.cloud9.money) as a payment rail behind Sharet. Customers never see Cloud9: the M-PESA prompt
 * carries the Sharet reference and "Sharet", and everything else they see comes from Sharet.
 *
 * Money in is an STK push (POST payin/deposits/init/) into Sharet's MAIN KES wallet at Cloud9; money out is a payout
 * (POST payout/payouts/init/): B2C to a phone, B2B to a paybill or till, a bank through its paybill. Both answer by
 * webhook (Cloud9CallbackController) or by reading the deposit or payout by its id (status()). Cloud9 keeps a charge:
 * out of a deposit, or on top of a payout; it is reported on the result so the ledger books it as Sharet's cost.
 * Credentials: the Cloud9 integration (environment, API key, callback secrets), never .env.
 */
class Cloud9Rail implements PaymentRail, ProvidesStatements
{
    public const MIN_IN = 2000;   // cents: Cloud9 takes deposits from KES 20

    public const MIN_OUT = 1000;  // cents: and pays out from KES 10

    public function name(): string
    {
        return 'cloud9';
    }

    public static function baseUrl(string $environment): string
    {
        return $environment === 'live' ? 'https://wallets.cloud9.money/api/v1/' : 'https://sandbox-wallets.cloud9.money/api/v1/';
    }

    public function collect(RailRequest $request): RailResult
    {
        if ($request->amount < self::MIN_IN) {
            return RailResult::failed('M-PESA payments start at KES 20.');
        }
        $c = $this->credentials();
        $res = $this->http()->post('payin/deposits/init/', array_filter([
            'country' => 'KE',
            'currency' => 'KES',
            'amount' => intdiv($request->amount, 100),
            'phone_number' => self::phone($request->party),
            'provider' => 'MPESA-C2B',
            // What the customer sees on the prompt: the Sharet reference (account) and "Sharet" (description)
            'reference' => self::sharetReference($request),
            'narration' => 'Sharet',
            'wallet_id' => $c['wallet_id'] ?? null,
            'callback_url' => route('rails.cloud9.callback'),
        ]));

        return $res->successful() && $res->json('id')
            ? RailResult::pending((string) $res->json('id'))
            : RailResult::failed('The M-PESA prompt could not be sent: '.self::error($res));
    }

    public function payout(RailRequest $request): RailResult
    {
        if ($request->amount < self::MIN_OUT) {
            return RailResult::failed('Payments out start at KES 10.');
        }
        $c = $this->credentials();
        $res = $this->http()->post('payout/payouts/init/', array_filter([
            'country' => 'KE',
            'currency' => 'KES',
            'amount' => intdiv($request->amount, 100),
            'reference' => self::clean($request->reference),   // unique per request: Cloud9 refuses a reused one when idempotency is on
            'narration' => 'Sharet '.self::sharetReference($request),
            'wallet_id' => $c['wallet_id'] ?? null,   // one MAIN wallet for everything: it mirrors ledger account 1160
            'callback_url' => route('rails.cloud9.callback'),
        ] + $this->destination($request)));

        if ($res->successful() && $res->json('id')) {
            return RailResult::pending((string) $res->json('id'));
        }
        if ($res->json('code') === 'duplicate_reference' && preg_match('/Payout (\w+) already uses/', (string) $res->json('error'), $m)) {
            return RailResult::pending($m[1]);   // our first attempt got through: follow that payout, never send another
        }

        return RailResult::failed('The payment could not be sent: '.self::error($res));
    }

    /** What Cloud9 says now, read by the deposit's or payout's id (a card top-up: its checkout, then its deposit). */
    public function status(RailRequest $request): RailResult
    {
        if (! $request->provider_reference) {
            return RailResult::unknown('The payment partner never acknowledged this request.');
        }
        if (($request->meta['method'] ?? null) === 'card' && $request->provider_reference === ($request->meta['checkout_id'] ?? null)) {
            $res = $this->http()->get('checkout/sessions/'.$request->provider_reference.'/');
            if (! $res->successful()) {
                return RailResult::unknown('No answer to the status check ('.$res->status().').');
            }
            $deposit = $res->json('deposit_id');
            $state = strtoupper((string) $res->json('status'));
            if (! $deposit) {
                return in_array($state, ['EXPIRED', 'FAILED', 'CANCELLED'], true)
                    ? RailResult::failed('The card payment was not completed.', $request->provider_reference)
                    : RailResult::pending($request->provider_reference);
            }
            $res = $this->http()->get('payin/deposits/'.$deposit.'/');

            return $res->successful() ? self::cardResult($res->json(), $request) : RailResult::unknown('No answer to the status check ('.$res->status().').');
        }
        $path = $request->kind === 'collect' ? 'payin/deposits/' : 'payout/payouts/';
        $res = $this->http()->get($path.$request->provider_reference.'/');
        if (! $res->successful()) {
            return RailResult::unknown('No answer to the status check ('.$res->status().').');
        }

        return self::result($res->json(), $request->kind);
    }

    /** One deposit or payout (from the API or a webhook) as a RailResult. */
    public static function result(array $p, string $kind): RailResult
    {
        $id = (string) ($p['id'] ?? '');
        $charge = self::cents($p['charge'] ?? 0) + self::cents($p['taxes'] ?? 0);

        return match (strtoupper((string) ($p['status'] ?? ''))) {
            'SUCCESS' => RailResult::succeeded((string) (($p['provider_reference'] ?? null) ?: $id), $id, $charge, self::network((string) ($p['provider'] ?? ''))),
            'FAILED' => RailResult::failed(self::plain($p['message'] ?? null) ?: ($kind === 'collect' ? 'The M-PESA payment was not completed.' : 'The payment could not be delivered.'), $id),
            default => RailResult::pending($id),   // PENDING, PROCESSING, IN_REVIEW: wait for the webhook, never resend
        };
    }

    /**
     * A card top-up's deposit. The customer pays the card charge on top (charge_bearer CUSTOMER), so the wallet gets
     * the amount asked for: no charge for the ledger; the card charge is kept on the request for the receipt.
     */
    public static function cardResult(array $p, RailRequest $request): RailResult
    {
        $r = self::result($p, 'collect');
        if ($r->status !== 'succeeded') {
            return $r;
        }
        $net = self::cents($p['net_amount'] ?? $p['amount'] ?? 0);
        if ($net < $request->amount) {
            return RailResult::unknown('The card payment arrived short: left for reconciliation.');
        }

        return RailResult::succeeded($r->receipt, $r->providerReference, 0, 'Card');
    }

    /** A hosted card page for a top-up: the customer pays the card charge on top. Returns [checkout id, url]. */
    public function checkout(RailRequest $request, \App\Models\User $user): array
    {
        if ($request->amount < self::MIN_IN) {
            throw new \RuntimeException('Card payments start at KES 20.');
        }
        [$first, $last] = array_pad(explode(' ', trim((string) $user->name), 2), 2, '');
        $res = $this->http()->post('checkout/sessions/', array_filter([
            'amount' => number_format($request->amount / 100, 2, '.', ''),
            'currency' => 'KES',
            'charge_bearer' => 'CUSTOMER',
            'customer' => array_filter(['email' => $user->email, 'first_name' => $first, 'last_name' => $last, 'phone_number' => $user->phone ? self::phone($user->phone) : null]),
            'reference' => self::sharetReference($request),
            'description' => 'Sharet wallet top-up',
            'wallet_id' => $this->credentials()['wallet_id'] ?? null,
            'redirect_url' => route('pay.card.return', $request->meta['order'] ?? ''),
            'callback_url' => route('rails.cloud9.callback'),
        ]));
        if (! $res->successful() || ! $res->json('checkout_url')) {
            throw new \RuntimeException('The card page could not be opened: '.self::error($res));
        }

        return [(string) $res->json('checkout_id'), (string) $res->json('checkout_url'), self::cents($res->json('charge') ?? 0) + self::cents($res->json('taxes') ?? 0)];
    }

    /** Sharet's MAIN KES wallet at Cloud9 (the integration's wallet ID, else read once from the API). */
    public function mainWalletId(): string
    {
        $c = $this->credentials();
        if (filled($c['wallet_id'] ?? null)) {
            return $c['wallet_id'];
        }

        return \Illuminate\Support\Facades\Cache::rememberForever('cloud9:main-wallet:'.md5($c['api_key']), function () {
            $wallet = collect((array) $this->http()->get('wallets/')->throw()->json('results'))
                ->first(fn ($w) => ($w['currency'] ?? '') === 'KES' && ($w['wallet_type'] ?? '') === 'MAIN');

            return $wallet['wallet_id'] ?? throw new \RuntimeException('Cloud9 has no MAIN KES wallet for this key.');
        });
    }

    /**
     * A person's own account number on Sharet's paybill: anyone pays it from the M-PESA menu and it lands in their
     * wallet. Cloud9 picks the number; it never changes. Label = their name, reference = their Sharet ID.
     * @return array{account_id: string, account: int, paybill: string}
     */
    public function createAccount(\App\Models\User $user): array
    {
        $res = $this->http()->post('wallets/'.$this->mainWalletId().'/paybill_accounts/', [
            'label' => mb_substr((string) $user->name, 0, 100),
            'reference' => self::clean((string) $user->sharet_id),
        ]);
        if (! $res->successful() || ! $res->json('account')) {
            throw new \RuntimeException('Cloud9 did not create the account number: '.self::error($res));
        }

        return ['account_id' => (string) $res->json('account_id'), 'account' => (int) $res->json('account'), 'paybill' => (string) $res->json('paybill_number')];
    }

    /**
     * The day's statement from Cloud9's own records: every successful deposit (money in) and payout (money out),
     * matched by reconciliation to our requests by the Cloud9 id.
     */
    public function statement(string $day): array
    {
        $lines = [];
        foreach (['in' => 'payin/deposits/', 'out' => 'payout/payouts/'] as $direction => $path) {
            $url = $path;
            $query = ['status' => 'SUCCESS', 'created_at_after' => $day, 'created_at_before' => $day];
            for ($page = 0; $url && $page < 50; $page++) {
                $res = $this->http()->get($url, $query)->throw();
                foreach ((array) $res->json('results') as $p) {
                    $lines[] = [
                        'line_ref' => $direction.':'.$p['id'],
                        'direction' => $direction,
                        'amount' => self::cents($p['amount'] ?? 0),
                        'party' => (string) ($p['phone_number'] ?? $p['account'] ?? ''),
                        'receipt' => (string) (($p['provider_reference'] ?? null) ?: $p['id']),
                        'occurred_at' => \Carbon\Carbon::parse($p['updated_at'] ?? $p['created_at'] ?? $day),
                        'provider_reference' => (string) $p['id'],
                        'raw' => $p,
                    ];
                }
                $url = $res->json('next');
                $query = [];
            }
        }

        return $lines;
    }

    /** Cloud9's fields for where a payout goes: a phone (B2C), a paybill or till (B2B), a bank through its paybill. */
    private function destination(RailRequest $request): array
    {
        $type = $request->meta['destination_type'] ?? 'phone';
        $d = (array) ($request->meta['destination'] ?? []);

        return match ($type) {
            'paybill' => ['provider' => 'MPESA-B2B', 'account' => (string) $d['paybill'], 'account_type' => 'PayBill', 'account_reference' => self::clean((string) ($d['account'] ?? 'Sharet'))],
            'till' => ['provider' => 'MPESA-B2B', 'account' => (string) $d['till'], 'account_type' => 'TillNumber'],
            // On live, PesaLink isn't open yet: banks are paid through their M-PESA paybill (config pricing.bank_paybills)
            'bank' => ['provider' => 'MPESA-B2B', 'account' => (string) (config('pricing.bank_paybills.'.($d['bank'] ?? ''))
                ?? throw new \RuntimeException('No M-PESA paybill for bank '.($d['bank'] ?? '?'))), 'account_type' => 'PayBill',
                'account_reference' => self::clean((string) ($d['account'] ?? ''))],
            default => ['provider' => 'MPESA-B2C', 'account' => self::phone($request->party)],
        };
    }

    /** The Sharet reference the customer knows (SHUK7Q2M4D), from the order the request belongs to. */
    private static function sharetReference(RailRequest $request): string
    {
        $order = isset($request->meta['order']) ? RampOrder::where('uuid', $request->meta['order'])->first() : null;

        return self::clean($order?->sharetReference() ?? 'Sharet');
    }

    /** The network behind a Cloud9 provider code: what the money really moved on, and whose reference the receipt is. */
    public static function network(string $provider): ?string
    {
        $p = strtoupper($provider);

        return match (true) {
            $p === '' => null,
            str_starts_with($p, 'MPESA') => 'M-PESA',
            str_starts_with($p, 'PESALINK') => 'PesaLink (bank)',
            str_ends_with($p, '-CARD') => 'Card',
            str_contains($p, 'AIRTEL') => 'Airtel Money',
            default => $provider,
        };
    }

    /** Cloud9 accepts letters, digits, spaces, - _ and : in references and narrations. */
    public static function clean(string $v): string
    {
        return trim(mb_substr(preg_replace('/[^A-Za-z0-9 _:\-]/', '', $v), 0, 100));
    }

    /** A provider message with the partner's name taken out (customers can see failure reasons). */
    private static function plain(?string $msg): ?string
    {
        return $msg ? trim(preg_replace('/\bcloud\s?9\b/i', 'the payment partner', $msg)) : null;
    }

    private static function phone(string $party): string
    {
        $d = preg_replace('/\D/', '', $party);

        return str_starts_with($d, '0') ? '254'.substr($d, 1) : $d;
    }

    private static function cents(mixed $v): int
    {
        return (int) round(((float) $v) * 100);
    }

    private static function error(\Illuminate\Http\Client\Response $res): string
    {
        $msg = $res->json('error') ?? $res->json('errors.0.detail') ?? $res->json('detail') ?? null;

        // Customers can see this text: never the partner's name, only what went wrong
        return is_string($msg) && $msg !== '' ? mb_substr(trim(preg_replace('/\bcloud\s?9\b/i', 'the payment partner', $msg)), 0, 200) : 'error '.$res->status();
    }

    /** @return array the Cloud9 integration's settings */
    public function credentials(): array
    {
        $c = (array) Integrations::credentials('cloud9');
        if (blank($c['api_key'] ?? null)) {
            throw new \RuntimeException('The Cloud9 integration needs its API key (Integrations › Cloud9).');
        }

        return $c;
    }

    public function http(): PendingRequest
    {
        $c = $this->credentials();

        return Http::baseUrl(self::baseUrl($c['environment'] ?? 'sandbox'))->timeout(30)->withToken($c['api_key'])->acceptJson()->asJson();
    }
}
