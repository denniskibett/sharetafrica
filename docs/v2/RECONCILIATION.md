# Reconciliation

Every line on a provider's statement must match a ledger entry, and every ledger entry for that rail must be on the
statement. `App\Recon\Reconciler` does the matching; `/ops/reconciliation` (permission `admin.reconciliation`) is the screen.

## Daily flow

1. `php artisan recon:run` (00:30 for yesterday; or "Run" on the screen for any day) imports the statement from the rail
   (`ProvidesStatements::statement($day)`) and matches each line. Re-running is safe: lines are stored once.
2. Each line ends up as one of:

| Status | Meaning | Action |
|---|---|---|
| matched | Statement and ledger agree | none |
| missed | The provider paid, we never booked it (callback never arrived) | **Book from statement**: applies the provider's result to the order, which moves on (and may send the next payout) |
| amount | Amounts differ | investigate; never auto-fixed |
| unknown | Money that belongs to no request of ours | **Park in suspense**: Dr rail / Cr 2900 (money in) or Dr 2910 / Cr rail (money out), until identified |
| fixed, parked | Resolved by a person | recorded with who and when |

   The summary also counts entries booked in the ledger but not on the statement (usually timing: they appear the next day).
3. **Prepare** (the maker): only with no open breaks. **Sign off** (the checker): a different person. Both re-import the
   statement first, so a payout triggered by a fix can't slip past.

If our record says a request failed but the provider says it was paid, "Book from statement" refuses and marks the line
for a person to decide.

## Rails

The simulator's statement reports what "really" happened (its outcome rules), whether or not Sharet heard about it, so
missed callbacks can be rehearsed. `SimulatorRail::addStatementLine()` adds unidentified money for tests. Cashia and
Daraja adapters implement `ProvidesStatements` from their statement or transactions API.

Tests: `tests/Feature/Recon/ReconciliationTest.php`.
