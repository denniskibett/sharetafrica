<?php

namespace App\Integrations\Contracts;

/**
 * A rail that can give its statement for a day: the provider's own record of every movement, which reconciliation
 * matches against the ledger. Each line: ['line_ref', 'direction' (in|out), 'amount' (cents), 'party', 'receipt',
 * 'occurred_at' (DateTimeInterface), 'provider_reference' (the request it belongs to, if any), 'raw'].
 */
interface ProvidesStatements
{
    /** @return array<int, array> */
    public function statement(string $day): array;
}
