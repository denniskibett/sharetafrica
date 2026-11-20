<?php

namespace App\Ledger;

use RuntimeException;

/**
 * Every refusal from the ledger, with a reason code callers can switch on:
 * invalid_amount, unbalanced, too_few_legs, unknown_account, inactive_account, currency_mismatch,
 * insufficient_funds, idempotency_conflict, already_reversed, hold_not_open, hold_exists.
 */
class LedgerException extends RuntimeException
{
    public string $reason = 'ledger_error';

    public static function make(string $reason, string $message): self
    {
        $e = new self($message);
        $e->reason = $reason;

        return $e;
    }
}
