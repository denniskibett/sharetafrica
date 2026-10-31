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
        $expired = $orders->expireQuotes();
        $moved = $orders->resolveWaiting();
        $this->info("Expired {$expired} quote(s); {$moved} waiting order(s) moved on.");

        return self::SUCCESS;
    }
}
