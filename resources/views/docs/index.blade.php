{{-- Public API docs (/docs, /docs/errors). The contract behind it: docs/v2/INTEGRATION.md. Same look as the website
     (site.css): contents on the left, one long page. Icons only. Keep in step with the API when it changes. --}}
@extends('site.layout')
@section('title', 'Sharet API docs · Collect, pay out and settle in Kenya')
@section('description', 'Sharet API v1: Sharet IDs, scoped keys, payments, payment requests, split settlement, batch settlements, connected accounts, signed webhooks, errors and the sandbox.')

@php
    $toc = ['quickstart' => 'Quickstart', 'ids' => 'Sharet IDs and @names', 'keys' => 'Keys, scopes and IPs', 'conventions' => 'Requests and retries',
        'quotes' => 'Quotes', 'payments' => 'Payments out', 'requests' => 'Payment requests', 'splits' => 'Split settlement', 'settlements' => 'Batch settlements',
        'connect' => 'Connected accounts', 'webhooks' => 'Webhooks', 'errors' => 'Errors', 'sandbox' => 'Sandbox', 'pricing' => 'Pricing', 'limits' => 'Limits'];
    $errors_ = [
        'invalid_api_key' => [401, 'The key is missing, wrong or switched off.'],
        'missing_scope' => [403, 'The key doesn\'t have the scope this call needs. Add it under Access in Developers › API keys.'],
        'ip_allow_list_required' => [403, 'A live key needs its server addresses listed before it can pay out.'],
        'ip_not_allowed' => [403, 'This live key pays out only from its listed addresses. The response says which IP we saw.'],
        'business_not_verified' => [403, 'Live money needs a verified business. Use a sk_test_ key meanwhile.'],
        'not_connected' => [403, 'The Sharet-Account you sent hasn\'t connected to your business, or has disconnected.'],
        'not_allowed_by_account' => [403, 'The connected person didn\'t allow this (e.g. you asked to pay out, they allowed only collect).'],
        'not_an_approver' => [403, 'Batches must be approved by a different person from the same business.'],
        'account_not_found' => [404, 'No Sharet account has that @name or Sharet ID.'],
        'payment_not_found' => [404, 'No payment with that id for this key\'s account and environment.'],
        'payment_request_not_found' => [404, 'No payment request with that id for this key\'s account and environment.'],
        'settlement_not_found' => [404, 'No settlement with that id for this key\'s account and environment.'],
        'webhook_not_found' => [404, 'No webhook endpoint with that id.'],
        'connection_not_found' => [404, 'No connection with that id.'],
        'idempotency_key_reused' => [409, 'That Idempotency-Key was used for a different request.'],
        'in_progress' => [409, 'The first request with this Idempotency-Key is still running. Try again shortly.'],
        'idempotency_key_required' => [428, 'Money calls need an Idempotency-Key header (8–80 characters).'],
        'invalid_amount' => [422, 'Amounts are strings in whole shillings, at least KES 10, e.g. "1500.00".'],
        'destination_required' => [422, 'Send "to" with a sharet_id, a handle, or a type.'],
        'invalid_destination' => [422, 'The phone, till, paybill or bank details aren\'t valid. "errors" says which field.'],
        'own_account' => [422, 'You can\'t pay or request from your own account.'],
        'payment_refused' => [422, 'The payment engine refused it: not enough money, over a limit, or payouts paused. "detail" explains.'],
        'invalid_items' => [422, 'Some batch items need fixing; nothing was paid. "errors" lists them by index.'],
        'invalid_splits' => [422, 'The split legs don\'t add up, repeat an account, or are out of range.'],
        'outside_split_not_available' => [422, 'Split legs go to Sharet accounts only; recipients can send outside themselves.'],
        'invalid_payer' => [422, 'The payer\'s phone isn\'t a valid Kenyan mobile number.'],
        'invalid_channels' => [422, 'channels must be "sharet", "mpesa" or both.'],
        'invalid_scopes' => [422, 'Connect links ask for "collect", "payout" or both.'],
        'invalid_return_url' => [422, 'return_url must be a public https:// address.'],
        'invalid_webhook_url' => [422, 'Webhook URLs must be public https:// addresses.'],
        'growth_no_site' => [403, 'Growth summary: this key\'s business has no website linked in the Sharet Growth hub.'],
        'growth_other_site' => [403, 'Growth summary: the site asked for belongs to another business.'],
        'invalid_date_range' => [422, 'Growth summary: from / to must be YYYY-MM-DD, from on or before to, at most 366 days apart.'],
        'growth_unavailable' => [503, 'Growth summary: website numbers are not available right now. Try again later.'],
    ];
    $c = fn ($s) => '<div class="code">'.$s.'</div>';
