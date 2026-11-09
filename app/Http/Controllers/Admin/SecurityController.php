<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\SecurityKit\KycLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Administration › Security: every domain and site on the VPS, from the defense agent's full report
 * (/home/sharet.africa/security/latest.json, rewritten every morning; agent + docs: ops/defense-agent in the
 * scharge repo). Each other site has its own isolated page at https://<site>/admin/security-report (security kit).
 * Tabs: Overview, Sites, Domains, What to fix, Server, Sign-in attempts (sharet.africa's own log).
 */
class SecurityController extends Controller
{
    public const SEV = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1, 'ok' => 0];

    public function index(Request $request)
    {
        $dir = (string) (KycLog::setting('SECURITY_REPORT_DIR') ?: '/home/sharet.africa/security');
        $report = $this->json("$dir/latest.json");
        $history = collect($this->json("$dir/history.json") ?? [])->take(-30)->values();

        if (! $report) {
            return view('admin.security.index', ['report' => null, 'dir' => $dir] + $this->attempts());
        }

        $findings = collect($report['findings'] ?? [])->values();
        $sites = $this->sites($report, $findings);
        $domains = collect(array_keys($report['domains'] ?? []))->merge(array_keys($report['sites'] ?? []))->unique()
            ->map(fn ($name) => $this->domainRow($name, $report, $findings))
            ->sortBy([fn ($a, $b) => self::SEV[$b['worst']] <=> self::SEV[$a['worst']], fn ($a, $b) => strcmp($a['name'], $b['name'])])->values();

        return view('admin.security.index', [
            'report' => $report, 'dir' => $dir, 'history' => $history, 'findings' => $findings,
            'sites' => $sites, 'domains' => $domains, 'server' => $report['server_checks'] ?? [],
            'files' => $report['files'] ?? [], 'sev' => self::SEV,
            'stale' => \Illuminate\Support\Carbon::parse($report['generated_at'])->lt(now()->subHours(30)),
        ] + $this->attempts());
    }

    /** One row per hosting account (/home/<account>): its live apps, worst severity, counts, link to its own page. */
    private function sites(array $report, Collection $findings): Collection
    {
        $byHome = [];
        foreach ($report['sites'] ?? [] as $name => $site) {
            if (! empty($site['mail_host'])) {
                continue;
            }
            $home = preg_match('#^/home/([^/]+)#', (string) ($site['docroot'] ?? ''), $m) ? $m[1] : $name;
            $byHome[$home][] = $name;
        }

        return collect($byHome)->map(function ($names, $home) use ($findings, $report) {
            $mine = $findings->filter(fn ($f) => in_array($f['scope'], $names, true) || str_starts_with((string) $f['scope'], "/home/$home/"));
            $apps = collect($report['laravel'] ?? [])->filter(fn ($a) => ! empty($a['live']) && str_starts_with($a['path'], "/home/$home/"));
            $primary = in_array($home, $names, true) ? $home : $names[0];

            return [
                'account' => $home,
                'primary' => $primary,
                'names' => $names,
                'worst' => $mine->sortByDesc(fn ($f) => self::SEV[$f['severity']])->first()['severity'] ?? 'ok',
                'counts' => $mine->countBy('severity')->all(),
                'laravel' => $apps->count(),
                'debug' => $apps->contains(fn ($a) => strtolower((string) ($a['app_debug'] ?? '')) === 'true'),
                'resolves' => ! empty($report['domains'][$primary]['a'] ?? null),
                'link' => "https://$primary/admin/security-report",
            ];
        })->sortBy([fn ($a, $b) => self::SEV[$b['worst']] <=> self::SEV[$a['worst']], fn ($a, $b) => strcmp($a['primary'], $b['primary'])])->values();
    }

    private function domainRow(string $name, array $report, Collection $findings): array
    {
        $site = $report['sites'][$name] ?? [];
        $dns = $report['domains'][$name] ?? [];
        $mine = $findings->where('scope', $name);

        return [
            'name' => $name,
            'worst' => $mine->sortByDesc(fn ($f) => self::SEV[$f['severity']])->first()['severity'] ?? 'ok',
            'count' => $mine->count(),
            'mail_host' => (bool) ($site['mail_host'] ?? str_starts_with($name, 'mail.')),
            'resolves' => array_key_exists('a', $dns) ? ! empty($dns['a']) : null,
            'tls_days' => $site['tls']['days_left'] ?? null,
            'tls_ok' => $site['tls']['verified'] ?? null,
            'issuer' => $site['tls']['issuer'] ?? null,
            'http' => $site['http']['status'] ?? null,
            'redirect' => $site['http']['http_redirect'] ?? null,
            'spf' => array_key_exists('spf', $dns) ? (bool) $dns['spf'] : null,
            'dkim' => $dns['dkim'] ?? null,
            'dmarc' => array_key_exists('dmarc', $dns) ? (bool) $dns['dmarc'] : null,
        ];
    }

    /** sharet.africa's own sign-in / sign-up / code attempts (security kit log). */
    private function attempts(): array
    {
        $rows = KycLog::read(24 * 7);
        $since = now()->subDay()->toIso8601String();
        $day = array_values(array_filter($rows, fn ($r) => $r['at'] >= $since));
        $ends = fn ($set, $s) => count(array_filter($set, fn ($r) => str_ends_with($r['event'], $s)));
        $byIp = [];
        foreach ($day as $r) {
            $ip = $r['ip'] ?? '?';
            $byIp[$ip] ??= ['ip' => $ip, 'fail' => 0, 'ok' => 0, 'blocked' => 0, 'accounts' => [], 'last' => ''];
            $kind = substr(strrchr($r['event'], '.') ?: '', 1);
            if (isset($byIp[$ip][$kind]) && is_int($byIp[$ip][$kind])) {
                $byIp[$ip][$kind]++;
            }
            if ($kind === 'fail') {
                $byIp[$ip]['accounts'][$r['identity'] ?? '?'] = true;
            }
            $byIp[$ip]['last'] = $r['at'];
        }
        $l = KycLog::limits();

        return [
            'kyc' => ['fail' => $ends($day, '.fail'), 'ok' => $ends($day, '.ok'), 'blocked' => $ends($day, '.blocked'),
                'signups' => count(array_filter($day, fn ($r) => $r['event'] === 'register.ok')), 'week_fail' => $ends($rows, '.fail')],
            'ips' => collect($byIp)->map(fn ($s) => ['accounts' => count($s['accounts']),
                'verdict' => $s['blocked'] || $s['fail'] >= $l['ip_fail_per_hour'] || count($s['accounts']) >= $l['ip_accounts_per_hour'] ? 'block'
                    : ($s['fail'] > max(5, $s['ok'] * 3) ? 'watch' : 'ok')] + $s)
                ->sortByDesc(fn ($s) => [$s['verdict'] === 'block', $s['fail']])->take(25)->values(),
            'recent' => array_slice(array_reverse(array_values(array_filter($rows, fn ($r) => ! str_ends_with($r['event'], '.ok')))), 0, 40),
            'limits' => $l,
        ];
    }

    private function json(string $path): ?array
    {
        return is_readable($path) ? (json_decode((string) file_get_contents($path), true) ?: null) : null;
    }
}
