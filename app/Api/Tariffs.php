<?php

namespace App\Api;

use App\Models\Company;
use App\Models\RampOrder;
use App\Models\Tariff;
use App\Models\User;
use App\Ramp\Pricing;

/**
 * Prices API movements from the tariff tables (docs/v2/INTEGRATION.md §7):
 *   fee = Table 1 (movement type × amount band: fixed + percent, capped) − Table 2 discount (the account's monthly
 *   outside volume so far, priced at the tier it starts in; Sharet group companies one tier up), then excise on the
 *   fee, and the total rounded up to the next whole shilling. Sharet ↔ Sharet is always free.
 * Connected accounts are priced on their own volume. Every API payment records the tariff version and discount used.
 */
final class Tariffs
{
    /** API payment type → tariff movement. */
    public static function movement(string $type): string
    {
        return match ($type) {
            'sharet' => 'sharet',
            'bank' => 'payout_bank',
            default => 'payout_mobile',   // mpesa, airtel, tkash, paybill, till
        };
    }

    /** @return array{fee: int, excise: int, rounding: int, total: int, tariff_id: ?int, tariff_version: ?int, discount_bp: int, volume: int} */
    public function quote(User $account, string $type, int $amount): array
    {
        $movement = self::movement($type);
        $tariff = Tariff::current();
        if ($movement === 'sharet') {
            return ['fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => $amount, 'tariff_id' => $tariff?->id, 'tariff_version' => $tariff?->version, 'discount_bp' => 0, 'volume' => 0];
        }
        if (! $tariff) {   // no table yet: the personal tariff, so a price is always shown before money moves
            return Pricing::quote($amount) + ['tariff_id' => null, 'tariff_version' => null, 'discount_bp' => 0, 'volume' => 0];
        }

        $band = collect($tariff->bands[$movement] ?? [])->first(fn ($b) => $b['up_to'] === null || $amount <= $b['up_to']);
        $raw = $band ? (int) $band['fixed'] + intdiv($amount * (int) $band['percent_bp'], 10_000) : 0;
        if ($band && $band['cap'] !== null) {
            $raw = min($raw, (int) $band['cap']);
        }

        $volume = $this->monthlyVolume($account);
        $discountBp = $this->discount($tariff, $volume, $this->isGroup($account));
        $fee = $raw - intdiv($raw * $discountBp, 10_000);
        // Never below the partner's own charge plus Sharet's markup (less the same discount): Sharet never pays to move a business's money
        $fee = max($fee, Pricing::quote($amount, $this->partnerCharge($account, $type, $amount), $account)['fee']);
        $excise = intdiv($fee * (int) $tariff->excise_bp + 5_000, 10_000);
        $subtotal = $amount + $fee + $excise;
        $total = intdiv($subtotal + 99, 100) * 100;

        return ['fee' => $fee, 'excise' => $excise, 'rounding' => $total - $subtotal, 'total' => $total,
            'tariff_id' => $tariff->id, 'tariff_version' => $tariff->version, 'discount_bp' => $discountBp, 'volume' => $volume];
    }

    /** What the partner carrying this payout charges: Cloud9's own quote when Cloud9 pays it, else null (Safaricom's band). */
    private function partnerCharge(User $account, string $type, int $amount): ?int
    {
        $rail = app(\App\Integrations\Rails::class)->paying($account, \App\Api\PaymentInput::rampType($type));

        return $rail === 'cloud9' ? \App\Integrations\Cloud9\Cloud9Rail::payoutCharge(\App\Api\PaymentInput::rampType($type), $amount) : null;
    }

    /** The business's volume discount (Table 2) now, in basis points; 0 without a tariff. */
    public function discountBp(User $account): int
    {
        $tariff = Tariff::current();

        return $tariff ? $this->discount($tariff, $this->monthlyVolume($account), $this->isGroup($account)) : 0;
    }

    /** The account's outside payouts this calendar month (Nairobi), before this one. */
    public function monthlyVolume(User $account): int
    {
        return (int) RampOrder::where('user_id', $account->id)->whereNotIn('destination_type', ['wallet', 'sharet'])
            ->whereIn('status', ['paying', 'paid'])->where('created_at', '>=', now('Africa/Nairobi')->startOfMonth()->utc())->sum('amount');
    }

    private function discount(Tariff $tariff, int $volume, bool $group): int
    {
        $tiers = collect($tariff->volume_tiers)->sortBy('from')->values();
        $i = $tiers->search(fn ($t, $k) => $volume >= $t['from'] && ($k === $tiers->count() - 1 || $volume < $tiers[$k + 1]['from']));
        $i = $i === false ? 0 : $i;
        if ($group) {
            $i = min($i + 1, $tiers->count() - 1);
        }

        return (int) ($tiers[$i]['discount_bp'] ?? 0);
    }

    private function isGroup(User $account): bool
    {
        return $account->is_business_wallet && $account->company_id && (bool) Company::whereKey($account->company_id)->value('is_group_company');
    }
}
