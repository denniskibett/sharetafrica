{{-- PDF of a website form (App\Support\FormIntake). dompdf: simple tables, inline CSS. Sharet ink + lime. --}}
@php($m = $e->meta ?? [])
@php($p = $e->user)
<!doctype html>
<html><head><meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111114; margin: 0; }
  .head { background: #111114; color: #fff; padding: 18px 24px; }
  .head .ref { float: right; background: #D4FF3D; color: #050507; padding: 4px 10px; border-radius: 10px; font-weight: bold; }
  h1 { font-size: 17px; margin: 8px 0 2px; } .sub { color: #C9C9C2; }
  .body { padding: 18px 24px; } h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .12em; color: #5A5A55; margin: 16px 0 6px; }
  table { width: 100%; border-collapse: collapse; } td { padding: 6px 4px; border-bottom: 1px solid #E5E5DE; text-align: left; vertical-align: top; }
  .k { width: 32%; color: #5A5A55; } .msg { white-space: pre-line; background: #F4F4F0; padding: 10px 12px; }
  .foot { color: #80807A; font-size: 9px; padding: 0 24px; }
</style></head><body>
<div class="head">
  <span class="ref">{{ $m['ref'] ?? '' }}</span>
  <div style="font-weight:bold">Sharet · {{ $m['form_name'] ?? 'Website form' }}</div>
  <h1>{{ $p?->name ?: ($p?->email ?: 'Website visitor') }}</h1>
  <div class="sub">Received {{ $e->created_at?->timezone('Africa/Nairobi')->format('D j M Y, H:i') }} EAT</div>
</div>
<div class="body">
  <h2>Who</h2>
  <table>
    <tr><td class="k">Name</td><td>{{ $p?->name ?: '-' }}</td></tr>
    <tr><td class="k">Email</td><td>{{ $p?->email ?: '-' }}</td></tr>
    <tr><td class="k">Phone</td><td>{{ $p?->phone ?: '-' }}</td></tr>
    @if ($e->business)<tr><td class="k">Business</td><td>{{ $e->business }}</td></tr>@endif
  </table>

  @if (! empty($m['rows']))
  <h2>What they sent</h2>
  <table>
    @foreach ($m['rows'] as $label => $value)
      <tr><td class="k">{{ $label }}</td><td>{{ is_array($value) ? implode(', ', $value) : $value }}</td></tr>
    @endforeach
  </table>
  @endif

  @if ($e->brief)<h2>Message</h2><div class="msg">{{ $e->brief }}</div>@endif
</div>
<p class="foot">Sent through the {{ strtolower($m['form_name'] ?? 'website') }} form on sharet.africa. Reference {{ $m['ref'] ?? '' }}.</p>
</body></html>
