<?php

namespace App\Support;

use App\Api\PaymentInput;
use App\Api\PaymentRequests;
use App\Integrations\Sms\Sms;
use App\Ledger\Money;
use App\Models\ApiPaymentRequest;
use App\Models\User;
use App\Ramp\Destination;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Personal requests and splits (canvas P05): ask an @name or any phone for money. The payer gets an SMS with a link
 * (/r/{token}): a Sharet member pays from their wallet with their PIN (free); anyone else pays by M-PESA prompt. A
 * member also sees it in Requests › To pay. Nothing moves until the payer approves. Built on payment requests.
 */
class PersonalRequests
{
    public const EXPIRY = ['48h' => 48 * 60, '7d' => 7 * 24 * 60];

    /** One request. $to: "@name" or a phone number. */
    public static function ask(User $payee, string $to, string $amount, ?string $note, string $expires = '48h', ?string $group = null, bool $notify = true): ApiPaymentRequest
    {
        $cents = self::cents($payee, $amount);
        [$payerUser, $payerPhone] = self::payer($payee, $to);
        $r = ApiPaymentRequest::create([
            'public_id' => PaymentRequests::newReference(true),
            'kind' => 'personal', 'group' => $group,
            'token' => Str::random(40),
            'api_key_id' => null, 'user_id' => $payee->id, 'environment' => 'live',
            'amount' => $cents,
            'reference' => filled($note) ? Str::limit(strip_tags((string) $note), 140, '') : null,
            'payer_user_id' => $payerUser?->id, 'payer_phone' => $payerPhone ?? $payerUser?->phone,
            'channels' => ['sharet', 'mpesa'],
            'status' => 'open',
            'expires_at' => now()->addMinutes(self::EXPIRY[$expires] ?? self::EXPIRY['48h']),
        ]);
        $notify && self::notify($r);

        return $r;
    }

    /**
     * Split a bill: the total shared equally between the people listed and, if $includeMe, you (your share is not
     * requested). One request each, in one group; the cents that don't divide go on the first request.
     * @return ApiPaymentRequest[]
     */
    public static function split(User $payee, string $total, array $people, ?string $note, bool $includeMe = true, string $expires = '7d'): array
    {
        $people = array_values(array_unique(array_filter(array_map('trim', $people))));
        if (count($people) < 1 || count($people) > 20) {
            throw ValidationException::withMessages(['people' => 'Add between 1 and 20 people to split with.']);
        }
        $shillings = intdiv(self::cents($payee, $total), 100);   // shares are whole shillings, like every payment
        $shares = count($people) + ($includeMe ? 1 : 0);
        $each = intdiv($shillings, $shares) * 100;
        if ($each < 1000) {
            throw ValidationException::withMessages(['amount' => 'Each share must be at least KES 10.']);
        }
        foreach ($people as $p) {
            self::payer($payee, $p);   // check everyone before creating any
        }
        $group = (string) Str::uuid();
        $first = $shillings * 100 - $each * $shares;   // the shillings that don't divide go on the first request

        return array_map(fn ($p, $i) => self::ask($payee, $p, Money::format($each + ($i === 0 ? $first : 0)), $note, $expires, $group), $people, array_keys($people));
    }

    /** Whole shillings, at least KES 10, as a form error (not the API's error format). */
    private static function cents(User $payee, string $amount): int
    {
        try {
            return PaymentInput::read(['amount' => $amount, 'to' => ['sharet_id' => $payee->sharet_id]], new User)['cents'];
        } catch (\App\Api\ApiError $e) {
            throw ValidationException::withMessages(['amount' => 'Enter whole shillings, at least KES 10.']);
        }
    }

    /** SMS the payer the link (also a member: it reaches them wherever they are). Remind = the same SMS again. */
    public static function notify(ApiPaymentRequest $r): bool
    {
        if (! $r->payer_phone) {
            return false;
        }
        $msg = $r->payee->name.' asks you for KES '.Money::format($r->amount).($r->reference ? ' ('.Str::limit($r->reference, 40).')' : '')
            .' on Sharet. Pay by M-PESA or your Sharet wallet: '.route('pay-request.show', $r->token).' Ref '.$r->public_id;

        return Sms::send(Destination::phone($r->payer_phone, 'phone'), $msg, 'request:'.$r->id.':'.($r->reminded_at?->timestamp ?? 0));
    }

    public static function remind(ApiPaymentRequest $r): bool
    {
        if ($r->status !== 'open') {
            throw ValidationException::withMessages(['request' => 'Only open requests can be reminded.']);
        }
        if ($r->reminded_at && $r->reminded_at->gt(now()->subHours(12))) {
            throw ValidationException::withMessages(['request' => 'You reminded them recently. Try again later.']);
        }
        $r->update(['reminded_at' => now()]);

        return self::notify($r->fresh());
    }

    /** The payer says no: closed, nothing moves, the person who asked sees "Declined". */
    public static function decline(ApiPaymentRequest $r, User $payer): void
    {
        if ($r->payer_user_id !== $payer->id || $r->status !== 'open') {
            throw ValidationException::withMessages(['request' => 'This request can\'t be declined.']);
        }
        $r->update(['status' => 'declined']);
    }

    /** "@name" → that member; anything else must be a phone (a member's number finds them). */
    private static function payer(User $payee, string $to): array
    {
        $to = trim($to);
        if (str_starts_with($to, '@')) {
            $user = Handle::find($to);
            if (! $user || $user->is_business_wallet) {
                throw ValidationException::withMessages(['to' => "No one on Sharet is {$to}."]);
            }
        } else {
            $phone = Destination::phone($to, 'to');
            $user = User::where('phone', $phone)->orWhere('phone', '+'.$phone)->orWhere('phone', '0'.substr($phone, 3))->first();
        }
        if ($user?->is($payee)) {
            throw ValidationException::withMessages(['to' => 'You can\'t ask yourself.']);
        }

        return [$user, $user ? null : ($phone ?? null)];
    }
}
