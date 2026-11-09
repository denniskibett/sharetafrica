<?php

namespace App\SecurityKit;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/** /admin/security-report: this site's findings (from the VPS defense agent) and its login / sign-up attempt log. */
class SecurityReportController extends Controller
{
    public const SEV = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1, 'ok' => 0];

    public function index(Request $request)
    {
        abort_unless(self::isAdmin($request->user()), 403);

        $report = null;
        $file = self::reportFile();
        if (is_readable($file)) {
            $report = json_decode((string) file_get_contents($file), true) ?: null;
        }
        $history = [];
        if (is_readable($h = dirname($file).'/site-history.json')) {
            $history = array_slice(json_decode((string) file_get_contents($h), true) ?: [], -30);
        }

        $rows = KycLog::read(24 * 7);
        $day = array_filter($rows, fn ($r) => $r['at'] >= now()->subDay()->toIso8601String());
        $count = fn ($set, $suffix) => count(array_filter($set, fn ($r) => str_ends_with($r['event'], $suffix)));
        $byIp = [];
        foreach ($day as $r) {
            $ip = $r['ip'] ?? '?';
            $byIp[$ip] ??= ['ip' => $ip, 'fail' => 0, 'ok' => 0, 'blocked' => 0, 'accounts' => [], 'last' => ''];
            $s = &$byIp[$ip];
            if (str_ends_with($r['event'], '.fail')) {
                $s['fail']++;
                $s['accounts'][$r['identity'] ?? '?'] = true;
            } elseif (str_ends_with($r['event'], '.ok')) {
                $s['ok']++;
            } elseif (str_ends_with($r['event'], '.blocked')) {
                $s['blocked']++;
            }
            $s['last'] = $r['at'];
            unset($s);
        }
        $limits = KycLog::limits();
        $ips = collect($byIp)->map(function ($s) use ($limits) {
            $s['accounts'] = count($s['accounts']);
            $s['verdict'] = $s['blocked'] || $s['fail'] >= $limits['ip_fail_per_hour'] || $s['accounts'] >= $limits['ip_accounts_per_hour']
                ? 'block' : ($s['fail'] > max(5, $s['ok'] * 3) ? 'watch' : 'ok');

            return $s;
        })->sortByDesc(fn ($s) => [$s['verdict'] === 'block', $s['fail']])->take(25)->values();

        return view('securitykit::report', [
            'brand' => self::brand(),
            'report' => $report, 'history' => $history, 'file' => $file,
            'kyc' => [
                'fail' => $count($day, '.fail'), 'ok' => $count($day, '.ok'), 'blocked' => $count($day, '.blocked'),
                'signups' => count(array_filter($day, fn ($r) => $r['event'] === 'register.ok')),
                'week_fail' => $count($rows, '.fail'),
            ],
            'ips' => $ips,
            'recent' => array_slice(array_reverse(array_values(array_filter($rows, fn ($r) => ! str_ends_with($r['event'], '.ok')))), 0, 40),
            'sev' => self::SEV,
        ]);
    }

    /** Name, colour and logo so each site's page looks like that site. Override with SECURITY_BRAND_*. */
    public static function brand(): array
    {
        $name = KycLog::setting('SECURITY_BRAND_NAME') ?: config('app.name');
        if (! $name || in_array(strtolower($name), ['laravel', 'app'], true)) {
            $name = parse_url((string) config('app.url'), PHP_URL_HOST) ?: request()->getHost();
        }
        $color = (string) (KycLog::setting('SECURITY_BRAND_COLOR') ?: '#E0362C');
        if (! preg_match('/^#[0-9a-f]{3,8}$/i', $color)) {
            $color = '#E0362C';
        }
        $logo = KycLog::setting('SECURITY_BRAND_LOGO');
        if (! $logo) {
            foreach (['logo.svg', 'logo.png', 'images/logo.svg', 'images/logo.png', 'img/logo.png', 'img/logo.svg', 'assets/logo.png',
                'assets/images/logo.png', 'assets/img/logo.png', 'favicon.svg', 'favicon.png', 'apple-touch-icon.png', 'favicon.ico'] as $f) {
                if (is_file(public_path($f))) {
                    $logo = asset($f);
                    break;
                }
            }
        }

        return ['name' => $name, 'color' => $color, 'logo' => $logo];
    }

    /** /home/<site>/security/site.json, written by the defense agent for this site only. */
    public static function reportFile(): string
    {
        if ($f = KycLog::setting('SECURITY_REPORT_FILE')) {
            return $f;
        }
        $parts = explode('/', base_path());

        return '/home/'.($parts[2] ?? '').'/security/site.json';
    }

    /**
     * Who may see this page: emails in SECURITY_ADMINS, or platform-level roles only (super-admin / sysadmin).
     * Plain "admin", "owner" or an app's own isAdmin() are NOT enough: in multi-tenant apps those are customers.
     */
    public static function isAdmin($user): bool
    {
        if (! $user) {
            return false;
        }
        $emails = array_filter(array_map(fn ($e) => strtolower(trim($e)), explode(',', (string) KycLog::setting('SECURITY_ADMINS', ''))));
        if ($emails && in_array(strtolower((string) $user->email), $emails, true)) {
            return true;
        }
        $roles = ['super-admin', 'super_admin', 'superadmin', 'Super Admin', 'Super-Admin', 'sysadmin', 'system-admin'];
        try {
            if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
                return true;
            }
        } catch (\Throwable $e) {
            // roles table missing for this guard
        }
        foreach (['isSuperAdmin', 'isSysadmin'] as $method) {
            if (method_exists($user, $method)) {
                try {
                    if ((new \ReflectionMethod($user, $method))->getNumberOfRequiredParameters() === 0 && $user->{$method}() === true) {
                        return true;
                    }
                } catch (\Throwable $e) {
                }
            }
        }
        $attrs = method_exists($user, 'getAttributes') ? $user->getAttributes() : [];
        if (! empty($attrs['is_super_admin']) && $attrs['is_super_admin'] !== '0') {
            return true;
        }
        foreach (['role', 'user_type', 'type', 'account_type'] as $col) {
            $v = $attrs[$col] ?? null;
            if (is_string($v) && in_array(strtolower($v), ['super-admin', 'super_admin', 'superadmin', 'sysadmin'], true)) {
                return true;
            }
        }

        return false;
    }
}
