{{-- resources/views/home/pages/index.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sharet Africa — Cheaper payments across East Africa. Any rail. No signup required on the other side.')
@section('description', 'Send money to anyone in East Africa — M-PESA, MTN, Airtel, Vodacom, or bank — from one Sharet wallet. Internal transfers are free. External payments cost less. And the person you\'re paying never has to sign up for anything.')

@section('content')

  {{-- Hero --}}
  <section class="hero">
    <div class="container container--wide">
      <div class="hero-grid">
        <div class="hero-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>East Africa · Kenya · Tanzania · Uganda</span>
          <h1 class="hero-headline">
            Cheaper.<br/>
            <span class="lime">Any rail.</span><br/>
            No signup <span class="slash">/</span> on their side.
          </h1>
          <p class="hero-sub">
            Send money to anyone in East Africa — M-PESA, MTN, Airtel, Vodacom, or bank — from one Sharet wallet. Internal transfers are free. External payments cost less. And the person you are paying never has to sign up for anything.
          </p>
          <div class="hero-cta-row">
            <a class="btn btn--primary btn--lg" href="{{ route('personal') }}">Send money
              <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a class="btn btn--ghost btn--lg" href="{{ route('merchants') }}">Accept payments</a>
          </div>
          <div class="hero-meta">
            <span><strong>0%</strong> · internal transfer fee</span>
            <span aria-hidden="true">·</span>
            <span><strong>6</strong> · rails at launch</span>
            <span aria-hidden="true">·</span>
            <span><strong>3</strong> · East African markets</span>
          </div>
        </div>
        <div class="hero-media reveal">
          <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80" alt="A Sharet user in Nairobi sending money from their phone — the recipient receives it directly on their M-PESA, no Sharet account needed." />
          <div class="floating-tag ft-top">
            <span class="pill">Send · 0%</span>
            KES 10,000 → +255 Vodacom
          </div>
          <div class="floating-tag ft-bottom">
            THEY DON'T NEED SHARET
          </div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-cell">
          <div class="stat-num"><span class="lime">0</span>%</div>
          <div class="stat-label">Fee on Sharet-to-Sharet transfers</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">6<span class="lime">+</span></div>
          <div class="stat-label">Rails integrated at launch</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">60<span class="lime">%</span></div>
          <div class="stat-label">Cheaper than rail-to-rail hops</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">0</div>
          <div class="stat-label">Signups required on the other side</div>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 01: THE KILLER FEATURE — They don't need Sharet --}}
  <section class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">The idea · 01</span>
          <h2>Send to anyone.<br/>They don't need<br/>to be on Sharet.</h2>
        </div>
        <p class="lede">
          You have a Sharet wallet. They have M-PESA, MTN, Airtel, Vodacom, or a bank account. You enter their number or PayBill. Sharet identifies the country, network, and rail — and routes the payment. They get paid. They never had to download anything.
        </p>
      </div>

      <div class="split">
        <div class="split-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Worked example</span>
          <h2>Nairobi →<br/>Dar es Salaam.</h2>
          <p>
            You are in Nairobi. Your supplier is in Dar es Salaam. You have their +255 number. You enter it in Sharet, choose the amount, confirm. Sharet does the rest — country detection, network identification, treasury check, rail selection, payout execution. Your supplier receives TZS in their Vodacom wallet in minutes.
          </p>
          <ul class="split-fact-list">
            <li><b>You enter</b><span>+255 754 XXX XXX · KES 10,000</span></li>
            <li><b>Sharet detects</b><span>Tanzania · Vodacom M-Pesa</span></li>
            <li><b>Sharet checks</b><span>TZS liquidity · limits · rail availability</span></li>
            <li><b>Sharet routes</b><span>Through the Vodacom payout rail</span></li>
            <li><b>They receive</b><span>TZS in their Vodacom wallet — no signup, no app</span></li>
          </ul>
          <a class="btn btn--ghost-on-tile" href="{{ route('personal') }}">See how sending works
            <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>
        <div class="split-img">
          <img src="https://images.unsplash.com/photo-1556742111-a301076d9d18?auto=format&fit=crop&w=1000&q=80" alt="A Vodacom M-PESA agent in Dar es Salaam — the recipient receives the payout in their existing wallet without needing a Sharet account." />
          <span class="ft-corner">Recipient side · Vodacom M-Pesa</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 02: Three audiences --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Built for · 02</span>
          <h2>Three ways<br/>to use Sharet.</h2>
        </div>
        <p class="lede">
          Whether you accept payments, send money, or build on our infrastructure — Sharet is one wallet and one ledger behind the scenes. Pick the door that matches what you do.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--tall">
          <img src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=1000&q=80" alt="A merchant in Nairobi accepting a mobile money payment." />
          <div class="cap-tall-meta">
            <span class="cap-num lime-text">01 / Merchants</span>
            <h3>Accept from any rail.<br/>Settle anywhere.</h3>
            <p>Receive payments from M-PESA, MTN, Airtel, cards, and banks — into one wallet. Settle externally on demand. One dashboard for all rails.</p>
            <a class="btn btn--ghost-on-tile btn--sm" href="{{ route('merchants') }}" style="margin-top: 12px;">Merchants →</a>
          </div>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">02 / Personal</span>
          <h3>Send across<br/>East Africa.</h3>
          <p>Send to family, pay for goods, receive from abroad — from one wallet. Recipients don't need Sharet.</p>
          <a class="btn btn--ghost btn--sm" href="{{ route('personal') }}" style="margin-top: auto;">Personal →</a>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">03 / Trade</span>
          <h3>Goods in and<br/>out of Africa.</h3>
          <p>Pay suppliers abroad. Get paid from abroad. Settle locally in KES, TZS, or UGX.</p>
          <a class="btn btn--ghost btn--sm" href="{{ route('trade') }}" style="margin-top: auto;">Trade →</a>
        </article>

        <article class="cap-card cap-card--wide">
          <span class="cap-num">04 / Developers &amp; platforms</span>
          <h3>Build on the Sharet ledger —<br/>or license the whole stack.</h3>
          <p>Full API, SDKs, sandbox, webhooks, and a white-label option for banks, fintechs, and platforms that want to launch their own branded wallet on top of Sharet. The ledger, treasury, and adapter layer are all licensable as IP.</p>
          <a class="btn btn--ghost btn--sm" href="{{ route('developers') }}" style="margin-top: 12px;">Developers &amp; licensing →</a>
        </article>
      </div>
    </div>
  </section>

  {{-- Section 03: Why cheaper --}}
  <section class="chapter">
    <div class="container container--narrow">
      <div class="chapter-grid">
        <div>
          <span class="label">Why cheaper · 03</span>
          <h2>The old path<br/>took three hops.<br/>Sharet takes one.</h2>
        </div>
        <div class="chapter-body">
          <p>
            Moving money from one rail to another in East Africa traditionally takes multiple hops — withdraw, convert, send, deposit — with a fee at each hop. Sharet collapses that into one payment routed through one ledger. Because the ledger carries the internal movement for free, the only fee is the external rail at the destination.
          </p>
          <blockquote class="pull-quote">
            The ledger is the cheapest place for money to move. Everything else is a rail.
            <cite>— Sharet engineering principle</cite>
          </blockquote>
          <p>
            In practice, this makes cross-rail payments in East Africa cheaper by a wide margin — often 40 to 60 percent less than routing through traditional intermediary paths. The savings come from removing hops, not from squeezing margins.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 04: Trade corridor — goods in and out of Africa --}}
  <section class="snug">
    <div class="container container--wide">
      <div class="split split--reverse">
        <div class="split-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Trade · 04</span>
          <h2>Pay suppliers<br/>abroad. Get paid<br/>from abroad.</h2>
          <p>
            Sharet sits on both sides of the African trade corridor. Importers can pay suppliers in China, the UAE, Europe, or India — settling in USD, EUR, or GBP — while the recipient receives funds in whatever rail or currency they use locally. Exporters can receive payment from international buyers and settle directly into M-PESA, MTN, Airtel, or a local bank account.
          </p>
          <ul class="split-fact-list">
            <li><b>Imports</b><span>Pay suppliers abroad · settle in USD/EUR/GBP</span></li>
            <li><b>Exports</b><span>Receive from buyers abroad · settle in KES/TZS/UGX</span></li>
            <li><b>Intra-Africa</b><span>Kenya ↔ Tanzania ↔ Uganda · without multiple hops</span></li>
            <li><b>Diaspora</b><span>Send home · recipients get paid on their existing rail</span></li>
            <li><b>Compliance</b><span>Documentation, customs, and regulatory support built-in</span></li>
          </ul>
          <a class="btn btn--ghost" href="{{ route('trade') }}">See the trade corridor
            <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>
        <div class="split-img">
          <img src="https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=1000&q=80" alt="A container port in Mombasa — the trade corridor between Africa and the world." />
          <span class="ft-corner">Trade corridor · in and out of Africa</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 05: Payment rails grid --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Rails · 05</span>
          <h2>Six rails.<br/>One wallet.<br/>Zero friction.</h2>
        </div>
        <p class="lede">
          We did not try to integrate every network on day one. We picked the rails that matter most in each market, built deep integrations, and will add more as transaction volumes justify them.
        </p>
      </div>

      <div class="work-grid">
        <a class="work-item work-item--xl reveal" href="{{ route('developers') }}">
          <div class="wm">
            <img src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=1400&q=80" alt="A Safaricom M-PESA agent kiosk in Nairobi with a customer completing a deposit." />
            <span class="wm-pill">Kenya</span>
            <span class="wm-arrow" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 11L11 3M11 3H5M11 3V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
          </div>
          <div class="work-meta">
            <div>
              <div class="wm-title">M-PESA Kenya</div>
              <div class="wm-cap">Safaricom · Daraja API · KES</div>
            </div>
            <div class="wm-cap">Rail 01</div>
          </div>
        </a>

        <a class="work-item work-item--sm reveal" href="{{ route('developers') }}">
          <div class="wm">
            <img src="https://images.unsplash.com/photo-1556742111-a301076d9d18?auto=format&fit=crop&w=900&q=80" alt="A Vodacom M-Pesa agent point in Dar es Salaam, Tanzania." />
            <span class="wm-pill">Tanzania</span>
            <span class="wm-arrow" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 11L11 3M11 3H5M11 3V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
          </div>
          <div class="work-meta">
            <div>
              <div class="wm-title">Vodacom M-Pesa</div>
              <div class="wm-cap">Tanzania · TZS</div>
            </div>
            <div class="wm-cap">Rail 02</div>
          </div>
        </a>

        <a class="work-item work-item--md reveal" href="{{ route('developers') }}">
          <div class="wm">
            <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=1100&q=80" alt="An MTN Mobile Money kiosk in Kampala, Uganda." />
            <span class="wm-pill">Uganda</span>
            <span class="wm-arrow" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 11L11 3M11 3H5M11 3V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
          </div>
          <div class="work-meta">
            <div>
              <div class="wm-title">MTN MoMo</div>
              <div class="wm-cap">Uganda · UGX</div>
            </div>
            <div class="wm-cap">Rail 03</div>
          </div>
        </a>

        <a class="work-item work-item--md reveal" href="{{ route('developers') }}">
          <div class="wm">
            <img src="https://images.unsplash.com/photo-1601597111158-2fceff292cdc?auto=format&fit=crop&w=1100&q=80" alt="An Airtel Money agent in East Africa." />
            <span class="wm-pill">Tanzania · Uganda</span>
            <span class="wm-arrow" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 11L11 3M11 3H5M11 3V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
          </div>
          <div class="work-meta">
            <div>
              <div class="wm-title">Airtel Money</div>
              <div class="wm-cap">Secondary rail · TZ &amp; UG</div>
            </div>
            <div class="wm-cap">Rail 04</div>
          </div>
        </a>

        <a class="work-item work-item--sm reveal" href="{{ route('developers') }}">
          <div class="wm">
            <img src="https://images.unsplash.com/photo-1541354329998-f4d9a9f9297f?auto=format&fit=crop&w=900&q=80" alt="A bank branch in Nairobi — Sharet connects to KES, TZS, and UGX bank accounts." />
            <span class="wm-pill">Banking</span>
            <span class="wm-arrow" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 11L11 3M11 3H5M11 3V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
          </div>
          <div class="work-meta">
            <div>
              <div class="wm-title">Bank Accounts</div>
              <div class="wm-cap">KES · TZS · UGX · settlement</div>
            </div>
            <div class="wm-cap">Rail 05</div>
          </div>
        </a>

        <a class="work-item work-item--lg reveal" href="{{ route('developers') }}">
          <div class="wm">
            <img src="https://images.unsplash.com/photo-1556742393-d75f468bfcb0?auto=format&fit=crop&w=1200&q=80" alt="A point-of-sale card terminal at a Nairobi merchant." />
            <span class="wm-pill">Cards</span>
            <span class="wm-arrow" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 11L11 3M11 3H5M11 3V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
          </div>
          <div class="work-meta">
            <div>
              <div class="wm-title">Card Processing</div>
              <div class="wm-cap">Visa · Mastercard · merchant rails</div>
            </div>
            <div class="wm-cap">Rail 06</div>
          </div>
        </a>

        <a class="work-item work-item--sm reveal" href="{{ route('developers') }}">
          <div class="wm">
            <img src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=900&q=80" alt="A stylised map of Africa showing future Sharet rail integrations." />
            <span class="wm-pill">Phase 2+</span>
            <span class="wm-arrow" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 11L11 3M11 3H5M11 3V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
          </div>
          <div class="work-meta">
            <div>
              <div class="wm-title">More African rails</div>
              <div class="wm-cap">Nigeria · Ghana · Rwanda · expansion</div>
            </div>
            <div class="wm-cap">Rail 07+</div>
          </div>
        </a>
      </div>

      <div class="center-row" style="margin-top: var(--space-7); justify-content: center;">
        <a class="btn btn--ghost btn--lg" href="{{ route('developers') }}">See the full infrastructure
          <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </div>
    </div>
  </section>

  {{-- Section 06: Trust / compliance --}}
  <section class="compact">
    <div class="container container--wide">
      <div class="press-strip">
        <div>
          <span class="label">Compliance</span>
          <p class="mono" style="margin-top: 6px; color: var(--fg-soft);">Built for East African markets</p>
        </div>
        <div class="press-row" aria-label="Compliance standards">
          <span>KYC · AML</span>
          <span>CBK-aligned</span>
          <span>BOT-aligned</span>
          <span>BOU-aligned</span>
          <span>Safeguarded funds</span>
          <span>Data localisation</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 07: Journal --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Notes from the ledger</span>
          <h2>The journal.</h2>
        </div>
        <p class="lede">
          Short notes from the Sharet team — working notes on rail routing, liquidity, ledger design, trade corridors, and the markets we are watching closely.
        </p>
      </div>

      <div class="journal-grid">
        <article class="journal-card">
          <div class="j-img">
            <img src="https://images.unsplash.com/photo-1601597111158-2fceff292cdc?auto=format&fit=crop&w=900&q=80" alt="A stylised diagram of a Sharet internal transaction — two ledger entries, zero external rails." />
          </div>
          <div class="j-meta"><span>Ledger</span><span aria-hidden="true">·</span><span>04 Oct 2026</span></div>
          <h3>Why the recipient never needs an account.</h3>
          <p>How Sharet routes a payment to a phone number that isn't on our system — and why that is the whole point of the design.</p>
          <a class="btn btn--ghost btn--sm" href="#">Read note</a>
        </article>

        <article class="journal-card">
          <div class="j-img">
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=900&q=80" alt="A stylised view of country-level liquidity pools connected by one Sharet ledger." />
          </div>
          <div class="j-meta"><span>Treasury</span><span aria-hidden="true">·</span><span>22 Sep 2026</span></div>
          <h3>Liquidity pools, not cross-border hops.</h3>
          <p>Why Sharet maintains local settlement positions in each market instead of moving every customer's funds across borders per transaction.</p>
          <a class="btn btn--ghost btn--sm" href="#">Read note</a>
        </article>

        <article class="journal-card">
          <div class="j-img">
            <img src="https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=900&q=80" alt="A container port at dawn — the trade corridor between Africa and the world." />
          </div>
          <div class="j-meta"><span>Trade</span><span aria-hidden="true">·</span><span>09 Aug 2026</span></div>
          <h3>The Mombasa–Dubai corridor in numbers.</h3>
          <p>What we learned building settlement rails for importers paying suppliers abroad — and why the fee structure matters more than the FX rate.</p>
          <a class="btn btn--ghost btn--sm" href="#">Read note</a>
        </article>
      </div>
    </div>
  </section>

  {{-- Closing CTA --}}
  <section class="closing-cta">
    <div class="container container--narrow">
      <span class="label" style="color: rgba(10,10,12,0.6);">Get started · 2026</span>
      <h2>Payments across<br/>East Africa.<br/>Cheaper, on any rail.</h2>
      <p class="lede">
        Whether you accept payments, send money, or build on our infrastructure — we onboard merchants in a day, individuals in minutes, and platforms in a week. Send us a note and we will get you set up.
      </p>
      <div class="cta-row">
        <a class="btn btn--dark btn--lg" href="{{ route('contact') }}">Get started
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btn btn--ghost btn--lg" href="{{ route('developers') }}">See the infrastructure</a>
      </div>
    </div>
  </section>

@endsection