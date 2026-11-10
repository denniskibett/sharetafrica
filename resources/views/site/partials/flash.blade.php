{{-- Website forms (waiting lists, sandbox, trade, contact) come back here with a thank-you or the errors. Icons only. --}}
@php $flash = collect(['waitlist', 'merchant', 'trade', 'dev', 'contact', 'personal'])->first(fn ($k) => session($k.'_received')); @endphp
@if ($flash)
  <div class="flashbar" role="status"><div class="ok">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L19 8"/></svg>
    <span>{{ session($flash.'_message') }}</span>
  </div></div>
  <script>(window.sa=window.sa||[]).push(['conversion','lead'])</script>
@endif
@if ($errors->any())
  <div class="flashbar" role="alert"><div class="bad">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div></div>
@endif
