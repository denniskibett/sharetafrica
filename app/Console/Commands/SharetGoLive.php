<?php

namespace App\Console\Commands;

use App\Ledger\Ledger;
use App\Models\Integration;
use App\Models\User;
use App\Security\Lockdown;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * One command that says whether Sharet can take real customers' money, item by item:
 *   php artisan sharet:golive            (exit 1 while anything blocks)
 * Read-only: it changes nothing. Each FAIL says exactly what to do.
 */
class SharetGoLive extends Command
{
    protected $signature = 'sharet:golive {--tester=v2-tester@sharet.test : Test account that must be gone before launch}';

    protected $description = 'Check every go-live blocker (scheduler, mail, SMS, payouts, float, test switches, backups, DNS, lockdown)';

    protected array $rows = [];

    protected bool $blocked = false;

    public function handle(): int
    {
        // Scheduler
        $beat = self::heartbeat();
        $this->row('Scheduler cron', $beat && $beat->gt(now()->subMinutes(3)),
            $beat ? 'Last ran '.$beat->diffForHumans() : 'Never ran',
            'Add in CyberPanel (every minute): cd <site> && php artisan schedule:run >> /dev/null 2>&1');

        // App mode
        $this->row('Production mode', app()->isProduction() && ! config('app.debug'), 'APP_ENV='.app()->environment().', debug '.(config('app.debug') ? 'on' : 'off'), 'APP_ENV=production, APP_DEBUG=false');

        // Money rails: test switches off, payouts live
        $this->row('Simulator off', ! config('rails.simulator.allow_in_production'), config('rails.simulator.allow_in_production') ? 'RAIL_SIMULATOR_IN_PRODUCTION=true' : 'Off', 'Set RAIL_SIMULATOR_IN_PRODUCTION=false');
        $this->row('Collections for everyone', config('rails.collect') === 'daraja' && ! config('rails.collect_only_for'), 'RAIL_COLLECT='.(config('rails.collect') ?: 'unset').(config('rails.collect_only_for') ? ', only for '.implode(',', config('rails.collect_only_for')) : ''), 'RAIL_COLLECT=daraja and empty RAIL_COLLECT_ONLY_FOR');
        $this->row('Payouts live', config('rails.payout') === 'daraja', 'RAIL_PAYOUT='.(config('rails.payout') ?: 'unset'), 'After the B2C test: RAIL_PAYOUT=daraja');

        $mpesa = Integration::whereNull('company_id')->where('provider', 'mpesa')->first();
        $cred = $mpesa?->credentials ?? [];
        $this->row('M-Pesa B2C initiator', filled($cred['initiator_name'] ?? null) && filled($cred['security_credential'] ?? null), filled($cred['initiator_name'] ?? null) ? 'Initiator set' : 'Initiator or credential missing',
            'M-Pesa Org Portal: create API initiator with B2C rights; enter it in Integrations → M-Pesa · Live');

        try {
            $float = app(Ledger::class)->account(config('rails.accounts.daraja.payout', '1120'))->available();
        } catch (\Throwable) {
            $float = 0;
        }
        $this->row('Payout float (1120)', $float > 0, \App\Ledger\Money::format($float), 'Move money working → utility on the portal, then php artisan ledger:float 1110 1120 <amount>');

        // Messages
        $smtp = Integration::whereNull('company_id')->where('provider', 'smtp')->where('enabled', true)->first();
        $mailOk = ! in_array(config('mail.default'), ['log', 'array'], true) || filled($smtp?->credentials['host'] ?? null);
        $this->row('Email delivers', $mailOk, $smtp ? 'SMTP in Integrations ('.($smtp->last_test_ok ? 'last test OK' : 'not tested').')' : 'MAIL_MAILER='.config('mail.default'), 'Enter SMTP in Integrations → Email; then php artisan sharet:mail-test you@gmail.com (must land in the inbox)');
        $sms = Integration::whereNull('company_id')->where('provider', 'kenyasms')->first();
        $this->row('SMS', filled(config('sms.kenyasms.api_key')) || filled($sms?->credentials['api_key'] ?? null), filled(config('sms.kenyasms.api_key')) || $sms ? 'Key set' : 'No key', 'Enter the KenyaSMS key in Integrations → KenyaSMS and press Test');

        // FX (fiat only): today's Safaricom reference rates recorded
        $missing = collect(config('fx.pairs'))->reject(function ($pair) {
            try { app(\App\Fx\Rates::class)->reference($pair); return true; } catch (\App\Fx\FxException) { return false; }
        });
        $this->row("Today's FX rates", $missing->isEmpty(), $missing->isEmpty() ? 'All pairs recorded' : 'Missing: '.$missing->implode(', '), 'Each morning: php artisan fx:rate KES/UGX <Safaricom rate> (cross-border pauses without it)', false);

        // Safety
        $dir = rtrim(config('database.backup_path') ?: storage_path('app/backups'), '/');
        $latest = collect(glob($dir.'/*.gz') ?: [])->map(fn ($f) => filemtime($f))->max();
        $this->row('Nightly backup', $latest && $latest > now()->subHours(26)->timestamp, $latest ? 'Latest '.Carbon::createFromTimestamp($latest)->diffForHumans() : 'None found in '.$dir, 'Runs at 01:30 once the cron runs; also set CyberPanel remote backups off the server');
        $this->row('Lockdown break-glass IPs', (bool) config('security.lockdown_allow_ips'), config('security.lockdown_allow_ips') ? implode(', ', config('security.lockdown_allow_ips')) : 'None', 'LOCKDOWN_ALLOW_IPS=<office/your IP> so a super admin can reopen from the web', false);
        $this->row('Ledger open', ! Lockdown::ledgerFrozen(), Lockdown::ledgerFrozen() ? 'FROZEN' : 'Open', 'php artisan sharet:lockdown status');

        // Test data
        $tester = User::withTrashed()->where('email', $this->option('tester'))->first();
        $this->row('Test account removed', ! $tester || $tester->trashed(), $tester ? ($tester->trashed() ? 'Disabled' : 'Still active') : 'Not present', 'php artisan sharet:reset-test-data (dry run first)');

        // DNS
        $v2 = @dns_get_record('v2.sharet.africa', DNS_A) ?: [];
        $this->row('v2.sharet.africa DNS', (bool) $v2, $v2 ? $v2[0]['ip'] : 'No A record', 'Add the A record at Olitt → 185.113.249.146', false);

        $this->table(['', 'Check', 'Now', 'To fix'], $this->rows);
        $this->line($this->blocked ? '<error> NOT READY </error> Fix every FAIL above. WARN items should be done but don\'t block.' : '<info> READY </info> All blockers clear.');
        $this->warn('Not checked by this command: CBK authorisation or a licensed partner\'s written cover for holding customer money.');

        return $this->blocked ? self::FAILURE : self::SUCCESS;
    }

    protected function row(string $check, bool $ok, string $now, string $fix, bool $blocks = true): void
    {
        $this->blocked = $this->blocked || (! $ok && $blocks);
        $this->rows[] = [$ok ? '<info>PASS</info>' : ($blocks ? '<error>FAIL</error>' : '<comment>WARN</comment>'), $check, $now, $ok ? '' : $fix];
    }

    public static function heartbeatPath(): string
    {
        return storage_path('framework/schedule-heartbeat');
    }

    public static function heartbeat(): ?Carbon
    {
        $t = @file_get_contents(self::heartbeatPath());

        return $t ? Carbon::createFromTimestamp((int) $t) : null;
    }
}
