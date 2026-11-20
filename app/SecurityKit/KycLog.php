<?php

namespace App\SecurityKit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * One JSON object per line in storage/logs/kyc/attempts-YYYY-MM-DD.jsonl. Passwords and codes are never written.
 * Short-lived cache counters drive verdict(): is this network / account guessing?
 */
class KycLog
{
    public const FAIL = ['login.fail', 'register.fail', 'otp.fail', 'password.fail'];

    /** env() returns null once config is cached, so fall back to reading .env directly. */
    public static function setting(string $key, $default = null)
    {
        $v = env($key);
        if ($v !== null) {
            return $v;
        }
        static $file;
        if ($file === null) {
            try {
                $file = \Dotenv\Dotenv::parse((string) @file_get_contents(base_path('.env')));
            } catch (\Throwable $e) {
                $file = [];
            }
        }
        if (! array_key_exists($key, $file)) {
            return $default;
        }
        $v = $file[$key];

        return match (strtolower((string) $v)) { 'true' => true, 'false' => false, 'null', '' => $default, default => $v };
    }

    public static function path(?string $date = null): string
    {
        return storage_path('logs/kyc/attempts-'.($date ?? now()->format('Y-m-d')).'.jsonl');
    }

    public static function limits(): array
    {
        return [
            'ip_fail_per_hour' => (int) self::setting('SECURITY_IP_FAIL_PER_HOUR', 40),
            'ip_accounts_per_hour' => (int) self::setting('SECURITY_IP_ACCOUNTS_PER_HOUR', 6),
            'ip_signups_per_hour' => (int) self::setting('SECURITY_IP_SIGNUPS_PER_HOUR', 10),
            'account_fail_per_day' => (int) self::setting('SECURITY_ACCOUNT_FAIL_PER_DAY', 20),
        ];
    }

    public static function record(string $event, ?string $identity = null, array $extra = []): void
    {
        try {
            $r = request();
            $ip = $r?->ip();
            $identity = $identity ? mb_strtolower(trim($identity)) : null;
            $row = array_filter([
                'at' => now()->toIso8601String(),
                'event' => $event,
                'ip' => $ip,
                'user_id' => auth()->id(),
                'identity' => $identity,
                'ua' => $r ? mb_substr((string) $r->userAgent(), 0, 200) : null,
                'path' => $r?->path(),
            ] + $extra, fn ($v) => $v !== null && $v !== '' && $v !== []);
            File::ensureDirectoryExists(dirname(self::path()));
            File::append(self::path(), json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");

            if ($ip && in_array($event, self::FAIL, true)) {
                self::hit("ip:$ip:fail", 3600);
                if ($identity) {
                    $acc = self::accounts($ip);
                    $acc[sha1($identity)] = time();
                    Cache::put("kyc:ip:$ip:accounts", $acc, 3600);
                    self::hit('id:'.sha1($identity).':fail', 86400);
                }
            }
            if ($ip && $event === 'register.ok') {
                self::hit("ip:$ip:signup", 3600);
            }
        } catch (\Throwable $e) {
            Log::warning('KYC log write failed: '.$e->getMessage());
        }
    }

    /** @return array{allow: bool, reasons: list<string>} */
    public static function verdict(?string $identity = null, ?string $kind = null): array
    {
        $ip = request()?->ip();
        $trusted = array_filter(array_map('trim', explode(',', (string) self::setting('SECURITY_TRUSTED_IPS', ''))));
        if (! $ip || in_array($ip, $trusted, true)) {
            return ['allow' => true, 'reasons' => []];
        }
        $l = self::limits();
        $why = [];
        if (($n = self::hits("ip:$ip:fail", 3600)) >= $l['ip_fail_per_hour']) {
            $why[] = "$n failed attempts from this network in the last hour";
        }
        if (($n = count(self::accounts($ip))) >= $l['ip_accounts_per_hour']) {
            $why[] = "failed attempts on $n different accounts from this network in the last hour";
        }
        if ($kind === 'register' && ($n = self::hits("ip:$ip:signup", 3600)) >= $l['ip_signups_per_hour']) {
            $why[] = "$n sign-ups from this network in the last hour";
        }
        if ($identity && ($n = self::hits('id:'.sha1(mb_strtolower(trim($identity))).':fail', 86400)) >= $l['account_fail_per_day']) {
            $why[] = "$n failed attempts on this account today";
        }

        return ['allow' => $why === [], 'reasons' => $why];
    }

    public static function read(int $hours = 24): array
    {
        $since = now()->subHours($hours)->toIso8601String();
        $rows = [];
        for ($d = now()->subHours($hours)->startOfDay(); $d->lte(now()); $d->addDay()) {
            $f = self::path($d->format('Y-m-d'));
            if (! is_file($f)) {
                continue;
            }
            foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $row = json_decode($line, true);
                if (is_array($row) && ($row['at'] ?? '') >= $since) {
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }

    private static function accounts(string $ip): array
    {
        return array_filter((array) Cache::get("kyc:ip:$ip:accounts", []), fn ($t) => $t > time() - 3600);
    }

    private static function hit(string $key, int $window): void
    {
        $t = array_values(array_filter((array) Cache::get("kyc:$key", []), fn ($x) => $x > time() - $window));
        $t[] = time();
        Cache::put("kyc:$key", array_slice($t, -500), $window);
    }

    private static function hits(string $key, int $window): int
    {
        return count(array_filter((array) Cache::get("kyc:$key", []), fn ($x) => $x > time() - $window));
    }
}
