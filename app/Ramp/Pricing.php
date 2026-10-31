<?php

namespace App\Ramp;

use App\Ledger\Money;

/** Fee, excise and rounding for a payment (config/pricing.php). All cents; the payer pays whole shillings. */
final class Pricing
{
    /** @return array{fee: int, excise: int, rounding: int, total: int} */
    public static function quote(int $amount): array
    {
        $cfg = config('pricing');
        $fee = max($cfg['fee_min'], min($cfg['fee_max'], Money::percent($amount, $cfg['fee_bp'])));
        $excise = Money::percent($fee, $cfg['excise_bp']);
        $subtotal = $amount + $fee + $excise;
        $total = intdiv($subtotal + 99, 100) * 100; // up to the next whole shilling, integers only

        return ['fee' => $fee, 'excise' => $excise, 'rounding' => $total - $subtotal, 'total' => $total];
    }
}
