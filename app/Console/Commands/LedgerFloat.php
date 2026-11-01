<?php

namespace App\Console\Commands;

use App\Ledger\Ledger;
use App\Ledger\LedgerException;
use App\Ledger\Money;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * php artisan ledger:float 1110 1120 5,000 --note="M-PESA portal: working to utility"
 * Records money moved between two of Sharet's own rail accounts, e.g. the paybill's working account (where collections
 * land) to its utility account (where payouts leave). Do the move at the provider first, then record it here, so the
 * books match. Only rail (asset) accounts; nothing else.
 */
class LedgerFloat extends Command
{
    protected $signature = 'ledger:float {from : account code money leaves} {to : account code money arrives} {amount : e.g. 5,000} {--note= : where it was done} {--by= : staff user id}';

    protected $description = 'Record a float move between two rail accounts (e.g. M-PESA working to utility)';

    public function handle(Ledger $ledger): int
    {
        $accounts = config('ledger.accounts');
        foreach (['from', 'to'] as $side) {
            if (($accounts[$this->argument($side)]['book'] ?? null) !== 'rail') {
                $this->error("{$this->argument($side)} is not a rail account (see config/ledger.php).");

                return self::FAILURE;
            }
        }
        try {
            $cents = Money::cents($this->argument('amount'));
            $journal = $ledger->post('float:'.Str::uuid(), 'float_move', [
                Ledger::debit($ledger->account($this->argument('to')), $cents),
                Ledger::credit($ledger->account($this->argument('from')), $cents),
            ], ['memo' => 'Float moved '.$this->argument('from').' → '.$this->argument('to').($this->option('note') ? ' · '.$this->option('note') : ''),
                'created_by' => $this->option('by')]);
        } catch (LedgerException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $this->info('Recorded KES '.Money::format($cents).' '.$this->argument('from').' → '.$this->argument('to').' (journal '.$journal->id.').');

        return self::SUCCESS;
    }
}
