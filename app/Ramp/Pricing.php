<?php

namespace App\Ramp;

use App\Ledger\Money;

/**
 * Fee, excise and rounding for a payment (config/pricing.php): the partner's charge (Cloud9's, or Safaricom's for the
 * amount band) plus Sharet's markup (App\Ramp\Markup), excise on the markup. Up to 100.00 is free. All cents; the payer pays whole shillings.
 */
final class Pricing
{
    /** @return array{fee: int, excise: int, rounding: int, total: int} */
    public static function quote(int $amount, ?int $partnerCharge = null, ?\App\Models\User $user = null): array
    {
        $cfg = config('pricing');
        // The partner's own charge (Cloud9's quoted payout charge when Cloud9 carries it, else Safaricom's band) plus
        // Sharet's markup (0.5%, at least KES 1, none up to KES 100)
        $band = collect($cfg['safaricom_bands'])->first(fn ($b) => $amount <= $b[0]) ?? end($cfg['safaricom_bands']);
        $partner = $partnerCharge ?? $band[1];
        $markup = Markup::cents($amount, $user);
        $fee = $partner + $markup;
        $excise = Money::percent($markup, $cfg['excise_bp']);
        $subtotal = $amount + $fee + $excise;
        $total = intdiv($subtotal + 99, 100) * 100; // up to the next whole shilling, integers only

        return ['fee' => $fee, 'excise' => $excise, 'rounding' => $total - $subtotal, 'total' => $total];
    }
}
