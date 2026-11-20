<?php

namespace App\Console\Commands;

use App\Security\Lockdown;
use Illuminate\Console\Command;

/**
 * The kill switches from the server, for when nobody can (or should) reach the dashboard:
 *   php artisan sharet:lockdown status
 *   php artisan sharet:lockdown ledger on --reason="Mismatch in recon"
 *   php artisan sharet:lockdown system on --reason="Suspected breach"
 *   php artisan sharet:lockdown system off
 */
class SharetLockdown extends Command
{
    protected $signature = 'sharet:lockdown {switch=status : system, ledger or status} {state? : on or off} {--reason= : Why (required to switch on)}';

    protected $description = 'Shut everyone out (system) or stop all money movement (ledger), or show where both stand';

    public function handle(): int
    {
        $switch = $this->argument('switch');
        if ($switch !== 'status') {
            if (! in_array($switch, Lockdown::SWITCHES, true) || ! in_array($this->argument('state'), ['on', 'off'], true)) {
                $this->error('Use: sharet:lockdown system|ledger on|off [--reason=...]');

                return self::INVALID;
            }
            $on = $this->argument('state') === 'on';
            if ($on && blank($this->option('reason'))) {
                $this->error('Give a --reason so the team knows why.');

                return self::INVALID;
            }
            Lockdown::set($switch, $on, $this->option('reason'), 'console:'.get_current_user());
        }

        $s = Lockdown::state();
        $this->table(['Switch', 'State', 'Reason', 'By', 'When'], [
            ['system', Lockdown::systemLocked() ? 'ON' : 'off', $s['system_reason'] ?? '', $s['system_by'] ?? '', $s['system_at'] ?? ''],
            ['ledger', Lockdown::ledgerFrozen() ? 'FROZEN' : 'open', $s['ledger_reason'] ?? '', $s['ledger_by'] ?? '', $s['ledger_at'] ?? ''],
        ]);
        config('security.system_lockdown') && $this->warn('SYSTEM_LOCKDOWN=true in .env: only editing .env lifts it.');
        config('security.ledger_freeze') && $this->warn('LEDGER_FREEZE=true in .env: only editing .env lifts it.');

        return self::SUCCESS;
    }
}
