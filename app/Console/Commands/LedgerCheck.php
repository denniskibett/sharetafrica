<?php

namespace App\Console\Commands;

use App\Ledger\Invariants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/** Run every ledger invariant. Exits 1 and logs a critical alert if any fails. Scheduled hourly (V2ServiceProvider). */
class LedgerCheck extends Command
{
    protected $signature = 'ledger:check';

    protected $description = 'Prove the ledger balances: journals, balances, holds, hash chain, safeguarding';

    public function handle(Invariants $invariants): int
    {
        $results = $invariants->run();
        $this->table(['Check', 'Status', 'Detail'], array_map(fn ($r) => [$r['check'], strtoupper($r['status']), $r['detail']], $results));
        Cache::put('ledger:last-check', ['at' => now()->toIso8601String(), 'results' => $results], now()->addDays(2));

        if (! Invariants::passed($results)) {
            Log::critical('Ledger check failed', ['results' => array_values(array_filter($results, fn ($r) => $r['status'] === 'fail'))]);
            $this->error('Ledger check FAILED. Payouts should be paused until this is explained.');

            return self::FAILURE;
        }
        $this->info('Ledger check passed.');

        return self::SUCCESS;
    }
}
