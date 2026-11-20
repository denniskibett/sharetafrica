# The ledger

`App\Ledger\Ledger` is the only way money moves in Sharet. Everything else (orders, rails, consoles) asks it.

## The rules it enforces

| Rule | Where it is enforced |
|---|---|
| Debits = credits in every journal, one currency, ≥ 2 lines, no zero lines | `Ledger::post()` before any write; `ledger:check` afterwards |
| Amounts are whole cents (integers), shown with 2 decimals | `Money::cents()` refuses floats and 3 decimals; posting refuses non-integers |
| The same idempotency key posts once; the same key with different lines is refused | unique key + payload hash; deterministic journal UUID from the key |
| Accounts holding real money or owing customers never go below zero (after holds) | `allow_negative = false` in `config/ledger.php`, checked under row locks |
| Journals and postings are never changed or deleted | database triggers (MySQL, SQLite, Postgres) + model guards. Fix with `reverse()` |
| Nothing slips in around the ledger | every journal carries the hash of the previous one; `ledger:check` recomputes the chain and every balance |
| Real money covers what customers are owed | `ledger:check` safeguarding check |
| No deadlocks between parallel postings | locks always in the order: chain → holds → accounts by id |

Proven by `tests/Feature/Ledger/LedgerTest.php` (SQLite and MySQL) and a 40-worker parallel stress test on MySQL:
30 workers spending 100.00 from a 1,000.00 wallet → exactly 10 succeed, wallet ends at 0.00; 20 workers posting the
same callback key → one journal, every worker gets the same id.

## Accounts

System accounts (`config/ledger.php` → `accounts`) are created by `LedgerAccountSeeder`. Per-owner accounts
(`templates`: 2100 wallet, 2110 reserved, 2200 ramp order, 2210 merchant settlement, 2410 business float) are created on
first use: `$ledger->account('2100', $user)`. Codes are permanent once live.

`balance` is the raw sum of postings (debit +, credit −). `natural()` gives the everyday sign (a wallet with money in it
is positive). `available()` = natural − held.

## Posting

```php
use App\Ledger\Ledger;
use App\Ledger\Money;

$ledger = app(Ledger::class);

// J1 · STK deposit confirmed (key = the M-PESA receipt, so a repeated callback posts once)
$c = Money::cents('1,000.00');
$ledger->post("stk:{$receipt}", 'deposit', [
    Ledger::debit($ledger->account('1110'), $c),
    Ledger::credit($ledger->account('2100', $amina), $c),
], ['memo' => 'M-PESA deposit', 'source' => $stkRequest]);

// J3 · withdrawal: hold first, capture on the rail's success, release on failure
$hold = $ledger->hold($ledger->account('2100', $amina), 32300, "withdrawal:{$id}", 'withdraw 300 + fee 20 + excise 3');
$ledger->capture($hold, "b2c:{$conversationId}", 'withdrawal', [
    Ledger::debit($ledger->account('2100', $amina), 32300),
    Ledger::credit($ledger->account('1120'), 30000),
    Ledger::credit($ledger->account('4100'), 2000),
    Ledger::credit($ledger->account('2300'), 300),
]);
$ledger->release($hold); // when the status check confirms the payout failed

// A mistake is never edited: it is reversed (once; calling again returns the same reversal)
$ledger->reverse($journal, 'sent to the wrong person', auth()->id());
```

Fees and excise: `Money::percent($cents, $basisPoints)` rounds half away from zero, once. M-PESA moves whole shillings:
`Money::wholeShillings($cents)` gives the rail amount and the remainder for 4900 Rounding.

Every refusal is a `LedgerException` with a `reason`: invalid_amount, unbalanced, too_few_legs, unknown_account,
inactive_account, currency_mismatch, insufficient_funds, idempotency_conflict, already_reversed, hold_not_open,
hold_exists, invalid_capture, immutable.

## The hourly check

`php artisan ledger:check` (scheduled hourly by `V2ServiceProvider`) checks: every journal balances, every journal has
2+ lines, all postings sum to zero, cached balances = postings, held = open holds, no forbidden negatives, hash chain
intact, safeguarding, ramp orders older than 30 minutes (warning). It exits 1 and logs a critical alert on any failure.
Payouts must pause until a failure is explained. The last result shows on the Ledger page.

## Staff screen

`/ledger` (permission `admin.reconciliation`): accounts with balances, the trial balance and the last check;
`/ledger/accounts/{id}`: one account's statement; journals open in the side panel. Read-only: nothing is posted from a
screen. Manual journals (with maker-checker) come later.

## Database notes

- MySQL with binary logging needs `log_bin_trust_function_creators=1` (or SUPER) to create the triggers.
- Back up with `mysqldump --triggers` (the switch script does).
- v1 never touches `ledger_*` tables, so switching to v1 for an audit leaves the books intact.
