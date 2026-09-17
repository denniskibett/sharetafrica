{{-- resources/views/home/pages/developers.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Developers — API spec, sandbox, and the Sharet ledger')
@section('description', 'REST API for the Sharet ledger. Idempotent writes, HMAC-signed webhooks, RFC 7807 errors. Base URL, auth model, rate limits, and a free sandbox for Kenya, Tanzania, and Uganda rails.')

@push('head')
<style>
  /* Developer page — inline additions for code blocks.
     Uses only existing tokens; no design system changes. */
  .code-block {
    background: var(--ink-000);
    border: 1px solid var(--rule-strong);
    border-radius: var(--radius);
    padding: var(--space-5);
    overflow-x: auto;
    font-family: var(--font-mono);
    font-size: var(--text-sm);
    line-height: 1.65;
    color: var(--paper-soft);
    margin: 0;
  }
  .code-block .k { color: var(--lime); }
  .code-block .s { color: #E2FF73; }
  .code-block .c { color: var(--fg-deep); }
  .code-block .n { color: #A5D6FF; }

  .spec-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-mono);
    font-size: var(--text-sm);
    border: 1px solid var(--rule);
    border-radius: var(--radius);
    overflow: hidden;
  }
  .spec-table th {
    text-align: left;
    padding: var(--space-3) var(--space-4);
    background: var(--ink);
    color: var(--fg-mute);
    font-weight: 500;
    font-size: var(--text-xs);
    letter-spacing: 0.12em;
    text-transform: uppercase;
    border-bottom: 1px solid var(--rule);
  }
  .spec-table td {
    padding: var(--space-3) var(--space-4);
    border-bottom: 1px solid var(--rule);
    color: var(--fg-soft);
    vertical-align: top;
  }
  .spec-table tr:last-child td { border-bottom: 0; }
  .spec-table td:first-child { color: var(--lime); white-space: nowrap; }

  .endpoint-row {
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: var(--space-4);
    align-items: baseline;
    padding: var(--space-4) 0;
    border-bottom: 1px solid var(--rule);
    font-family: var(--font-mono);
    font-size: var(--text-sm);
  }
  .endpoint-row:last-child { border-bottom: 0; }
  .endpoint-method { color: var(--lime); font-weight: 500; letter-spacing: 0.04em; }
  .endpoint-path { color: var(--paper); letter-spacing: 0.02em; }
  .endpoint-desc {
    color: var(--fg-mute);
    margin-top: var(--space-2);
    font-family: var(--font-body);
    font-size: var(--text-sm);
    letter-spacing: 0;
  }
</style>
@endpush

@section('content')

  {{-- Hero — with a live curl example --}}
  <section class="hero">
    <div class="container container--wide">
      <div class="hero-grid">
        <div class="hero-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>API v1 · Beta · Sandbox live</span>
          <h1 class="hero-headline">
            Build on the<br/>
            <span class="lime">Sharet ledger.</span><br/>
            Or license the stack.
          </h1>
          <p class="hero-sub">
            REST API. JSON in, JSON out. Idempotent writes. HMAC-signed webhooks. RFC 7807 errors. A free sandbox with simulated M-PESA, MTN, Airtel, Vodacom, and bank rails for Kenya, Tanzania, and Uganda.
          </p>
          <div class="hero-cta-row">
            <a class="btn btn--primary btn--lg" href="#sandbox">Request sandbox access
              <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a class="btn btn--ghost btn--lg" href="#endpoints">See the endpoints</a>
          </div>
          <div class="hero-meta">
            <span><strong>REST</strong> · JSON</span>
            <span aria-hidden="true">·</span>
            <span><strong>RFC 7807</strong> · errors</span>
            <span aria-hidden="true">·</span>
            <span><strong>HMAC-SHA256</strong> · webhooks</span>
          </div>
        </div>
        <div class="hero-media">
          <pre class="code-block" style="height: 100%; display: flex; flex-direction: column; justify-content: center; border-radius: var(--radius-lg);"><span class="c"># Check the API is live</span>
$ <span class="k">curl</span> https://api.sharet.africa/v1/health

<span class="c"># → 200 OK</span>
{
  <span class="s">"status"</span>: <span class="s">"ok"</span>,
  <span class="s">"version"</span>: <span class="s">"v1"</span>,
  <span class="s">"rails"</span>: [
    <span class="s">"mpesa_ke"</span>,
    <span class="s">"mtn_ug"</span>
  ],
  <span class="s">"ts"</span>: <span class="s">"2026-09-16T10:42:00Z"</span>
}</pre>
        </div>
      </div>
    </div>
  </section>

  {{-- Essentials --}}
  <section class="compact">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>The essentials</span>
          <h2>Everything you<br/>need to know in<br/>one table.</h2>
        </div>
        <p class="lede">
          The full contract. If it is not here, it is in the OpenAPI spec. If it is not in the OpenAPI spec, write to us and we will add it.
        </p>
      </div>

      <table class="spec-table">
        <tbody>
          <tr><td>Base URL</td><td>https://api.sharet.africa/v1</td></tr>
          <tr><td>Sandbox URL</td><td>https://sandbox.api.sharet.africa/v1</td></tr>
          <tr><td>Auth</td><td>Bearer JWT (short-lived, 15 min) + HMAC-SHA256 signed request body</td></tr>
          <tr><td>Idempotency</td><td>Required on all POST/PUT. Header: <code style="color:var(--paper)">Idempotency-Key</code>. 24-hour window.</td></tr>
          <tr><td>Content-Type</td><td><code style="color:var(--paper)">application/json</code></td></tr>
          <tr><td>Rate limits</td><td>100 req/s standard · 1,000 req/s on Tier 03. Headers: <code style="color:var(--paper)">X-RateLimit-Remaining</code>.</td></tr>
          <tr><td>Errors</td><td>RFC 7807 <code style="color:var(--paper)">problem+json</code></td></tr>
          <tr><td>Webhooks</td><td>HMAC-SHA256 signed · retried 24h with exponential backoff</td></tr>
          <tr><td>SLA</td><td>99.9% uptime target (contractual on Tier 03)</td></tr>
          <tr><td>Status page</td><td><a href="#" style="color:var(--lime)">status.sharet.africa</a></td></tr>
          <tr><td>OpenAPI spec</td><td><a href="#" style="color:var(--lime)">/openapi/v1.yaml</a></td></tr>
        </tbody>
      </table>
    </div>
  </section>

  {{-- A real transaction example --}}
  <section class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Worked example</span>
          <h2>Send KES 10,000<br/>to a Vodacom<br/>number in Tanzania.</h2>
        </div>
        <p class="lede">
          One call. Sharet detects the country, resolves the network, checks liquidity, and returns a transaction ID and fee. Here is exactly what the request and response look like.
        </p>
      </div>

      <div class="split">
        <div class="split-text">
          <span class="eyebrow eyebrow--on-tile"><span class="dot" aria-hidden="true"></span>Request</span>
          <pre class="code-block" style="background: var(--ink-000); border-color: var(--rule-strong); color: var(--paper-soft);"><span class="k">curl</span> -X POST https://api.sharet.africa/v1/transactions \
  -H <span class="s">"Authorization: Bearer $TOKEN"</span> \
  -H <span class="s">"Idempotency-Key: 7f3a-4c1b-8e2d"</span> \
  -H <span class="s">"Content-Type: application/json"</span> \
  -d <span class="s">'{
    "source": "wallet_abc",
    "destination": {
      "type": "phone",
      "value": "+255754000000",
      "country": "TZ"
    },
    "amount": {
      "value": "10000",
      "currency": "KES"
    }
  }'</span></pre>
        </div>
        <div class="split-text">
          <span class="eyebrow eyebrow--on-tile"><span class="dot" aria-hidden="true"></span>Response · 202 Accepted</span>
          <pre class="code-block" style="background: var(--ink-000); border-color: var(--rule-strong); color: var(--paper-soft);">{
  <span class="s">"id"</span>: <span class="s">"tx_8f3a2b9e"</span>,
  <span class="s">"status"</span>: <span class="s">"processing"</span>,
  <span class="s">"rail"</span>: <span class="s">"vodacom_tz"</span>,
  <span class="s">"country"</span>: <span class="s">"TZ"</span>,
  <span class="s">"amount"</span>: {
    <span class="s">"value"</span>: <span class="s">"10000"</span>,
    <span class="s">"currency"</span>: <span class="s">"KES"</span>
  },
  <span class="s">"fee"</span>: {
    <span class="s">"value"</span>: <span class="s">"320"</span>,
    <span class="s">"currency"</span>: <span class="s">"KES"</span>
  },
  <span class="s">"eta"</span>: <span class="s">"2026-09-16T10:45:00Z"</span>,
  <span class="s">"webhook"</span>: <span class="s">"transaction.settled"</span>
}</pre>
        </div>
      </div>

      <p class="mono" style="text-align: center; color: var(--fg-on-tile-mute); margin-top: var(--space-6);">
        Idempotent · retry-safe · webhook fires on settle with HMAC signature
      </p>
    </div>
  </section>

  {{-- Endpoints --}}
  <section id="endpoints">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Endpoints · v1</span>
          <h2>The full v1<br/>surface area.</h2>
        </div>
        <p class="lede">
          Nine endpoints. Everything you need to send money, query the ledger, monitor treasury, and receive webhooks. No hidden magic.
        </p>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">POST</div>
        <div>
          <div class="endpoint-path">/v1/transactions</div>
          <div class="endpoint-desc">Send money to any supported rail. Idempotent. Returns transaction ID, fee, and ETA.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">GET</div>
        <div>
          <div class="endpoint-path">/v1/transactions/:id</div>
          <div class="endpoint-desc">Retrieve a transaction by ID. Includes status, rail, fee, and settlement timestamp.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">GET</div>
        <div>
          <div class="endpoint-path">/v1/ledger</div>
          <div class="endpoint-desc">Query ledger entries. Filters: account, currency, rail, status, from, to. Paginated.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">POST</div>
        <div>
          <div class="endpoint-path">/v1/wallets</div>
          <div class="endpoint-desc">Create a Sharet wallet. Optional KYC pre-verification. Returns wallet ID and initial balance.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">GET</div>
        <div>
          <div class="endpoint-path">/v1/wallets/:id</div>
          <div class="endpoint-desc">Retrieve wallet balance, currency, and status.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">GET</div>
        <div>
          <div class="endpoint-path">/v1/treasury</div>
          <div class="endpoint-desc">Country-level liquidity pools: available balance, threshold, last reconciliation time.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">GET</div>
        <div>
          <div class="endpoint-path">/v1/rails</div>
          <div class="endpoint-desc">Current status of every rail: live, degraded, or down. Includes latency percentiles.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">POST</div>
        <div>
          <div class="endpoint-path">/v1/webhooks</div>
          <div class="endpoint-desc">Register a webhook URL. Choose events: transaction.settled, transaction.failed, treasury.low, rail.degraded.</div>
        </div>
      </div>

      <div class="endpoint-row">
        <div class="endpoint-method">POST</div>
        <div>
          <div class="endpoint-path">/v1/webhooks/test</div>
          <div class="endpoint-desc">Trigger a test webhook with a valid HMAC signature. For sandbox use.</div>
        </div>
      </div>

      <div class="center-row" style="margin-top: var(--space-7); justify-content: center;">
        <a class="btn btn--ghost btn--lg" href="#">Read the OpenAPI spec
          <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </div>
    </div>
  </section>

  {{-- Errors --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Errors</span>
          <h2>RFC 7807<br/>problem+json.</h2>
        </div>
        <p class="lede">
          Every error response follows the same shape. No HTML. No stack traces. No surprises. Parse <code style="color:var(--paper)">type</code> to branch, <code style="color:var(--paper)">detail</code> to show the user.
        </p>
      </div>

      <table class="spec-table" style="margin-bottom: var(--space-6);">
        <thead>
          <tr><th>Status</th><th>Meaning</th><th>Retry?</th></tr>
        </thead>
        <tbody>
          <tr><td>400</td><td>Malformed request body</td><td>No</td></tr>
          <tr><td>401</td><td>Missing or invalid token</td><td>No</td></tr>
          <tr><td>403</td><td>Insufficient permissions</td><td>No</td></tr>
          <tr><td>409</td><td>Idempotency conflict — key reused with different body</td><td>No</td></tr>
          <tr><td>422</td><td>Business rule violation (insufficient balance, over limit)</td><td>No</td></tr>
          <tr><td>429</td><td>Rate limit exceeded — see <code style="color:var(--paper)">Retry-After</code></td><td>Yes</td></tr>
          <tr><td>502</td><td>Upstream rail unavailable</td><td>Yes, with backoff</td></tr>
          <tr><td>503</td><td>Sharet temporarily unavailable</td><td>Yes, with backoff</td></tr>
        </tbody>
      </table>

      <pre class="code-block"><span class="c"># Error response shape</span>
{
  <span class="s">"type"</span>: <span class="s">"https://api.sharet.africa/errors/insufficient_funds"</span>,
  <span class="s">"title"</span>: <span class="s">"Insufficient funds"</span>,
  <span class="s">"status"</span>: <span class="n">422</span>,
  <span class="s">"detail"</span>: <span class="s">"Wallet balance is KES 5,000; requested transfer is KES 10,320."</span>,
  <span class="s">"instance"</span>: <span class="s">"/v1/transactions"</span>,
  <span class="s">"trace_id"</span>: <span class="s">"t_9c2e8f1a"</span>
}</pre>
    </div>
  </section>

  {{-- Sandbox — honest status --}}
  <section id="sandbox" class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Sandbox · 2026</span>
          <h2>Live for Kenya<br/>and Uganda.<br/>Tanzania Q4.</h2>
        </div>
        <p class="lede">
          The sandbox is real and running. It simulates M-PESA Kenya and MTN Uganda today. Vodacom Tanzania is in integration. Airtel and card simulation are planned for Q1 2027.
        </p>
      </div>

      <table class="spec-table" style="margin-bottom: var(--space-7);">
        <thead>
          <tr><th>Rail</th><th>Status</th><th>Notes</th></tr>
        </thead>
        <tbody>
          <tr><td>mpesa_ke</td><td style="color:var(--lime)">● Live</td><td>Full simulation: success, failure, delay</td></tr>
          <tr><td>mtn_ug</td><td style="color:var(--lime)">● Live</td><td>Full simulation</td></tr>
          <tr><td>vodacom_tz</td><td style="color:var(--lime-soft)">● In integration</td><td>Expected Q4 2026</td></tr>
          <tr><td>airtel_tz</td><td style="color:var(--fg-mute)">○ Planned</td><td>Q1 2027</td></tr>
          <tr><td>airtel_ug</td><td style="color:var(--fg-mute)">○ Planned</td><td>Q1 2027</td></tr>
          <tr><td>card</td><td style="color:var(--fg-mute)">○ Planned</td><td>Q1 2027</td></tr>
          <tr><td>bank_ke</td><td style="color:var(--fg-mute)">○ Planned</td><td>Q1 2027</td></tr>
        </tbody>
      </table>

      <div class="cap-bento">
        <article class="cap-card cap-card--wide">
          <span class="cap-num">Sandbox access</span>
          <h3>Free. No card.<br/>5-minute setup.</h3>
          <p>Request access and you will receive a sandbox token, a set of test phone numbers, and a Postman collection. Test success, failure, delay, and rail-degraded scenarios deterministically.</p>
          <span class="label">Deterministic test numbers · real webhooks · real idempotency</span>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">Test data</span>
          <h3>Documented<br/>scenarios.</h3>
          <p>Every test phone number is documented. <code style="color:var(--paper)">+254700000001</code> succeeds. <code style="color:var(--paper)">+254700000002</code> fails. <code style="color:var(--paper)">+254700000003</code> delays by 30 seconds.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">Webhooks</span>
          <h3>Real signatures,<br/>real retries.</h3>
          <p>Sandbox webhooks are signed exactly like production. Test your retry logic and signature verification before going live.</p>
        </article>

        <article class="cap-card cap-card--wide">
          <span class="cap-num">SDKs · 2027</span>
          <h3>Official SDKs<br/>coming Q1.</h3>
          <p>Python, Node, PHP, and Flutter SDKs are in development. Until then, the API is a plain REST interface that works from any HTTP client. Community SDKs welcome.</p>
          <span class="label">Python · Node · PHP · Flutter · Q1 2027</span>
        </article>
      </div>
    </div>
  </section>

  {{-- White-label / licensing --}}
  <section id="licensing">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">White-label &amp; licensing</span>
          <h2>Your brand.<br/>Sharet's stack.<br/>Licensed as IP.</h2>
        </div>
        <p class="lede">
          For banks, fintechs, SACCOs, and platforms that want their own branded wallet — the entire Sharet ledger, treasury, and adapter stack is licensable. You bring the customers and the brand. We bring the rails and the settlement.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--wide">
          <span class="cap-num">White-label wallet</span>
          <h3>Launch your own<br/>branded wallet.</h3>
          <p>A fully-branded wallet with your logo, your colour, and your domain. Your customers see your brand. Sharet runs the ledger, treasury, and rails underneath. Deployment options include Sharet-hosted, single-tenant, or self-hosted in your cloud.</p>
          <span class="label">Banks · fintechs · SACCOs · platforms · insurers</span>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">Direct ledger</span>
          <h3>Higher limits,<br/>dedicated quota.</h3>
          <p>Direct API access with reserved capacity, custom rate limits, and priority routing. Ideal for high-volume platforms.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">Treasury</span>
          <h3>Managed<br/>liquidity.</h3>
          <p>Sharet operates the country-level liquidity pools, reconciles them daily, and reports to you in real time. Or you operate your own pools and we reconcile across them.</p>
        </article>

        <article class="cap-card cap-card--wide">
          <span class="cap-num">Bespoke rails</span>
          <h3>New rails built<br/>for your corridor.</h3>
          <p>If your business needs a rail or corridor Sharet does not yet support, we build the adapter. You get first access. The adapter then becomes part of the Sharet network for future customers.</p>
          <span class="label">Custom corridors · bespoke rails · dedicated engineering</span>
        </article>
      </div>

      <p class="mono" style="text-align: center; color: var(--fg-mute); margin-top: var(--space-7);">
        Licensing models: revenue share · platform fee · bespoke commercial terms. Contact us to discuss.
      </p>
    </div>
  </section>

  {{-- Support --}}
  <section class="compact">
    <div class="container container--wide">
      <div class="press-strip">
        <div>
          <span class="label">Support by tier</span>
          <p class="mono" style="margin-top: 6px; color: var(--fg-soft);">Documented paths</p>
        </div>
        <div class="press-row" aria-label="Support tiers">
          <span>Community Slack</span>
          <span>Email · Tier 02</span>
          <span>Dedicated engineer · Tier 03</span>
          <span>99.9% SLA · Tier 03</span>
          <span>Status page</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Intake --}}
  <section id="intake">
      <div class="container container--narrow">
          <div class="section-head">
              <div>
                  <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Get started</span>
                  <h2>Request sandbox.<br/>Or license the stack.</h2>
              </div>
              <p class="lede">
                  Every request is read by a human on the engineering team. Sandbox access is granted within one working day. Licensing conversations are scheduled within a week.
              </p>
          </div>

          <form class="form-card" method="POST" action="{{ route('developers.apply') }}">
              @csrf

              <div class="form-row">
                  <div class="form-field">
                      <label for="d-name">Your name</label>
                      <input id="d-name" name="name" type="text" required
                            value="{{ old('name') }}" placeholder="Brian Otieno" />
                      @error('name')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
                  <div class="form-field">
                      <label for="d-company">Company / project</label>
                      <input id="d-company" name="company" type="text"
                            value="{{ old('company') }}" placeholder="Nairobi Fintech Ltd." />
                  </div>
              </div>

              <div class="form-row">
                  <div class="form-field">
                      <label for="d-email">Work email</label>
                      <input id="d-email" name="email" type="email" required
                            value="{{ old('email') }}" placeholder="brian @ nairobifintech.co.ke" />
                      @error('email')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
                  <div class="form-field">
                      <label for="d-interest">What are you interested in?</label>
                      <select id="d-interest" name="interest" required>
                          <option value="sandbox"         @selected(old('interest') === 'sandbox')>Sandbox access — I want to test the API</option>
                          <option value="production_keys" @selected(old('interest') === 'production_keys')>Production keys — I am ready to go live</option>
                          <option value="white_label"     @selected(old('interest') === 'white_label')>White-label WaaS — I want my own branded wallet</option>
                          <option value="licensing"       @selected(old('interest') === 'licensing')>Licensing — I want to discuss the full stack</option>
                          <option value="bespoke_rail"    @selected(old('interest') === 'bespoke_rail')>Bespoke rail — I need a corridor Sharet does not yet support</option>
                      </select>
                      @error('interest')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
              </div>

              <div class="form-row form-row--full">
                  <div class="form-field">
                      <label for="d-brief">Tell us what you are building</label>
                      <textarea id="d-brief" name="brief" required
                                placeholder="What are you building, which rails do you need, and what is the timeline?">{{ old('brief') }}</textarea>
                      @error('brief')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
              </div>

              <div class="form-actions">
                  <small>We reply within one working day. No newsletter signup. No third-party sharing.</small>
                  <button type="submit" class="btn btn--primary btn--lg">
                      Send request
                      <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
              </div>
          </form>
      </div>
  </section>

  {{-- Closing --}}
  <section class="closing-cta">
    <div class="container container--narrow">
      <span class="label" style="color: rgba(10,10,12,0.6);">Get started · 2026</span>
      <h2>Build on the<br/>ledger. Or make<br/>it your own.</h2>
      <p class="lede">
        A working API. A live sandbox. A contractual SLA on Tier 03. Whatever your team needs to ship payments across East Africa — it starts here.
      </p>
      <div class="cta-row">
        <a class="btn btn--dark btn--lg" href="#intake">Request access
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btn btn--ghost btn--lg" href="{{ route('contact') }}">Talk to the team</a>
      </div>
    </div>
  </section>

@endsection