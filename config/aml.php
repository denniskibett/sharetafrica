<?php

/*
| AML screening (App\Aml\Screening): every payment is checked before any money moves (an M-PESA prompt, a payout through
| Cloud9 or the paybill, a Sharet-to-Sharet transfer). A rule can hold a payment for a person to review (Compliance ›
| AML checks) or block it. Starting figures, to be set by the owner (docs/v2/DECISIONS-FOR-APPROVAL.md). Cents.
*/

return [
    'enabled' => (bool) env('AML_SCREENING', true),

    'max_payments_per_hour' => 10,            // velocity: payments (any kind) by one person in 60 minutes
    'max_out_per_hour' => 15_000_000,         // KES 150,000 out in 60 minutes
    'structuring_share' => 0.9,               // payments at ≥ 90% of the per-payment limit…
    'structuring_count' => 2,                 // …after this many such payments in 24 hours: the next is held
    'new_payee_large' => 2_000_000,           // KES 20,000 to someone never paid before
    'new_account_hours' => 24,                // an account younger than this…
    'new_account_large' => 1_000_000,         // …sending KES 10,000 or more
    'in_out_min_in' => 1_000_000,             // KES 10,000 in during the last hour…
    'in_out_share' => 0.9,                    // …and 90% of it going straight back out…
    'in_out_payees' => 3,                     // …to this many different people
    'fan_in_senders' => 10,                   // this many different people paid the wallet in 24 hours (a possible mule)
    'pep_large' => 5_000_000,                 // a politically exposed person paying KES 50,000 or more
];