@endphp

@section('content')
<div class="ui web pg">
@include('site.partials.header')

<div class="wr phero"><span class="lbl">DEVELOPERS · API V1</span><h1 class="mega pg">Sharet API docs</h1>
<p class="lead">Collect from customers, pay anyone, split a payment and settle in batches, in Kenyan shillings, through one API. Every account has a fixed Sharet ID and every settlement is tied to it.</p>
<div class="row2" style="margin-top:24px;flex-wrap:wrap;gap:12px"><a class="wb" href="{{ route('developers') }}#apply">Request sandbox access</a><a class="wb o" href="{{ route('home') }}#pricing">Pricing</a></div></div>

<div class="wr sect" style="padding-top:32px"><div class="doc">
<nav class="toc" aria-label="Contents">@foreach ($toc as $id => $label)<a href="#{{ $id }}" class="{{ $loop->first ? 'on' : '' }}">{{ $label }}</a>@endforeach</nav>
<div>

<div class="tldr"><b>Base URL</b><p><code>https://sharet.africa/api/v1</code> · JSON in, JSON out · amounts are strings like <code>"1500.00"</code> · currency KES · fiat only.</p></div>

<h2 id="quickstart">Quickstart</h2>
<ol>
  <li>Create a <b>sandbox key</b> (<code>sk_test_…</code>) in Developers › API keys and give it the scopes you need.</li>
  <li>Check it works:</li>
