{{-- How to use a key: what it is for, how to send it, and the request/response shapes.
     Uses the key just generated (`plain`) when there is one, otherwise a placeholder. --}}
@php $base = url('/api/v1'); @endphp
<x-ui.card title="How to use your key" subtitle="Your systems (a shop, an app, a back office) use a key to call Sharet on behalf of your business. Calls carry your business name, not Sharet's, so you can build it into your own product." class="mb-6">
  <div x-data="{ lang: 'curl', get key() { return plain || 'sk_test_your_key_here' } }" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
    <ol class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
      <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-[#D4FF3D]">1</span>
        <span><strong>Start with a test key</strong> (<code>sk_test_…</code>). It runs against the sandbox, including your <em>M-Pesa · Sandbox</em> integration, and never moves real money.</span></li>
      <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-[#D4FF3D]">2</span>
        <span><strong>Keep it on your server.</strong> Put it in an environment variable. Never in a mobile app, a web page or a public repository.</span></li>
      <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-[#D4FF3D]">3</span>
        <span><strong>Send it on every request</strong> as <code>Authorization: Bearer &lt;key&gt;</code>, with <code>Accept: application/json</code>. Bodies are JSON.</span></li>
      <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-[#D4FF3D]">4</span>
        <span><strong>Check it works</strong> with <code>GET /me</code> (right). Then switch to a live key (<code>sk_live_…</code>) once your <em>M-Pesa · Live</em> integration is on.</span></li>
      <li class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-[#D4FF3D]">5</span>
        <span><strong>If a key leaks</strong>, rotate it from the ⋯ menu: the old one stops working immediately.</span></li>
    </ol>

    <div class="min-w-0 space-y-4">
      <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Request</p>
        <div class="inline-flex rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">
          @foreach (['curl' => 'cURL', 'php' => 'PHP', 'js' => 'JavaScript'] as $k => $label)
            <button type="button" @click="lang = '{{ $k }}'" :class="lang === '{{ $k }}' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500'" class="rounded-md px-3 py-1.5 text-xs font-medium">{{ $label }}</button>
          @endforeach
        </div>
      </div>
      <div class="relative">
        <pre class="overflow-x-auto rounded-xl bg-gray-950 p-4 text-xs leading-relaxed text-gray-100"><code x-show="lang === 'curl'" x-text="`curl {{ $base }}/me \\\n  -H 'Authorization: Bearer ${key}' \\\n  -H 'Accept: application/json'`"></code><code x-show="lang === 'php'" x-cloak x-text="`$response = Http::withToken(env('SHARET_API_KEY')) // ${key}\n    ->acceptJson()\n    ->get('{{ $base }}/me');\n\n$business = $response->json('company');`"></code><code x-show="lang === 'js'" x-cloak x-text="`const res = await fetch('{{ $base }}/me', {\n  headers: { Authorization: 'Bearer ' + process.env.SHARET_API_KEY, Accept: 'application/json' },\n}); // key: ${key}\nconst data = await res.json();`"></code></pre>
        <button type="button" @click="sharet.copy($el.previousElementSibling.querySelector('code:not([style*=none])').textContent)" class="absolute right-2 top-2 rounded-md bg-white/10 px-2 py-1 text-xs text-white hover:bg-white/20">Copy</button>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <p class="mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Response <span :class="sharet.pill('success')">200</span></p>
          <pre class="overflow-x-auto rounded-xl bg-gray-950 p-4 text-xs text-gray-100">{
  "key": "Shop checkout",
  "environment": "test",
  "company": "Your business name"
}</pre>
        </div>
        <div>
          <p class="mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Bad or disabled key <span :class="sharet.pill('error')">401</span></p>
          <pre class="overflow-x-auto rounded-xl bg-gray-950 p-4 text-xs text-gray-100">{
  "error": "invalid_api_key",
  "message": "Send a valid, enabled key…"
}</pre>
        </div>
      </div>
      <p class="text-xs text-gray-500 dark:text-gray-400">Base URL <code>{{ $base }}</code> · 60 requests a minute per key. Payments, payouts and webhooks are added to the <a href="{{ route('developers') }}" class="underline">developer docs</a> as they go live; the same key and headers work for them.</p>
    </div>
  </div>
</x-ui.card>
