<?php

/*
| The chart of accounts (plan U5/U9, report section 05). Codes are fixed once live: never renumber.
|
| type   asset | liability | equity | income | expense. Assets and expenses grow with debits; the rest with credits.
| book   rail (mirrors a real account outside Sharet) | customer (money owed to someone) | gl (Sharet's own books)
| allow_negative  false = the account may never go below zero (after its holds). Real money and money owed to a
|                 customer can't be negative, so a journal that would make them negative is refused.
|
| `accounts` exist once (owner "system"). `templates` are created per owner on first use (a customer's wallet,
| one ramp order, one merchant's settlement), with the owner's code suffix.
*/

return [
    'currency' => 'KES',

    'accounts' => [
        '1110' => ['name' => 'M-PESA paybill · working (collections)', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1120' => ['name' => 'M-PESA paybill · utility (payout float)', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1130' => ['name' => 'PSP settlement · Cashia', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1140' => ['name' => 'PSP settlement · Gladys', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1150' => ['name' => 'M-PESA money in transit', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1210' => ['name' => 'I&M customer trust account', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1220' => ['name' => 'I&M Sharet operating account', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1250' => ['name' => 'Bank money in transit', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1310' => ['name' => 'Card processor receivable', 'type' => 'asset', 'book' => 'rail', 'allow_negative' => false],
        '1600' => ['name' => 'Owed by customers', 'type' => 'asset', 'book' => 'gl', 'allow_negative' => false],

        '2150' => ['name' => 'Payouts in flight', 'type' => 'liability', 'book' => 'customer', 'allow_negative' => false],
        '2300' => ['name' => 'Excise duty payable (KRA)', 'type' => 'liability', 'book' => 'gl', 'allow_negative' => false],
        '2900' => ['name' => 'Suspense · unidentified money in and held orders', 'type' => 'liability', 'book' => 'gl', 'allow_negative' => false],
        '2910' => ['name' => 'Suspense · reconciliation breaks', 'type' => 'liability', 'book' => 'gl', 'allow_negative' => true],

        '3100' => ['name' => 'Share capital and owner funds', 'type' => 'equity', 'book' => 'gl', 'allow_negative' => true],

        '4100' => ['name' => 'Sharet fees', 'type' => 'income', 'book' => 'gl', 'allow_negative' => true],
        '4110' => ['name' => 'Merchant and API fees', 'type' => 'income', 'book' => 'gl', 'allow_negative' => true],
        '4800' => ['name' => 'Test payments kept (real money paid in while testing)', 'type' => 'income', 'book' => 'gl', 'allow_negative' => true],
        '4900' => ['name' => 'Rounding differences', 'type' => 'income', 'book' => 'gl', 'allow_negative' => true],

        '5100' => ['name' => 'M-PESA charges', 'type' => 'expense', 'book' => 'gl', 'allow_negative' => true],
        '5110' => ['name' => 'Bank charges', 'type' => 'expense', 'book' => 'gl', 'allow_negative' => true],
        '5120' => ['name' => 'Card charges', 'type' => 'expense', 'book' => 'gl', 'allow_negative' => true],
        '5130' => ['name' => 'PSP charges', 'type' => 'expense', 'book' => 'gl', 'allow_negative' => true],
        '5900' => ['name' => 'Fraud and write-off losses', 'type' => 'expense', 'book' => 'gl', 'allow_negative' => true],
    ],

    'templates' => [
        '2100' => ['name' => 'Wallet', 'type' => 'liability', 'book' => 'customer', 'allow_negative' => false],
        '2110' => ['name' => 'Reserved in wallet', 'type' => 'liability', 'book' => 'customer', 'allow_negative' => false],
        '2200' => ['name' => 'Ramp order', 'type' => 'liability', 'book' => 'customer', 'allow_negative' => false],
        '2210' => ['name' => 'Merchant settlement payable', 'type' => 'liability', 'book' => 'customer', 'allow_negative' => false],
        '2410' => ['name' => 'Business payout float', 'type' => 'liability', 'book' => 'customer', 'allow_negative' => false],
    ],

    /*
    | Safeguarding (ledger:check): the real money in these accounts must cover everything owed to customers.
    | Codes match by prefix, so '2100' covers every customer's wallet.
    */
    'safeguarded_assets' => ['1110', '1120', '1130', '1140', '1150', '1210', '1250', '1310'],
    'customer_liabilities' => ['2100', '2110', '2150', '2200', '2210', '2410'],

    // A ramp order account older than this with money still in it is reported by ledger:check.
    'ramp_order_max_minutes' => 30,
];
