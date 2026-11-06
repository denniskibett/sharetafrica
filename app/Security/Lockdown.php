<?php

namespace App\Security;

use Illuminate\Support\Facades\Log;

/**
 * The two kill switches.
 *
 *   system  everyone is shut out (pages, API, dashboard) except super admins on an allowed IP; money rails'
 *           callbacks are still received and kept, so no real payment is lost.
 *   ledger  nothing can move money: the ledger refuses every post, hold, capture and reversal (whatever calls it:
 *           web, API, queue, console), and money routes answer 423. Reading balances still works. Callbacks keep
 *           their payloads and are booked by `php artisan ramp:resolve` once the ledger reopens.
 *
 * State lives in a file on the private disk (not the cache, so clearing caches never lifts a lockdown) and can be
 * forced on with SYSTEM_LOCKDOWN=true / LEDGER_FREEZE=true in .env, which nothing in the app can switch off.
 * If the file can't be read, the ledger fails CLOSED.
 */
class Lockdown
{
    public const SWITCHES = ['system', 'ledger'];

    protected static ?array $state = null;

    public static function path(): string
    {
        $file = config('security.lockdown_file');

        return ! $file ? storage_path('app/private/lockdown.json') : (str_starts_with($file, '/') ? $file : base_path($file));
    }

    public static function state(): array
    {
        if (self::$state !== null) {
            return self::$state;
        }
        $state = ['system' => false, 'ledger' => false, 'unreadable' => false];
        $file = self::path();
        if (is_file($file)) {
            $json = json_decode((string) @file_get_contents($file), true);
            is_array($json) ? $state = array_merge($state, $json) : $state['unreadable'] = true;
        }

        return self::$state = $state;
    }

    public static function systemLocked(): bool
    {
        return (bool) config('security.system_lockdown') || (bool) (self::state()['system'] ?? false);
    }

    public static function ledgerFrozen(): bool
    {
        $s = self::state();

        return (bool) config('security.ledger_freeze') || self::systemLocked() || (bool) ($s['ledger'] ?? false) || ($s['unreadable'] ?? false);
    }

    public static function set(string $switch, bool $on, ?string $reason = null, ?string $by = null): array
    {
        abort_unless(in_array($switch, self::SWITCHES, true), 422, 'Unknown switch.');
        $state = self::state();
        unset($state['unreadable']);
        $state[$switch] = $on;
        $state["{$switch}_reason"] = $on ? ($reason ?: 'No reason given') : null;
        $state["{$switch}_by"] = $by;
        $state["{$switch}_at"] = now()->toIso8601String();

        @mkdir(dirname(self::path()), 0750, true);
        file_put_contents(self::path(), json_encode($state, JSON_PRETTY_PRINT), LOCK_EX);
        self::$state = null;

        self::log("{$switch} ".($on ? 'ON' : 'OFF'), ['reason' => $reason, 'by' => $by]);

        return self::state();
    }

    /** Called at the top of every ledger write. */
    public static function assertLedgerOpen(): void
    {
        if (self::ledgerFrozen()) {
            self::log('ledger write refused (frozen)');
            throw \App\Ledger\LedgerException::make('ledger_frozen', 'The ledger is frozen: no money can move until an administrator reopens it.');
        }
    }

    public static function log(string $what, array $context = []): void
    {
        Log::channel(config('logging.channels.security') ? 'security' : config('logging.default'))->warning('[security] '.$what, $context + [
            'ip' => request()?->ip(), 'path' => request()?->path(), 'user' => auth()->id(),
        ]);
    }

    /** Tests and long-running workers re-read the file. */
    public static function forget(): void
    {
        self::$state = null;
    }
}
