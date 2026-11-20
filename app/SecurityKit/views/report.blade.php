{{-- Sharet security kit: this site's own security dashboard. Data: App\SecurityKit\SecurityReportController.
     Branded per site (SECURITY_BRAND_NAME / _COLOR / _LOGO, else app name, #E0362C and the site's logo file). --}}
@php
    $c = $brand['color'];
    $s = $report['summary'] ?? [];
    $findings = $report['findings'] ?? [];
    $sevColor = ['critical' => '#B3261E', 'high' => '#D9480F', 'medium' => '#B58100', 'low' => '#7a7f87', 'ok' => '#2B8A3E'];
    $toFix = ($s['critical'] ?? 0) + ($s['high'] ?? 0);
    $tabs = ['overview' => 'Overview', 'findings' => 'What to fix ('.count($findings).')', 'domains' => 'Domains', 'attempts' => 'Sign-in attempts'];
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Security · {{ $brand['name'] }}</title>
<style>
  :root { --brand: {{ $c }}; --ink:#17181c; --bg:#f5f5f7; --card:#fff; --line:#e7e7ea; --muted:#6b6f78; --text:#17181c; }
  @media (prefers-color-scheme: dark) { :root { --bg:#111215; --card:#1a1b1f; --line:#2a2c31; --muted:#a0a3ab; --text:#f2f2f4; } }
  * { box-sizing: border-box; }
  body { margin:0; background:var(--bg); color:var(--text); font:14px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif; }
  .top { background:var(--card); border-bottom:1px solid var(--line); }
  .top .bar { height:4px; background:var(--brand); }
  .wrap { max-width:1120px; margin:0 auto; padding:0 16px; }
  .head { display:flex; align-items:center; gap:14px; padding:18px 0 6px; flex-wrap:wrap; }
  .head img { height:38px; width:auto; max-width:140px; object-fit:contain; }
  .head h1 { margin:0; font-size:21px; }
  .head p { margin:2px 0 0; color:var(--muted); font-size:13px; }
  .head .back { margin-left:auto; color:var(--brand); font-weight:600; text-decoration:none; font-size:13px; }
  .tabs { display:flex; gap:4px; overflow-x:auto; padding:10px 0 0; }
  .tabs button { border:0; background:none; color:var(--muted); font:600 13px/1 inherit; padding:11px 14px; border-bottom:3px solid transparent; cursor:pointer; white-space:nowrap; }
  .tabs button[aria-selected=true] { color:var(--text); border-color:var(--brand); }
  section[role=tabpanel] { display:none; padding:18px 0 40px; }
  section[role=tabpanel].on { display:block; }
  .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:16px; }
  .card { background:var(--card); border:1px solid var(--line); border-radius:14px; padding:16px; }
  .card .n { font-size:26px; font-weight:700; margin-top:4px; }
  .card .h { color:var(--muted); font-size:12px; }
  h2 { font-size:15px; margin:20px 0 10px; }
  table { width:100%; border-collapse:collapse; background:var(--card); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
  th, td { text-align:left; padding:9px 10px; border-bottom:1px solid var(--line); vertical-align:top; }
  th { color:var(--muted); font-weight:600; font-size:12px; }
  .scroll { overflow-x:auto; }
  .pill { display:inline-block; padding:1px 8px; border-radius:10px; font-size:11px; font-weight:700; text-transform:uppercase; color:#fff; }
  .fix { color:var(--muted); font-size:12px; margin-top:3px; }
  .muted { color:var(--muted); }
  code { font-size:12px; }
  .bars { display:flex; align-items:flex-end; gap:3px; height:90px; }
  .bars div { flex:1; display:flex; flex-direction:column-reverse; }
  .filters { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px; }
  .filters button { border:1px solid var(--line); background:var(--card); color:var(--text); border-radius:999px; padding:5px 11px; font:600 12px inherit; cursor:pointer; }
  .filters button.on { background:var(--brand); border-color:var(--brand); color:#fff; }
  .ok { color:#2B8A3E; } .bad { color:#B3261E; font-weight:700; }
</style>
</head>
<body>
<div class="top"><div class="bar"></div><div class="wrap">
  <div class="head">
    @if ($brand['logo'])<img src="{{ $brand['logo'] }}" alt="">@endif
    <div><h1>{{ $brand['name'] }} · Security</h1>
      <p>@if ($report) Checked {{ \Illuminate\Support\Carbon::parse($report['generated_at'])->timezone('Africa/Nairobi')->format('D j M, H:i') }} (Nairobi) by the server's defense agent · @endif sign-in attempts are live</p></div>
    <a class="back" href="{{ url('/') }}">← Back to {{ $brand['name'] }}</a>
  </div>
  <div class="tabs" role="tablist">
    @foreach ($tabs as $k => $label)<button role="tab" data-tab="{{ $k }}" aria-selected="false">{{ $label }}</button>@endforeach
  </div>
</div></div>

<main class="wrap">
  {{-- Overview --}}
  <section role="tabpanel" id="tab-overview">
    <div class="grid">
      @foreach (['critical' => 'Fix today', 'high' => 'Fix this week', 'medium' => 'Plan it', 'low' => 'Nice to have'] as $k => $hint)
        <div class="card"><div class="h" style="text-transform:capitalize">{{ $k }}</div><div class="n" style="color:{{ $sevColor[$k] }}">{{ $s[$k] ?? 0 }}</div><div class="h">{{ $hint }}</div></div>
      @endforeach
      <div class="card"><div class="h">Failed sign-ins (24 h)</div><div class="n">{{ $kyc['fail'] }}</div><div class="h">{{ $kyc['blocked'] }} refused · {{ $kyc['signups'] }} new accounts</div></div>
    </div>
    @if (count($history) > 1)
      @php $max = max(1, max(array_map(fn ($h) => ($h['summary']['critical'] ?? 0) + ($h['summary']['high'] ?? 0) + ($h['summary']['medium'] ?? 0), $history))); @endphp
      <div class="card"><div class="h" style="margin-bottom:8px">Problems per day (critical, high, medium)</div>
        <div class="bars">@foreach ($history as $h)<div title="{{ substr($h['at'], 0, 10) }}">@foreach (['critical', 'high', 'medium'] as $k)<span style="display:block;height:{{ round(86 * ($h['summary'][$k] ?? 0) / $max) }}px;background:{{ $sevColor[$k] }}"></span>@endforeach</div>@endforeach</div>
      </div>
    @endif
    <h2>Fix first</h2>
    <div class="scroll"><table>
      @forelse (array_slice(array_values(array_filter($findings, fn ($f) => in_array($f['severity'], ['critical', 'high'], true))), 0, 8) as $f)
        <tr><td style="width:90px"><span class="pill" style="background:{{ $sevColor[$f['severity']] }}">{{ $f['severity'] }}</span></td><td>{{ $f['title'] }}@if (! empty($f['fix']))<div class="fix">Fix: {{ $f['fix'] }}</div>@endif</td></tr>
      @empty
        <tr><td>{{ $report ? 'Nothing critical or high. 🎉' : 'No server report for this site yet.' }}</td></tr>
      @endforelse
    </table></div>
  </section>

  {{-- Findings --}}
  <section role="tabpanel" id="tab-findings">
    <div class="filters" data-filter="sev">
      <button data-v="" class="on">All</button>
      @foreach (['critical', 'high', 'medium', 'low'] as $k)<button data-v="{{ $k }}">{{ ucfirst($k) }} ({{ $s[$k] ?? 0 }})</button>@endforeach
    </div>
    <div class="scroll"><table>
      @forelse ($findings as $f)
        <tr data-sev="{{ $f['severity'] }}"><td style="width:90px"><span class="pill" style="background:{{ $sevColor[$f['severity']] }}">{{ $f['severity'] }}</span></td>
          <td><b>{{ $f['scope'] }}</b> <span class="muted" style="font-size:11px;text-transform:uppercase">{{ $f['check'] }}</span><br>{{ $f['title'] }}
            @if (! empty($f['fix']))<div class="fix">Fix: {{ $f['fix'] }}</div>@endif</td></tr>
      @empty
        <tr><td>{{ $report ? 'Nothing to fix. 🎉' : 'No server report yet; the agent writes '.$file.' every morning.' }}</td></tr>
      @endforelse
    </table></div>
  </section>

  {{-- Domains --}}
  <section role="tabpanel" id="tab-domains">
    <div class="scroll"><table>
      <tr><th>Name</th><th>Certificate</th><th>HTTP</th><th>SPF</th><th>DKIM</th><th>DMARC</th></tr>
      @forelse ($report['domains'] ?? [] as $name => $d)
        @php $t = $report['sites'][$name]['tls'] ?? null; $hs = $report['sites'][$name]['http']['status'] ?? null; @endphp
        <tr>
          <td><b>{{ $name }}</b>@if (empty($d['a'])) <span class="muted">does not resolve</span>@endif</td>
          <td>@if ($t)<span class="{{ ($t['verified'] ?? false) && ($t['days_left'] ?? 0) >= 14 ? 'ok' : 'bad' }}">{{ ($t['verified'] ?? false) ? ($t['days_left'].' days') : (($t['days_left'] ?? 0) < 0 ? 'expired' : 'not trusted') }}</span> <span class="muted">{{ $t['issuer'] ?? '' }}</span>@else <span class="muted">—</span>@endif</td>
          <td>{{ $hs ?? '—' }}</td>
          <td>{!! array_key_exists('spf', $d) ? ($d['spf'] ? '<span class="ok">✓</span>' : '<span class="bad">✗</span>') : '<span class="muted">—</span>' !!}</td>
          <td>{!! isset($d['dkim']) ? ($d['dkim'] === 'ok' ? '<span class="ok">✓</span>' : '<span class="bad">'.e($d['dkim']).'</span>') : '<span class="muted">—</span>' !!}</td>
          <td>{!! array_key_exists('dmarc', $d) ? ($d['dmarc'] ? '<span class="ok">✓</span>' : '<span class="bad">✗</span>') : '<span class="muted">—</span>' !!}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="muted">No domain data yet.</td></tr>
      @endforelse
    </table></div>
  </section>

  {{-- Sign-in attempts --}}
  <section role="tabpanel" id="tab-attempts">
    <div class="grid">
      <div class="card"><div class="h">Failed (24 h)</div><div class="n">{{ $kyc['fail'] }}</div><div class="h">{{ $kyc['week_fail'] }} in 7 days</div></div>
      <div class="card"><div class="h">Succeeded</div><div class="n">{{ $kyc['ok'] }}</div><div class="h">sign-ins, codes, resets</div></div>
      <div class="card"><div class="h">New accounts</div><div class="n">{{ $kyc['signups'] }}</div><div class="h">sign-ups</div></div>
      <div class="card"><div class="h">Refused</div><div class="n" style="color:{{ $kyc['blocked'] ? '#B3261E' : 'inherit' }}">{{ $kyc['blocked'] }}</div><div class="h">guessing detected</div></div>
    </div>
    <h2>Networks (24 h)</h2>
    <div class="scroll"><table>
      <tr><th>IP</th><th>Verdict</th><th>Failed</th><th>OK</th><th>Refused</th><th>Accounts tried</th><th>Last seen</th></tr>
      @forelse ($ips as $i)
        <tr><td><code>{{ $i['ip'] }}</code></td><td><span class="pill" style="background:{{ ['block' => '#B3261E', 'watch' => '#B58100', 'ok' => '#2B8A3E'][$i['verdict']] }}">{{ $i['verdict'] }}</span></td><td>{{ $i['fail'] }}</td><td>{{ $i['ok'] }}</td><td>{{ $i['blocked'] }}</td><td>{{ $i['accounts'] }}</td><td class="muted">{{ \Illuminate\Support\Carbon::parse($i['last'])->diffForHumans() }}</td></tr>
      @empty
        <tr><td colspan="7" class="muted">No sign-in attempts in the last 24 hours.</td></tr>
      @endforelse
    </table></div>
    @if (count($recent))
      <h2>Recent failed and refused attempts</h2>
      <div class="scroll"><table>
        <tr><th>When</th><th>What</th><th>Account</th><th>IP</th></tr>
        @foreach ($recent as $r)
          <tr><td class="muted">{{ \Illuminate\Support\Carbon::parse($r['at'])->timezone('Africa/Nairobi')->format('j M H:i') }}</td><td>{{ $r['event'] }}@if (! empty($r['reasons']))<div class="fix">{{ implode('; ', (array) $r['reasons']) }}</div>@endif</td><td>{{ $r['identity'] ?? '' }}</td><td><code>{{ $r['ip'] ?? '' }}</code></td></tr>
        @endforeach
      </table></div>
    @endif
    <p class="muted" style="margin-top:20px;font-size:12px">Log: <code>storage/logs/kyc/attempts-YYYY-MM-DD.jsonl</code>, one JSON object per line (passwords and codes are never written). A network is refused after {{ \App\SecurityKit\KycLog::limits()['ip_fail_per_hour'] }} failures in an hour or after trying {{ \App\SecurityKit\KycLog::limits()['ip_accounts_per_hour'] }} accounts; one account after {{ \App\SecurityKit\KycLog::limits()['account_fail_per_day'] }} failures in a day.</p>
  </section>
</main>

<script>
  (function () {
    var tabs = document.querySelectorAll('[role=tab]');
    function show(name) {
      if (!document.getElementById('tab-' + name)) name = 'overview';
      tabs.forEach(function (t) { t.setAttribute('aria-selected', t.dataset.tab === name ? 'true' : 'false'); });
      document.querySelectorAll('[role=tabpanel]').forEach(function (p) { p.classList.toggle('on', p.id === 'tab-' + name); });
      if (location.hash !== '#' + name) history.replaceState(null, '', '#' + name);
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { show(t.dataset.tab); }); });
    show(location.hash.slice(1) || 'overview');
    document.querySelectorAll('[data-filter=sev] button').forEach(function (b) {
      b.addEventListener('click', function () {
        b.parentNode.querySelectorAll('button').forEach(function (x) { x.classList.toggle('on', x === b); });
        document.querySelectorAll('tr[data-sev]').forEach(function (r) { r.style.display = !b.dataset.v || r.dataset.sev === b.dataset.v ? '' : 'none'; });
      });
    });
  })();
</script>
</body>
</html>
