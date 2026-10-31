<?php

namespace Database\Seeders;

use App\Ledger\Ledger;
use Illuminate\Database\Seeder;

/** Creates the system accounts in config/ledger.php. Safe to run again: existing accounts are left as they are. */
class LedgerAccountSeeder extends Seeder
{
    public function run(Ledger $ledger): void
    {
        foreach (array_keys(config('ledger.accounts')) as $code) {
            $ledger->account((string) $code);
        }
    }
}
