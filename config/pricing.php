<?php

/*
| Personal "pay anyone" pricing and limits. DUMMY values until Cashia's tariff is known (plan U2): change here only.
| Amounts in cents. Fee = fee_bp of the amount, kept between fee_min and fee_max. Excise = 15% of the fee, added on top.
| The customer pays whole shillings (M-PESA): any cents left over go to 4900 Rounding.
*/

return [
    'fee_bp' => 100,          // 1%
    'fee_min' => 1000,        // 10.00
    'fee_max' => 10000,       // 100.00
    'excise_bp' => 1500,      // 15% of the fee
    'min_amount' => 1000,     // 10.00
    'quote_minutes' => 10,

    // by KYC level (App\Support\Kyc): per payment and per day, cents
    'limits' => [
        'none' => ['per_order' => 0, 'per_day' => 0],
        'basic' => ['per_order' => 7_000_000, 'per_day' => 7_000_000],        // 70,000.00
        'standard' => ['per_order' => 25_000_000, 'per_day' => 50_000_000],   // 250,000.00 / 500,000.00
    ],

    'banks' => [
        'equity' => 'Equity Bank', 'kcb' => 'KCB Bank', 'ncba' => 'NCBA Bank', 'imbank' => 'I&M Bank',
        'coop' => 'Co-operative Bank', 'absa' => 'Absa Bank', 'stanbic' => 'Stanbic Bank', 'family' => 'Family Bank',
    ],
];
