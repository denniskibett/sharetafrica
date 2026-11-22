<?php

namespace App\Aml;

use App\Models\AmlCheck;
use App\Models\AmlWatch;
use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * AML screening, before any money moves (you asked, 22 Nov: "before anything hits Cloud9"). Runs on every payment
 * when it is confirmed: an M-PESA prompt, a payout, a Sharet-to-Sharet transfer. Each rule either holds the payment for
 * a person to review (Compliance › AML checks) or blocks it. Figures: config/aml.php. A payment staff approved passes.
 *
 * Rules: watch list (block) · too many payments, or too much out, in an hour · structuring just under the limit · a
 * large first payment to a new payee · a large payment from a new account · money in and straight out to many people ·
 * many different people paying one wallet then money out (mule) · a politically exposed person paying a large amount.
 */
class Screening
{
    /** @return array{decision: string, hits: array} decision: allow | hold | block */
    public static function evaluate(RampOrder $o): array
    {
        if (! config('aml.enabled') || ($o->exists && AmlCheck::where('ramp_order_id', $o->id)->where('status', 'approved')->exists())) {
            return ['decision' => 'allow', 'hits' => []];
        }
        $user = $o->user ?? User::find($o->user_id);
        $c = config('aml');
        $block = [];
        $hold = [];
        $d = (array) $o->destination;
        $outgoing = $o->destination_type !== 'wallet';

        // Watch list: the payee, the payer's phone, or either Sharet account
        $recipient = $o->recipient_user_id ? User::find($o->recipient_user_id) : null;
        $watched = AmlWatch::query()->where(function ($q) use ($o, $d, $user, $recipient) {
            foreach (array_filter(['phone' => [$d['phone'] ?? null, $o->payer_phone, $user?->phone], 'paybill' => [$d['paybill'] ?? null], 'till' => [$d['till'] ?? null],
                'account' => [$d['account'] ?? null], 'sharet_id' => [$user?->sharet_id, $recipient?->sharet_id]]) as $type => $values) {
                $values = array_values(array_filter(array_map(fn ($v) => self::norm((string) $v), $values)));
                $values && $q->orWhere(fn ($w) => $w->where('type', $type)->whereIn('value', $values));
            }
        })->first();
        $watched && $block[] = ['code' => 'watch_list', 'label' => 'On the watch list: '.$watched->reason];

        // A verified business's own wallet (API payouts, settlements, payroll) pays many new people in bulk by design:
        // it is screened against the watch list only; its limits and two-person approvals still apply.
        if ($user->is_business_wallet) {
            $hits = $block;

            return ['decision' => $block ? 'block' : 'allow', 'hits' => $hits];
        }
        // Paying a verified business (rent, a bill on a business's request) is not "someone new"
        $toBusiness = (bool) $recipient?->is_business_wallet
            || (isset($d['ref']) && \App\Models\ApiPaymentRequest::where('public_id', $d['ref'])->where('kind', 'api')->exists());   // a business's API request

        $mine = fn () => RampOrder::where('user_id', $user->id)->whereNotIn('status', ['quoted', 'expired', 'reversed', 'rejected'])->when($o->exists, fn ($q) => $q->whereKeyNot($o->id));
        $hour = now()->subHour();
        if ($mine()->where('created_at', '>=', $hour)->count() + 1 > $c['max_payments_per_hour']) {
            $hold[] = ['code' => 'velocity_count', 'label' => 'More than '.$c['max_payments_per_hour'].' payments in an hour'];
        }

        if ($outgoing) {
            $outHour = (int) $mine()->where('destination_type', '!=', 'wallet')->where('created_at', '>=', $hour)->sum('total');
            if ($outHour + $o->total > $c['max_out_per_hour']) {
                $hold[] = ['code' => 'velocity_amount', 'label' => 'More than KES '.number_format($c['max_out_per_hour'] / 100).' out in an hour'];
            }
            $perOrder = (int) (config('pricing.limits.'.($user->kyc_level ?: 'none').'.per_order') ?? 0);
            $near = (int) round($perOrder * $c['structuring_share']);
            if ($perOrder > 0 && $o->total >= $near
                && $mine()->where('destination_type', '!=', 'wallet')->where('created_at', '>=', now()->subDay())->where('total', '>=', $near)->count() >= $c['structuring_count']) {
                $hold[] = ['code' => 'structuring', 'label' => 'Several payments just under the limit in a day'];
            }
            $paidBefore = $mine()->where('status', 'paid')->where('destination_type', $o->destination_type)->get(['destination_type', 'destination', 'recipient_user_id'])
                ->contains(fn ($p) => self::payeeKey($p->destination_type ?? $o->destination_type, (array) $p->destination, $p->recipient_user_id) === self::payeeKey($o->destination_type, $d, $o->recipient_user_id));
            if (! $paidBefore && ! $toBusiness && $o->total >= $c['new_payee_large']) {
                $hold[] = ['code' => 'new_payee_large', 'label' => 'A large first payment to someone new'];
            }
            if (! $toBusiness && $user->created_at && $user->created_at->gt(now()->subHours($c['new_account_hours'])) && $o->total >= $c['new_account_large']) {
                $hold[] = ['code' => 'new_account_large', 'label' => 'A large payment from an account opened in the last '.$c['new_account_hours'].' hours'];
            }
            $in = self::inflows($user, $hour);
            if ($in->sum('amount') >= $c['in_out_min_in']) {
                $outs = $mine()->where('destination_type', '!=', 'wallet')->where('created_at', '>=', $hour)->get();
                $payees = $outs->toBase()->map(fn ($p) => self::payeeKey($p->destination_type, (array) $p->destination, $p->recipient_user_id))->push(self::payeeKey($o->destination_type, $d, $o->recipient_user_id))->unique()->count();
                if ($outs->sum('total') + $o->total >= $in->sum('amount') * $c['in_out_share'] && $payees >= $c['in_out_payees']) {
                    $hold[] = ['code' => 'in_and_out', 'label' => 'Money in and straight back out to many people'];
                }
            }
            $senders = self::inflows($user, now()->subDay())->pluck('from')->filter()->unique()->count();
            if ($senders >= $c['fan_in_senders']) {
                $hold[] = ['code' => 'fan_in', 'label' => $senders.' different people paid this wallet in a day'];
            }
            $kyc = \App\Support\Kyc::data($user);
            if (! empty($kyc['pep']) && $o->total >= $c['pep_large']) {
                $hold[] = ['code' => 'pep_large', 'label' => 'A politically exposed person paying a large amount'];
            }
        }

        $hits = array_merge($block, $hold);

        return ['decision' => $block ? 'block' : ($hold ? 'hold' : 'allow'), 'hits' => $hits];
    }

