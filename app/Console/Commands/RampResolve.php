<?php

namespace App\Console\Commands;

use App\Ramp\RampOrders;
use Illuminate\Console\Command;

/** Every minute: expire old quotes, and send one status check for each order that has been waiting too long. */
class RampResolve extends Command
{
    protected $signature = 'ramp:resolve';

    protected $description = 'Expire old quotes and check the status of waiting payments (never resends)';

    public function handle(RampOrders $orders): int
    {
        if (\App\Security\Lockdown::ledgerFrozen()) {
            $this->warn('Ledger frozen: nothing resolved. Waiting payments are kept and booked once it reopens.');

            return self::SUCCESS;
        }
        $expired = $orders->expireQuotes();
        $moved = $orders->resolveWaiting();
        $floated = $orders->sendAwaitingFloat();
        $this->info("Expired {$expired} quote(s); {$moved} waiting order(s) moved on; {$floated} payout(s) sent once float allowed.");

        return self::SUCCESS;
    }
}