</ol>
{!! $c('<span class="c"># who am I?</span>
curl https://sharet.africa/api/v1/accounts/me \
  -H <span class="s">"Authorization: Bearer sk_test_…"</span>') !!}
<ol start="3">
  <li>Pay someone in the sandbox (no real money moves):</li>
</ol>
{!! $c('curl https://sharet.africa/api/v1/payments \
  -H <span class="s">"Authorization: Bearer sk_test_…"</span> \
  -H <span class="s">"Idempotency-Key: order-1042"</span> \
  -H <span class="s">"Content-Type: application/json"</span> \
  -d <span class="s">\'{"amount": "1500.00", "to": {"type": "mpesa", "phone": "0722 000 904"}}\'</span>') !!}
<p>When you're ready, verify your business and create a <b>live key</b> (<code>sk_live_…</code>) with your servers' IP addresses.</p>

<h2 id="ids">Sharet IDs and @names</h2>
<p>Every person and business has a <b>Sharet ID</b>, like <code>SH-4K7Q2M</code>. It never changes and is never reused, and every settlement, webhook and statement is tied to it. An <b>@name</b> (like <code>@mwangihardware</code>) is the alias people see and type; you can send it, and we always resolve it to the Sharet ID and return that. Renaming an @name never redirects money.</p>
{!! $c('<span class="m">GET</span> /accounts/resolve?handle=@akinyi

{ <span class="k">"sharet_id"</span>: <span class="s">"SH-9TRX3A"</span>, <span class="k">"handle"</span>: <span class="s">"@akinyi"</span>, <span class="k">"name"</span>: <span class="s">"Akinyi W."</span>, <span class="k">"verified"</span>: true, <span class="k">"can_receive"</span>: true }') !!}
<p>Names come back shortened (<i>Akinyi W.</i>) so lookups can't be used to collect people's full names. <code>GET /accounts/me</code> returns your own Sharet ID, @name, verification level, balance and the key's scopes.</p>

<h2 id="keys">Keys, scopes and IPs</h2>
<p>Send your key as <code>Authorization: Bearer sk_…</code>. A business's keys act for <b>the business's own Sharet wallet</b>; we keep only a fingerprint, so copy a key when it's shown.</p>
<div class="scr"><table class="price tt1"><tr><th>Scope</th><th>Lets the key</th></tr>
<tr><td><code>read</code></td><td>Look things up (every key has it)</td></tr>
<tr><td><code>collect</code></td><td>Create and cancel payment requests</td></tr>
<tr><td><code>payout</code></td><td>Send payments and batches, retry splits</td></tr>
<tr><td><code>connect</code></td><td>Ask people to connect their accounts</td></tr></table></div>
<ul>
  <li><b>Sandbox and live are separate.</b> <code>sk_test_</code> keys never move real money; <code>sk_live_</code> keys need a verified business.</li>
  <li><b>Live payouts need an IP allow-list:</b> list your servers' addresses or ranges on the key. Calls from anywhere else get <code>ip_not_allowed</code>.</li>
  <li><b>Large batches need a second person:</b> live batches over KES 500,000 wait in Dashboard › API approvals for someone else from your business.</li>
</ul>

<h2 id="conventions">Requests and retries</h2>
<ul>
  <li><b>Idempotency:</b> every call that moves money (and creating a payment request) takes an <code>Idempotency-Key</code> header, 8–80 characters, one per payment. Retrying with the same key returns the first answer (with <code>Idempotent-Replay: true</code>) and never pays twice. Refusals aren't remembered, so you can fix the problem and retry with the same key.</li>
  <li><b>Amounts</b> are strings with two decimals and no separators, in whole shillings: <code>"35000.00"</code>.</li>
  <li><b>Errors</b> are <code>application/problem+json</code> with a stable <code>type</code>; see <a href="#errors">Errors</a>.</li>
  <li><b>Sandbox and live data are separate:</b> a key only sees objects from its own environment.</li>
  <li><b>Pauses:</b> if Sharet pauses payments for safety, money calls answer <code>423</code>; nothing moves and you can retry later.</li>
</ul>

<h2 id="quotes">Quotes</h2>
<p>See the exact fee and total before paying. Nothing moves.</p>
{!! $c('<span class="m">POST</span> /quotes
{ <span class="k">"amount"</span>: <span class="s">"5000.00"</span>, <span class="k">"to"</span>: { <span class="k">"type"</span>: <span class="s">"bank"</span>, <span class="k">"bank"</span>: <span class="s">"equity"</span>, <span class="k">"account"</span>: <span class="s">"0123456789"</span> } }

{ <span class="k">"amount"</span>: <span class="s">"5000.00"</span>, <span class="k">"fee"</span>: <span class="s">"50.00"</span>, <span class="k">"excise"</span>: <span class="s">"7.50"</span>, <span class="k">"rounding"</span>: <span class="s">"0.50"</span>, <span class="k">"total"</span>: <span class="s">"5058.00"</span>,
  <span class="k">"tariff"</span>: { <span class="k">"version"</span>: 1, <span class="k">"volume_discount_percent"</span>: 0, <span class="k">"month_to_date"</span>: <span class="s">"0.00"</span> } }') !!}
<p>Destinations (<code>to</code>): <code>{"sharet_id": "SH-…"}</code> or <code>{"handle": "@name"}</code> (free and instant), <code>{"type": "mpesa"|"airtel"|"tkash", "phone": "0722 000 904"}</code>, <code>{"type": "till", "till": "512334"}</code>, <code>{"type": "paybill", "paybill": "247247", "account": "…"}</code>, <code>{"type": "bank", "bank": "equity", "account": "…"}</code> (PesaLink; banks: equity, kcb, ncba, imbank, coop, absa, stanbic, family).</p>

<h2 id="payments">Payments out</h2>
<p><code>POST /payments</code> (scope <code>payout</code>, Idempotency-Key) pays from your account's wallet. The response includes the fee, total and status: <code>paid</code> straight away for Sharet accounts, <code>paying</code> while M-PESA or the bank works, then <code>paid</code>, <code>failed</code> or <code>refunded</code> (a failed payout's money comes back to your wallet). Follow it with <code>GET /payments/{id}</code> or a webhook.</p>
{!! $c('<span class="m">POST</span> /payments
{ <span class="k">"amount"</span>: <span class="s">"31500.00"</span>, <span class="k">"to"</span>: { <span class="k">"sharet_id"</span>: <span class="s">"SH-4K7Q2M"</span> }, <span class="k">"reference"</span>: <span class="s">"Rent Nov · 4B"</span> }

<span class="c"># 201</span>
{ <span class="k">"id"</span>: <span class="s">"pay_…"</span>, <span class="k">"status"</span>: <span class="s">"paid"</span>, <span class="k">"amount"</span>: <span class="s">"31500.00"</span>, <span class="k">"fee"</span>: <span class="s">"0.00"</span>, <span class="k">"total"</span>: <span class="s">"31500.00"</span>, <span class="k">"to"</span>: { <span class="k">"type"</span>: <span class="s">"sharet"</span>, <span class="k">"sharet_id"</span>: <span class="s">"SH-4K7Q2M"</span> } }') !!}

<h2 id="requests">Payment requests</h2>
<p><code>POST /payment-requests</code> (scope <code>collect</code>, Idempotency-Key) asks someone to pay you and returns a <b>pay link</b> (<code>pay_url</code>). They pay from their Sharet wallet (signed in, with their PIN, free) or by an M-PESA prompt to any phone. The money lands in your wallet. No call can take money from someone's wallet without them approving it.</p>
{!! $c('<span class="m">POST</span> /payment-requests
{ <span class="k">"amount"</span>: <span class="s">"35000.00"</span>, <span class="k">"reference"</span>: <span class="s">"Rent Nov 2026 · Unit 4B"</span>,
  <span class="k">"payer"</span>: { <span class="k">"handle"</span>: <span class="s">"@akinyi"</span> },            <span class="c">// optional: or {"sharet_id"} or {"phone"}</span>
  <span class="k">"channels"</span>: [<span class="s">"sharet"</span>, <span class="s">"mpesa"</span>],
  <span class="k">"expires_in_minutes"</span>: 10080,
  <span class="k">"metadata"</span>: { <span class="k">"lease"</span>: <span class="s">"L-2041"</span> } }

<span class="c"># 201</span>
{ <span class="k">"id"</span>: <span class="s">"pr_…"</span>, <span class="k">"status"</span>: <span class="s">"open"</span>, <span class="k">"pay_url"</span>: <span class="s">"https://sharet.africa/r/…"</span>, <span class="k">"expires_at"</span>: <span class="s">"…"</span> }') !!}
<p>If you name a payer, only that Sharet account can pay from a wallet. <code>GET /payment-requests/{id}</code> shows the status (<code>open</code>, <code>paid</code>, <code>expired</code>, <code>cancelled</code>); <code>POST /payment-requests/{id}/cancel</code> closes it.</p>

<h2 id="splits">Split settlement</h2>
<p>Add <code>splits</code> to a payment request and, once it's paid, every leg is paid from your wallet to the recipients' Sharet IDs <b>together or not at all</b>. Legs are percentages or fixed amounts in whole shillings; what isn't in a leg (your <code>"self"</code> share and rounding) stays with you.</p>
{!! $c('<span class="k">"splits"</span>: [
  { <span class="k">"to"</span>: { <span class="k">"sharet_id"</span>: <span class="s">"SH-4K7Q2M"</span> }, <span class="k">"percent"</span>: 90 },   <span class="c">// landlord</span>
  { <span class="k">"to"</span>: { <span class="k">"handle"</span>: <span class="s">"@kevin"</span> }, <span class="k">"percent"</span>: 8 },        <span class="c">// agent</span>
  { <span class="k">"to"</span>: <span class="s">"self"</span>, <span class="k">"percent"</span>: 2 }                         <span class="c">// you</span>
]') !!}
<p>Legs go to Sharet accounts only (up to 10, each account once). If a leg can't be paid, none are, the money stays in your wallet and <code>settlement.failed</code> says why; fix it and retry with <code>POST /payment-requests/{id}/settle</code>.</p>

<h2 id="settlements">Batch settlements</h2>
<p><code>POST /settlements</code> (scope <code>payout</code>, Idempotency-Key) pays up to 500 items in one call. Every item is checked first: if any is wrong, nothing is paid and <code>errors</code> lists them by index. Each item is then paid like <code>POST /payments</code>; a refused item is recorded as failed without stopping the others.</p>
{!! $c('<span class="m">POST</span> /settlements
{ <span class="k">"reference"</span>: <span class="s">"Landlords · Nov"</span>, <span class="k">"items"</span>: [
  { <span class="k">"amount"</span>: <span class="s">"31500.00"</span>, <span class="k">"to"</span>: { <span class="k">"sharet_id"</span>: <span class="s">"SH-4K7Q2M"</span> }, <span class="k">"reference"</span>: <span class="s">"4B"</span> },
  { <span class="k">"amount"</span>: <span class="s">"22000.00"</span>, <span class="k">"to"</span>: { <span class="k">"type"</span>: <span class="s">"bank"</span>, <span class="k">"bank"</span>: <span class="s">"kcb"</span>, <span class="k">"account"</span>: <span class="s">"1102345678"</span> } } ] }

<span class="c"># 201 completed / partially_failed / failed, or 202 awaiting_approval</span>') !!}
<p>Live batches over KES 500,000 return <code>202 awaiting_approval</code> and run once a different person from your business approves them in Dashboard › API approvals.</p>

<h2 id="connect">Connected accounts</h2>
<p>Platforms (like a property manager) can collect and settle <b>for</b> their users, in their users' own Sharet accounts, with their consent.</p>
<ol>
  <li><code>POST /connect/links</code> (scope <code>connect</code>) with <code>{"scopes": ["collect", "payout"], "reference": "landlord-17", "return_url": "https://…"}</code> returns a link, valid 7 days.</li>
  <li>The person opens it, signs in, reads exactly what you may do, and approves with their PIN (or declines). They come back to your <code>return_url</code> with <code>?connection=…&amp;status=active</code>.</li>
  <li>Act for them by adding <code>Sharet-Account: SH-…</code> to your calls, only within what they approved; their own limits apply. Sharet ↔ Sharet is free; sending outside Sharet is charged.</li>
  <li>List them with <code>GET /connect/accounts</code>; either side can disconnect at any time (<code>POST /connect/accounts/{id}/disconnect</code>, or Account › Connected apps).</li>
</ol>

<h2 id="webhooks">Webhooks</h2>
<p>Register an endpoint with <code>POST /webhooks</code> <code>{"url": "https://…", "events": [...]}</code>; the response includes a <code>whsec_…</code> secret, shown once. Events: <code>payment.paid</code>, <code>payment.failed</code>, <code>payment.refunded</code>, <code>payment_request.paid</code>, <code>payment_request.expired</code>, <code>settlement.completed</code>, <code>settlement.failed</code>, <code>settlement.awaiting_approval</code>, <code>account.connected</code>, <code>account.disconnected</code>.</p>
<p>Every delivery is signed. Check the signature before trusting it, ignore repeats by <code>id</code>, answer 2xx quickly, and confirm with a <code>GET</code> when it matters. We retry for 24 hours.</p>
{!! $c('<span class="c"># Header</span>
Sharet-Signature: t=1794060000,v1=5f2b…   <span class="c"># HMAC-SHA256 of "t.body" with your secret</span>

<span class="c"># PHP</span>
[$t, $v1] = [/* parse t= and v1= */];
$ok = hash_equals(hash_hmac(<span class="s">\'sha256\'</span>, $t.<span class="s">\'.\'</span>.$rawBody, $secret), $v1) && abs(time() - $t) &lt; 300;

<span class="c"># Node</span>
const ok = crypto.timingSafeEqual(Buffer.from(crypto.createHmac(<span class="s">\'sha256\'</span>, secret).update(t + <span class="s">\'.\'</span> + rawBody).digest(<span class="s">\'hex\'</span>)), Buffer.from(v1));') !!}

<h2 id="errors">Errors</h2>
<p>Errors are <code>application/problem+json</code>: <code>{"type": "https://sharet.africa/docs/errors#…", "title": "…", "status": 422, "detail": "…"}</code>, sometimes with <code>errors</code> by field. Use <code>type</code> in your code; show <code>detail</code> to people.</p>
<div class="scr"><table class="price tt1"><tr><th>type</th><th>Status</th><th>Meaning</th></tr>
@foreach ($errors_ as $type => [$status, $meaning])<tr id="{{ $type }}"><td><code>{{ $type }}</code></td><td>{{ $status }}</td><td>{{ $meaning }}</td></tr>@endforeach
</table></div>

<h2 id="sandbox">Sandbox</h2>
<ul>
  <li>Use <code>sk_test_</code> keys: same endpoints, simulated money, nothing touches real balances.</li>
  <li>The M-PESA number <code>0700 000 002</code> always fails; every other destination succeeds.</li>
  <li>Sandbox pay links show <i>Simulate paid</i> and <i>Simulate cancel</i> buttons.</li>
  <li>Webhooks fire in the sandbox too, to endpoints registered with a test key.</li>
</ul>

<h2 id="pricing">Pricing</h2>
<p>Sharet ↔ Sharet is always free, including splits and connected accounts. Outside payouts are priced from two tables: a fee by movement type and amount, then a discount by your outside payouts this month. Every quote and payment shows the fee, the tariff version and your discount before money moves. Sharet's own companies are priced one tier up.</p>

<h2 id="limits">Limits</h2>
<ul>
  <li>60 requests a minute per key to start; batches up to 500 items; splits up to 10 legs.</li>
  <li>Payment limits follow the paying account's verification: verified businesses have business limits; connected people keep their own.</li>
  <li>Kenyan shillings only for now; Tanzania is next after the Kenya pilot.</li>
</ul>
<p style="margin-top:32px">Questions? <a href="{{ route('contact') }}">Talk to us</a>, or <a href="{{ route('developers') }}#apply">request sandbox access</a>.</p>

</div></div></div>

@include('site.partials.footer')
</div>
@endsection