    /** Record a hold or block for review. */
    public static function record(RampOrder $o, array $result): ?AmlCheck
    {
        if ($result['decision'] === 'allow') {
            return null;
        }

        return AmlCheck::create(['ramp_order_id' => $o->exists ? $o->id : null, 'user_id' => $o->user_id, 'decision' => $result['decision'], 'hits' => $result['hits'],
            'amount' => $o->total, 'status' => $result['decision'] === 'block' ? 'closed' : 'pending']);
    }

    /** Money into this person's wallet since $since: paid top-ups and transfers received. [amount, from] */
    private static function inflows(User $user, \DateTimeInterface $since): Collection
    {
        return RampOrder::where('status', 'paid')->where('created_at', '>=', $since)
            ->where(fn ($q) => $q->where(fn ($w) => $w->where('user_id', $user->id)->where('destination_type', 'wallet'))->orWhere('recipient_user_id', $user->id))
            ->get(['user_id', 'recipient_user_id', 'destination_type', 'amount', 'payer_phone'])->toBase()
            ->map(fn ($p) => ['amount' => (int) $p->amount, 'from' => $p->recipient_user_id === $user->id ? 'u'.$p->user_id : 'p'.$p->payer_phone]);
    }

    private static function payeeKey(string $type, array $d, ?int $recipient): string
    {
        return $type.':'.($recipient ?: self::norm((string) ($d['phone'] ?? $d['paybill'] ?? $d['till'] ?? ''))).':'.self::norm((string) ($d['account'] ?? ''));
    }

    /** Digits and letters only, upper case: 0712 345 678, +254712345678 and 254712345678 all match. */
    public static function norm(string $v): string
    {
        $v = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $v));

        return preg_match('/^0([17]\d{8})$/', $v, $m) ? '254'.$m[1] : (preg_match('/^([17]\d{8})$/', $v, $m) ? '254'.$m[1] : $v);
    }
}
