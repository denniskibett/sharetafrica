<?php

namespace App\Ramp;

use App\Ledger\Money;

/**
 * Fee, excise and rounding for a payment (config/pricing.php): Safaricom's charge for the amount band plus our markup,
 * excise on the markup. Up to 100.00 is free. All cents; the payer pays whole shillings.
 */
final class Pricing
{
    /** @return array{fee: int, excise: int, rounding: int, total: int} */
    public static function quote(int $amount): array
    {
        $cfg = config('pricing');
        $band = collect($cfg['safaricom_bands'])->first(fn ($b) => $amount <= $b[0]) ?? end($cfg['safaricom_bands']);
        $markup = Money::percent($band[1], $cfg['markup_bp']);
        $fee = $band[1] + $markup;
        $excise = Money::percent($markup, $cfg['excise_bp']);
        $subtotal = $amount + $fee + $excise;
        $total = intdiv($subtotal + 99, 100) * 100; // up to the next whole shilling, integers only

        return ['fee' => $fee, 'excise' => $excise, 'rounding' => $total - $subtotal, 'total' => $total];
    }
}
