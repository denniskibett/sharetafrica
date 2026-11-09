{{-- Administration › Security: every domain and site on the VPS. Data: Admin\SecurityController (defense agent report). --}}
@extends('layouts.app')
@section('title', 'Security')

@section('content')
@php
    $pill = [
        'critical' => 'bg-red-600 text-white', 'high' => 'bg-orange-500 text-white', 'medium' => 'bg-amber-400 text-gray-900',
        'low' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200', 'ok' => 'bg-emerald-600 text-white',
        'block' => 'bg-red-600 text-white', 'watch' => 'bg-amber-400 text-gray-900',
    ];
    $bar = ['critical' => '#DC2626', 'high' => '#F97316', 'medium' => '#FBBF24'];
    $th = 'px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400';
    $td = 'px-3 py-2.5 align-top';
    $tick = fn ($v) => $v === null ? '<span class="text-gray-400">—</span>' : ($v ? '<span class="text-emerald-600">✓</span>' : '<span class="font-semibold text-red-600">✗</span>');
    $s = $report['summary'] ?? [];
    $tabs = ['overview' => 'Overview', 'sites' => 'Sites', 'domains' => 'Domains', 'findings' => 'What to fix', 'server' => 'Server', 'attempts' => 'Sign-in attempts'];
@endphp
<div x-data="{ tab: (location.hash.slice(1) in {{ json_encode(array_fill_keys(array_keys($tabs), 1)) }}) ? location.hash.slice(1) : 'overview', sev: '', site: '', q: '' }"
     x-init="$watch('tab', t => history.replaceState(null, '', '#' + t))">
  <x-ui.page-header title="Security" subtitle="Every domain and site on the Sharet server, checked every morning by the defense agent: certificates, SPF / DKIM / DMARC, uptime, exposed files, Laravel settings, package vulnerabilities, SSH, firewall, mail and file changes. Each site also has its own page with only its checks." />

  @if (! $report)
    <x-ui.card>
      <p class="text-sm text-gray-700 dark:text-gray-300">No report yet. The agent writes <code>{{ $dir }}/latest.json</code> every morning at 06:00.</p>
    </x-ui.card>
  @else
  @if ($stale)
    <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">The last report is from {{ \Illuminate\Support\Carbon::parse($report['generated_at'])->diffForHumans() }}. Check the timer on the server: <code>systemctl status defense-agent.timer</code>.</div>
  @endif

  <div class="mb-6 flex gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-white/[0.03]" role="tablist">
    @foreach ($tabs as $key => $label)
      <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
              :class="tab === '{{ $key }}' ? 'bg-primary text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5'"
              class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium">{{ $label }}
        @if ($key === 'findings')<span class="ml-1 text-xs opacity-70">{{ count($findings) }}</span>@endif
        @if ($key === 'sites')<span class="ml-1 text-xs opacity-70">{{ count($sites) }}</span>@endif
      </button>
    @endforeach
  </div>

  {{-- ========== Overview ========== --}}
  <div x-show="tab === 'overview'" x-cloak>
    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
      @foreach (['critical' => 'Fix today', 'high' => 'Fix this week', 'medium' => 'Plan it', 'low' => 'Nice to have'] as $k => $hint)
        <button type="button" @click="tab = 'findings'; sev = '{{ $k }}'" class="ui-card rounded-2xl border border-gray-200 bg-white p-5 text-left hover:border-gray-400 dark:border-gray-800 dark:bg-white/[0.03]">
          <p class="text-sm capitalize text-gray-500 dark:text-gray-400">{{ $k }}</p>
          <p class="mt-2 text-2xl font-bold" style="color: {{ $bar[$k] ?? '#6B7280' }}">{{ $s[$k] ?? 0 }}</p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
        </button>
      @endforeach
      <div class="ui-card rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-sm text-gray-500 dark:text-gray-400">Sites</p>
        <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ count($sites) }}</p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ count($domains) }} names checked</p>
      </div>
      <div class="ui-card rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-sm text-gray-500 dark:text-gray-400">Last checked</p>
        <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ \Illuminate\Support\Carbon::parse($report['generated_at'])->timezone('Africa/Nairobi')->format('D H:i') }}</p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nairobi time · took {{ round($report['took_seconds'] ?? 0) }} s</p>
      </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
      <x-ui.card title="Trend" subtitle="Critical, high and medium problems per day">
        @if ($history->count() > 1)
          @php $max = max(1, $history->max(fn ($h) => ($h['summary']['critical'] ?? 0) + ($h['summary']['high'] ?? 0) + ($h['summary']['medium'] ?? 0))); @endphp
          <div class="flex h-40 items-end gap-1">
            @foreach ($history as $h)
              <div class="flex flex-1 flex-col-reverse" title="{{ substr($h['at'], 0, 10) }}: {{ $h['summary']['critical'] ?? 0 }} critical, {{ $h['summary']['high'] ?? 0 }} high, {{ $h['summary']['medium'] ?? 0 }} medium">
                @foreach (['critical', 'high', 'medium'] as $k)<div style="height: {{ round(150 * ($h['summary'][$k] ?? 0) / $max) }}px; background: {{ $bar[$k] }}"></div>@endforeach
              </div>
            @endforeach
          </div>
          <div class="mt-1 flex justify-between text-xs text-gray-400"><span>{{ substr($history->first()['at'], 0, 10) }}</span><span>{{ substr($history->last()['at'], 0, 10) }}</span></div>
        @else
          <p class="text-sm text-gray-500">The trend fills in after a few daily runs.</p>
        @endif
      </x-ui.card>

      <x-ui.card title="Fix first" subtitle="Critical and high, across every site">
        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($findings->filter(fn ($f) => in_array($f['severity'], ['critical', 'high'], true))->take(8) as $f)
            <li class="flex gap-3 py-2.5 text-sm">
              <span class="h-fit shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase {{ $pill[$f['severity']] }}">{{ $f['severity'] }}</span>
              <span class="min-w-0"><span class="font-medium text-gray-900 dark:text-white">{{ $f['scope'] }}</span><br><span class="text-gray-600 dark:text-gray-300">{{ $f['title'] }}</span></span>
            </li>
          @empty
            <li class="py-2 text-sm text-gray-500">Nothing critical or high. 🎉</li>
          @endforelse
        </ul>
        <button type="button" @click="tab = 'findings'" class="mt-3 text-sm font-medium text-gray-700 hover:underline dark:text-gray-300">See all {{ count($findings) }} →</button>
      </x-ui.card>
    </div>
  </div>

  {{-- ========== Sites ========== --}}
  <div x-show="tab === 'sites'" x-cloak>
    <x-ui.card title="Sites" subtitle="One row per hosting account. Each site's own page shows only its checks and its sign-in log." :padded="false">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Site</th><th class="{{ $th }}">Status</th><th class="{{ $th }}">Problems</th><th class="{{ $th }}">Apps</th><th class="{{ $th }}">Names</th><th class="{{ $th }}"></th></tr></thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @foreach ($sites as $r)
            <tr @class(['opacity-60' => ! $r['resolves']])>
              <td class="{{ $td }} font-medium text-gray-900 dark:text-white">{{ $r['primary'] }}@if (! $r['resolves'])<span class="ml-1 text-xs font-normal text-gray-500">does not resolve</span>@endif</td>
              <td class="{{ $td }}"><span class="rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase {{ $pill[$r['worst']] }}">{{ $r['worst'] }}</span></td>
              <td class="{{ $td }} text-xs text-gray-600 dark:text-gray-400">@foreach (['critical', 'high', 'medium', 'low'] as $k)@if ($r['counts'][$k] ?? 0)<span class="mr-2">{{ $r['counts'][$k] }} {{ $k }}</span>@endif @endforeach</td>
              <td class="{{ $td }} text-xs">{{ $r['laravel'] ?: '—' }}@if ($r['debug']) <span class="font-semibold text-red-600">debug on</span>@endif</td>
              <td class="{{ $td }} text-xs text-gray-500">{{ implode(', ', array_diff($r['names'], [$r['primary']])) ?: '—' }}</td>
              <td class="{{ $td }} whitespace-nowrap text-right">
                <button type="button" @click="tab = 'findings'; site = '{{ $r['account'] }}'" class="text-xs font-medium text-gray-700 hover:underline dark:text-gray-300">Problems</button>
                @if ($r['laravel'] && $r['resolves'])<a href="{{ $r['link'] }}" target="_blank" rel="noopener" class="ml-3 text-xs font-medium text-gray-700 hover:underline dark:text-gray-300">Its page ↗</a>@endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </x-ui.card>
  </div>

  {{-- ========== Domains ========== --}}
  <div x-show="tab === 'domains'" x-cloak>
    <x-ui.card title="Domains" subtitle="Certificate, HTTP, http→https and mail authentication for every name on the server" :padded="false">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Name</th><th class="{{ $th }}">Status</th><th class="{{ $th }}">Certificate</th><th class="{{ $th }}">HTTP</th><th class="{{ $th }}">→ https</th><th class="{{ $th }}">SPF</th><th class="{{ $th }}">DKIM</th><th class="{{ $th }}">DMARC</th></tr></thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @foreach ($domains as $r)
            <tr @class(['opacity-60' => $r['resolves'] === false])>
              <td class="{{ $td }} font-medium text-gray-900 dark:text-white">{{ $r['name'] }}
                @if ($r['resolves'] === false)<span class="ml-1 text-xs font-normal text-gray-500">does not resolve</span>@endif
                @if ($r['mail_host'])<span class="ml-1 text-xs font-normal text-gray-500">mail host</span>@endif</td>
              <td class="{{ $td }}"><span class="rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase {{ $pill[$r['worst']] }}">{{ $r['worst'] }}</span></td>
              <td class="{{ $td }} text-xs">
                @if ($r['tls_days'] !== null)
                  <span @class(['font-semibold text-red-600' => ! $r['tls_ok'] || $r['tls_days'] < 7, 'text-amber-600' => $r['tls_ok'] && $r['tls_days'] >= 7 && $r['tls_days'] < 21])>{{ $r['tls_ok'] ? $r['tls_days'].' days' : ($r['tls_days'] < 0 ? 'expired' : 'not trusted') }}</span>
                  <span class="text-gray-400">{{ $r['issuer'] }}</span>
                @else <span class="text-gray-400">—</span> @endif
              </td>
              <td class="{{ $td }} text-xs">{{ $r['http'] ?? '—' }}</td>
              <td class="{{ $td }}">{!! $tick($r['redirect']) !!}</td>
              <td class="{{ $td }}">{!! $tick($r['spf']) !!}</td>
              <td class="{{ $td }} text-xs">{!! $r['dkim'] === null ? '<span class="text-gray-400">—</span>' : ($r['dkim'] === 'ok' ? '<span class="text-emerald-600">✓</span>' : '<span class="font-semibold text-red-600">'.e($r['dkim']).'</span>') !!}</td>
              <td class="{{ $td }}">{!! $tick($r['dmarc']) !!}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </x-ui.card>
  </div>

  {{-- ========== What to fix ========== --}}
  <div x-show="tab === 'findings'" x-cloak>
    <div class="mb-4 flex flex-wrap items-center gap-2">
      @foreach (['' => 'All', 'critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $k => $label)
        <button type="button" @click="sev = '{{ $k }}'" :class="sev === '{{ $k }}' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'border border-gray-200 bg-white text-gray-700 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300'"
                class="rounded-full px-3 py-1.5 text-xs font-medium">{{ $label }}@if ($k) ({{ $s[$k] ?? 0 }})@endif</button>
      @endforeach
      <select x-model="site" class="h-9 rounded-lg border border-gray-200 bg-white px-3 text-sm dark:border-gray-800 dark:bg-gray-900">
        <option value="">Every site</option>
        @foreach ($sites as $r)<option value="{{ $r['account'] }}">{{ $r['primary'] }}</option>@endforeach
        <option value="server">Server</option>
      </select>
      <input x-model="q" type="search" placeholder="Search…" class="h-9 w-48 rounded-lg border border-gray-200 bg-white px-3 text-sm dark:border-gray-800 dark:bg-gray-900">
    </div>
    <x-ui.card :padded="false">
      <ul class="divide-y divide-gray-100 dark:divide-gray-800">
        @php
          $accountOf = [];
          foreach ($sites as $r) { foreach ($r['names'] as $n) { $accountOf[$n] = $r['account']; } }
        @endphp
        @foreach ($findings as $f)
          @php
            $acct = $accountOf[$f['scope']] ?? (preg_match('#^/home/([^/]+)#', (string) $f['scope'], $m) ? $m[1] : ($f['scope'] === 'server' ? 'server' : $f['scope']));
            $text = strtolower($f['scope'].' '.$f['check'].' '.$f['title'].' '.($f['fix'] ?? ''));
          @endphp
          <li class="flex gap-3 px-5 py-3 text-sm" x-show="(!sev || sev === '{{ $f['severity'] }}') && (!site || site === @js($acct)) && (!q || @js($text).includes(q.toLowerCase()))">
            <span class="h-fit w-20 shrink-0 rounded-full px-2 py-0.5 text-center text-[11px] font-semibold uppercase {{ $pill[$f['severity']] }}">{{ $f['severity'] }}</span>
            <div class="min-w-0">
              <p><span class="font-medium text-gray-900 dark:text-white">{{ $f['scope'] }}</span> <span class="text-[11px] uppercase text-gray-400">{{ $f['check'] }}</span></p>
              <p class="text-gray-700 dark:text-gray-300">{{ $f['title'] }}</p>
              @if (! empty($f['fix']))<p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Fix: {{ $f['fix'] }}</p>@endif
              @if (is_array($f['detail'] ?? null) && $f['detail'])
                <details class="mt-1 text-xs text-gray-500"><summary class="cursor-pointer">{{ count($f['detail']) }} files</summary><pre class="mt-1 whitespace-pre-wrap">{{ implode("\n", $f['detail']) }}</pre></details>
              @endif
            </div>
          </li>
        @endforeach
      </ul>
    </x-ui.card>
  </div>

  {{-- ========== Server ========== --}}
  <div x-show="tab === 'server'" x-cloak>
    @php
      $tiles = [
        ['Disk used', ($server['disk_used_pct'] ?? '?').'%', 'of the 98 GB disk'],
        ['Failed SSH logins', number_format($server['ssh_failed_24h'] ?? 0), 'last 24 hours'],
        ['IPs blocked', ($server['csf_permanent_blocks'] ?? 0).' / '.($server['csf_limit'] ?? '?'), 'CSF, + '.($server['csf_temp_blocks'] ?? 0).' for 24 h'],
        ['Mail', number_format($server['mail_sent_24h'] ?? 0).' sent', ($server['mail_bounced_24h'] ?? 0).' bounced, '.($server['mail_deferred_24h'] ?? 0).' deferred, '.($server['mail_queue'] ?? 0).' queued'],
        ['Updates', ($server['updates_security'] ?? 0).' security', ($server['updates_total'] ?? 0).' in all'.(($server['reboot_required'] ?? false) ? ' · reboot needed' : '')],
        ['Files watched', number_format($files['files'] ?? 0), ($files['changed'] ?? 0).' changed, '.($files['added'] ?? 0).' new · '.count($files['suspicious'] ?? []).' suspicious'],
      ];
    @endphp
    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
      @foreach ($tiles as [$label, $value, $hint])
        <div class="ui-card rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
          <p class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $value }}</p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
        </div>
      @endforeach
    </div>
    <div class="grid gap-6 xl:grid-cols-2">
      <x-ui.card title="Access">
        <dl class="grid grid-cols-[160px_minmax(0,1fr)] gap-y-2 text-sm">
          <dt class="text-gray-500">Listening ports</dt><dd>{{ implode(', ', $server['public_ports'] ?? []) }} <span class="text-xs text-gray-500">(CSF lets in only the usual web, mail and SSH ones)</span></dd>
          <dt class="text-gray-500">SSH passwords</dt><dd>{{ ($server['sshd']['passwordauthentication'] ?? '?') === 'yes' ? 'accepted (turn off)' : 'off' }}</dd>
          <dt class="text-gray-500">Root login</dt><dd>{{ $server['sshd']['permitrootlogin'] ?? '?' }}</dd>
          <dt class="text-gray-500">Root-level users</dt><dd>{{ implode(', ', $server['uid0_users'] ?? []) }}</dd>
          <dt class="text-gray-500">sudo users</dt><dd>{{ implode(', ', $server['sudo_users'] ?? []) ?: 'none' }}</dd>
          <dt class="text-gray-500">SSH keys</dt><dd>{{ count($server['ssh_keys'] ?? []) }}</dd>
          <dt class="text-gray-500">Automatic actions</dt><dd>{{ implode('; ', $report['defenses'] ?? []) ?: 'none this run' }}</dd>
        </dl>
      </x-ui.card>
      <x-ui.card title="Most failed SSH logins (24 h)" :padded="false">
        <table class="w-full text-left text-sm">
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($server['ssh_top_ips'] ?? [] as [$ip, $n])
            <tr><td class="{{ $td }}"><code>{{ $ip }}</code></td><td class="{{ $td }} text-right">{{ $n }}</td></tr>
          @empty
            <tr><td class="{{ $td }} text-gray-500">None.</td></tr>
          @endforelse
          </tbody>
        </table>
      </x-ui.card>
    </div>
  </div>
  @endif

  {{-- ========== Sign-in attempts (sharet.africa) ========== --}}
  <div x-show="tab === 'attempts'" x-cloak>
    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
      @foreach ([['Failed (24 h)', $kyc['fail'], $kyc['week_fail'].' in 7 days'], ['Succeeded', $kyc['ok'], 'sign-ins, codes, resets'], ['New accounts', $kyc['signups'], 'sign-ups'], ['Refused', $kyc['blocked'], 'guessing detected']] as [$label, $value, $hint])
        <div class="ui-card rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
          <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $value }}</p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
        </div>
      @endforeach
    </div>
    <x-ui.card title="Networks on sharet.africa (24 h)" subtitle="Every POST to sign-in, sign-up, password reset and one-time codes. Other sites show theirs on their own page." :padded="false">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">IP</th><th class="{{ $th }}">Verdict</th><th class="{{ $th }}">Failed</th><th class="{{ $th }}">OK</th><th class="{{ $th }}">Refused</th><th class="{{ $th }}">Accounts tried</th><th class="{{ $th }}">Last seen</th></tr></thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($ips as $i)
            <tr><td class="{{ $td }}"><code>{{ $i['ip'] }}</code></td><td class="{{ $td }}"><span class="rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase {{ $pill[$i['verdict']] }}">{{ $i['verdict'] }}</span></td><td class="{{ $td }}">{{ $i['fail'] }}</td><td class="{{ $td }}">{{ $i['ok'] }}</td><td class="{{ $td }}">{{ $i['blocked'] }}</td><td class="{{ $td }}">{{ $i['accounts'] }}</td><td class="{{ $td }} text-gray-500">{{ \Illuminate\Support\Carbon::parse($i['last'])->diffForHumans() }}</td></tr>
          @empty
            <tr><td colspan="7" class="{{ $td }} text-gray-500">No attempts in the last 24 hours.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </x-ui.card>
    @if (count($recent))
      <x-ui.card title="Recent failed and refused attempts" class="mt-6" :padded="false">
        <table class="w-full text-left text-sm">
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @foreach ($recent as $r)
            <tr><td class="{{ $td }} whitespace-nowrap text-gray-500">{{ \Illuminate\Support\Carbon::parse($r['at'])->timezone('Africa/Nairobi')->format('j M H:i') }}</td><td class="{{ $td }}">{{ $r['event'] }}@if (! empty($r['reasons']))<p class="text-xs text-gray-500">{{ implode('; ', (array) $r['reasons']) }}</p>@endif</td><td class="{{ $td }}">{{ $r['identity'] ?? '' }}</td><td class="{{ $td }}"><code>{{ $r['ip'] ?? '' }}</code></td></tr>
          @endforeach
          </tbody>
        </table>
      </x-ui.card>
    @endif
    <p class="mt-4 text-xs text-gray-500">Refused when a network has {{ $limits['ip_fail_per_hour'] }}+ failures in an hour or tries {{ $limits['ip_accounts_per_hour'] }}+ accounts, or one account has {{ $limits['account_fail_per_day'] }}+ failures in a day. Log: <code>storage/logs/kyc/</code>.</p>
  </div>
</div>
@endsection
