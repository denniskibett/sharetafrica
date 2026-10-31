<?php

namespace App\Console\Commands;

use App\Recon\Reconciler;
use Illuminate\Console\Command;

/** Import a rail's statement for a day and match it to the ledger (daily at 00:30 for yesterday; also from the screen). */
class ReconRun extends Command
{
    protected $signature = 'recon:run {--rail= : default rail if omitted} {--day= : YYYY-MM-DD, default yesterday}';

    protected $description = "Reconcile a payment rail's statement against the ledger";

    public function handle(Reconciler $reconciler): int
    {
        $rail = $this->option('rail') ?: config('rails.default');
        $day = $this->option('day') ?: now()->subDay()->toDateString();
        $result = $reconciler->run($rail, $day)->present();
        $this->info("{$rail} {$day}: {$result['lines']} lines, {$result['matched']} matched, {$result['breaks']} open breaks, {$result['not_on_statement']} booked but not on the statement.");

        return self::SUCCESS;
    }
}
