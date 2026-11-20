<?php

namespace App\Support;

use App\Integrations\Sms\KenyaSms;
use App\Ledger\Money;
use App\Models\RampOrder;
use App\Models\User;

/**
 * SMS to customers as a payment moves (KenyaSMS), alongside the M-PESA prompt and the emailed receipt (Receipt):
 * - the prompt went out: approve it on your phone
 * - finished: paid / added to wallet / not paid / refunded, with the reference
 * - an @name transfer: the person paid gets their own message
 * Sent after the database commits, once per payment and status (KenyaSMS idempotency key); a failure never touches money.
 */
final class CustomerMessages
{
    public static function order(RampOrder $o): void
    {
        $p = $o->present();
        $total = 'KES '.Money::format($o->total);
        $ref = $o->reference();

        $text = match ($o->status) {
            'collecting' => "Sharet: approve {$total} on the M-PESA prompt on your phone"
                .($p['top_up'] ? ' to add money to your wallet' : ' to pay '.$o->recipient()).". Ref {$o->sharetReference()}. Didn't ask for it? Ignore the prompt.",
            'paid' => match (true) {
                $p['top_up'] => 'Sharet: KES '.Money::format($o->amount)." added to your wallet. M-PESA {$ref}.",
                $p['transfer'] => "Sharet: {$total} sent to {$o->recipient()}. Ref {$ref}.",
                default => 'Sharet: KES '.Money::format($o->amount)." sent to {$o->recipient()}. Total {$total} incl. fees. Ref {$ref}.",
            },
            'failed' => $o->failure && str_contains($o->failure, 'wallet instead')
                ? null : "Sharet: your payment of {$total} did not go through".($o->failure ? ': '.rtrim($o->failure, '.') : '').'. No money was taken.',
            'refunded' => "Sharet: {$total} could not be delivered and was refunded"
                .($o->funding === 'wallet' ? ' to your wallet' : ' to your M-PESA').". Ref {$ref}.",
            default => null,
        };
        if ($text && ($phone = self::phone($o->payer_phone))) {
            KenyaSms::send($phone, $text, "sms:{$o->uuid}:{$o->status}");
        }

        // the person paid on an @name transfer
        if ($o->status === 'paid' && $p['transfer'] && ($to = User::find($o->recipient_user_id)) && ($phone = self::phone($to->phone))) {
            $from = '@'.($o->sender?->handle ?? 'sharet');
            KenyaSms::send($phone, 'Sharet: you received KES '.Money::format($o->amount)." from {$from}. Ref {$ref}.", "sms:{$o->uuid}:received");
        }
    }

    /** 2547… from whatever is on file, or null when it isn't a usable Kenyan mobile number. */
    private static function phone(?string $raw): ?string
    {
        try {
            return filled($raw) ? \App\Ramp\Destination::phone((string) $raw) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
