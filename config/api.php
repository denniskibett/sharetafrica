<?php

/* Public API v1 (docs/v2/INTEGRATION.md). Amounts in cents. */
return [
    // Live batch settlements above this total wait for a second approver from the same business (§2, §4)
    'second_approval_cents' => (int) env('API_SECOND_APPROVAL_CENTS', 50_000_000),   // KES 500,000
];
