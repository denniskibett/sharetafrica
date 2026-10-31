<?php

/*
| Personal payment pricing and limits. Amounts in cents.
| Revenue-share tariff: the fee is what Safaricom charges for the amount band (M-PESA send-money tariff) plus our
| markup (markup_bp of that charge). Up to 100.00 is free, as on M-PESA. Excise duty (15%) applies to our markup;
| Safaricom's charge already carries its own. The customer pays whole shillings: leftover cents go to 4900 Rounding.
| CHECK the bands against Safaricom's current tariff sheet (and Cashia's / the Daraja B2C rate) before going live.
| Wallet-to-wallet transfers and adding money are free.
*/

return [
    // [up to amount, Safaricom charge], cents, ascending; above the last band the last charge applies
    'safaricom_bands' => [
        [10_000, 0],          //      1 –    100: free
        [50_000, 700],        //    101 –    500
        [100_000, 1_300],     //    501 –  1,000
        [150_000, 2_300],     //  1,001 –  1,500
        [250_000, 3_300],     //  1,501 –  2,500
        [350_000, 5_300],     //  2,501 –  3,500
        [500_000, 5_700],     //  3,501 –  5,000
        [750_000, 7_800],     //  5,001 –  7,500
        [1_000_000, 9_000],   //  7,501 – 10,000
        [1_500_000, 10_000],  // 10,001 – 15,000
        [2_000_000, 10_500],  // 15,001 – 20,000
        [25_000_000, 10_800], // 20,001 – 250,000
    ],
    'markup_bp' => 2000,      // 20% on top of Safaricom's charge
    'excise_bp' => 1500,      // 15% of our markup
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
