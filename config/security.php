<?php

/*
| Access control for the whole system and the ledger (App\Security\Lockdown, App\Http\Middleware\SystemGuard).
| The .env switches below can only be turned off by editing .env on the server.
*/
return [

    // Where the two kill switches are kept (tests point this at a temp file)
    'lockdown_file' => env('LOCKDOWN_FILE'),

    'system_lockdown' => (bool) env('SYSTEM_LOCKDOWN', false),
    'ledger_freeze' => (bool) env('LEDGER_FREEZE', false),

    // Super admins on these IPs (comma separated) can still sign in during a system lockdown. Empty = none.
    'lockdown_allow_ips' => array_filter(array_map('trim', explode(',', (string) env('LOCKDOWN_ALLOW_IPS', '')))),

    // If set, staff money consoles (ledger, reconciliation) only answer from these IPs.
    'staff_allowed_ips' => array_filter(array_map('trim', explode(',', (string) env('STAFF_ALLOWED_IPS', '')))),

    // Staff must have re-entered their password within this many seconds before a money action.
    'staff_reconfirm_seconds' => (int) env('STAFF_RECONFIRM_SECONDS', 900),

    // Route name prefixes that move money (refused while the ledger is frozen, for any method but GET/HEAD).
    'money_routes' => ['pay.', 'wallet.', 'ops.reconciliation.', 'ledger.', 'api.money.'],

    // Route name prefixes for staff money consoles (allow-listed IPs + recent password).
    'staff_money_routes' => ['ops.reconciliation.', 'ops.ledger.', 'ledger.'],

    // Always reachable (rail callbacks must be received and stored; health check).
    'always_open_routes' => ['rails.'],
];
