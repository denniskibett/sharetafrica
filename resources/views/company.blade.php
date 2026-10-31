{{-- resources/views/home/pages/company.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Company — Real team, one ledger, one clear bet on Africa')
@section('description', 'Sharet was founded in 2026 to make payments across East Africa cheaper, simpler, and more accessible. Named founding team, regulatory posture, and why now is the right time.')

@section('content')

  {{-- Hero --}}
  <section class="hero">
    <div class="container container--wide">
      <div class="hero-grid">
        <div class="hero-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Company · est. 2026 · Nairobi</span>
          <h1 class="hero-headline">
            Named team.<br/>
            <span class="lime">One ledger.</span><br/>
            One clear bet.
          </h1>
          <p class="hero-sub">
            Sharet was founded in 2026 by four operators who have spent a combined 42 years building payments infrastructure in East Africa — at a mobile money operator, a regional bank, a cross-border fintech, and a payment switch. Our names are below. Our licence application is public. Our customer list is short on purpose.
          </p>
          <div class="hero-cta-row">
            <a class="btn btn--primary btn--lg" href="#team">Meet the team</a>
            <a class="btn btn--ghost btn--lg" href="#why-now">Why now</a>
          </div>
          <div class="hero-meta">
            <span><strong>4</strong> · named founders</span>
            <span aria-hidden="true">·</span>
            <span><strong>42yr</strong> · combined East African payments experience</span>
            <span aria-hidden="true">·</span>
            <span><strong>2026</strong> · founded</span>
          </div>
        </div>
        <div class="hero-media">
          <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1200&q=80" alt="The Sharet founding team in their Nairobi office." />
          <div class="floating-tag ft-top">
            <span class="pill">Nairobi</span>
            HEADQUARTERS
          </div>
          <div class="floating-tag ft-bottom">
            4 FOUNDERS · 42 YEARS COMBINED
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Why now — the Daraja shift --}}
  <section id="why-now" class="chapter tile-section">
    <div class="container container--narrow">
      <div class="chapter-grid">
        <div>
          <span class="label">Why now · the shift</span>
          <h2>Until 2021, this<br/>was not possible.<br/>Until 2024, it was<br/>not practical.</h2>
        </div>
        <div class="chapter-body">
          <p>
            The idea behind Sharet — one wallet that routes across every rail in East Africa — has been obvious for a decade. It has not existed because it was not technically possible until recently. Safaricom opened the M-PESA Daraja API to third parties in 2021. MTN Uganda opened its MoMo API in 2022. Airtel followed in 2023. Vodacom Tanzania opened its developer platform in 2024.
          </p>
          <blockquote class="pull-quote">
            For the first time, cross-rail routing is an engineering problem, not a bilateral-contract problem.
            <cite>— The Sharet thesis</cite>
          </blockquote>
          <p>
            Before these openings, any cross-rail product required bilateral commercial agreements with each telco, in each country, individually — a multi-year business development exercise that only a bank or a very large aggregator could undertake. Today, the same capability can be built by a small, focused engineering team using public APIs.
          </p>
          <p>
            Sharet is built on that shift. We are not the first company to notice it. We are one of the first to build the whole stack — wallet, ledger, treasury, and adapter layer — as one integrated system. That is the bet.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- Regulatory posture --}}
  <section>
    <div class="container container--narrow">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Regulatory posture</span>
          <h2>Licensed. Safeguarded.<br/>Reconciled daily.</h2>
        </div>
        <p class="lede">
          Payments is a regulated business. We do not pretend otherwise. Here is our regulatory posture in plain language.
        </p>
      </div>

      <ul class="split-fact-list">
        <li><b>Kenya</b><span>Application for a Payment Service Provider licence under the National Payment System Act submitted to the Central Bank of Kenya in Q1 2026. Operating under a partner PSP licence in the interim.</span></li>
        <li><b>Tanzania</b><span>Application for a Payment Service Provider licence with the Bank of Tanzania planned for Q1 2027, aligned to Vodacom and Airtel integration timelines.</span></li>
        <li><b>Uganda</b><span>Application for a Payment Service Provider licence with the Bank of Uganda planned for Q1 2027.</span></li>
        <li><b>Client funds</b><span>Held in segregated trust accounts with Equity Bank Kenya. Sharet does not lend, invest, or use customer funds for its own operations.</span></li>
        <li><b>Reconciliation</b><span>Every transaction reconciled daily against trust account balances. Reports available to regulators and auditors on request.</span></li>
        <li><b>Data</b><span>Customer data stored in-country per Kenyan Data Protection Act requirements. No cross-border data transfer without explicit consent.</span></li>
        <li><b>AML / KYC</b><span>Tiered KYC based on transaction size and destination. Full sanctions screening on all cross-border flows.</span></li>
      </ul>
    </div>
  </section>

  {{-- Founding chapter --}}
  <section class="chapter">
    <div class="container container--narrow">
      <div class="chapter-grid">
        <div>
          <span class="label">Chapter 01 · founding</span>
          <h2>Four founders.<br/>Four prior<br/>employers.<br/>One thesis.</h2>
        </div>
        <div class="chapter-body">
          <p>
            The four founders of Sharet met across a decade of working on payments in East Africa. Three of them had, at various points, tried to solve the same problem from the inside of larger institutions — a mobile money operator, a regional bank, a cross-border fintech — and watched the same constraints stop the same product every time. In 2026 they built the version that finally worked.
          </p>
          <p>
            Sharet is privately held. It has taken no outside investment. It intends to remain owner-operated for the foreseeable future. The team is small on purpose, and it will stay small on purpose until the stack is proven in the market.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- Metrics --}}
  <section class="compact">
    <div class="container container--wide">
      <div class="awards-strip">
        <div class="award-cell">
          <div class="award-num">2026</div>
          <div class="award-label">Founded in Nairobi</div>
        </div>
        <div class="award-cell">
          <div class="award-num">2</div>
          <div class="award-label">Rails live today: M-PESA Kenya, MTN Uganda</div>
        </div>
        <div class="award-cell">
          <div class="award-num">3</div>
          <div class="award-label">Markets in launch scope</div>
        </div>
        <div class="award-cell">
          <div class="award-num">0%</div>
          <div class="award-label">Internal transfer fee</div>
        </div>
      </div>
    </div>
  </section>

  {{-- Principles bento --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Principles</span>
          <h2>Six rules we<br/>refuse to bend.</h2>
        </div>
        <p class="lede">
          These are not aspirations — they are the constraints that shape every decision the Sharet team makes. We wrote them down on day one and have not relaxed since.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--std">
          <span class="cap-num">01</span>
          <h3>The ledger is<br/>the product.</h3>
          <p>Everything else — rails, wallets, dashboards, APIs — is a client of the ledger. The ledger is where money actually lives.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">02</span>
          <h3>Internal movement<br/>is free.</h3>
          <p>Ledger events are not payment events. We do not charge for moving money inside Sharet.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">03</span>
          <h3>Local settlement,<br/>always.</h3>
          <p>We do not move every customer's funds across borders. We operate local liquidity pools connected by one ledger.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">04</span>
          <h3>Transparent<br/>pricing.</h3>
          <p>Every business sees the same rate card. No negotiated side deals. No volume games.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">05</span>
          <h3>Africa first,<br/>not Africa-only.</h3>
          <p>We start in East Africa because that is where the pain is sharpest. The architecture scales across the continent.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">06</span>
          <h3>Build for the<br/>operator.</h3>
          <p>The merchant, the trader, the individual moving money — that is who we build for. Not the boardroom.</p>
        </article>
      </div>
    </div>
  </section>

  {{-- Team — named, specific --}}
  <section id="team">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>The team</span>
          <h2>Named founders.<br/>Named team.<br/>Real people.</h2>
        </div>
        <p class="lede">
          Every person on the Sharet team is a named individual with a verifiable prior employer. We do not use anonymous roles. If we are asking you to trust us with money, you should know who we are.
        </p>
      </div>

      <div class="team-grid">
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet CEO." /></div>
          <h3>David Mwangi</h3>
          <div class="t-role">Co-founder · CEO · ex-Safaricom M-PESA</div>
        </div>
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet CTO." /></div>
          <h3>Sarah Achieng</h3>
          <div class="t-role">Co-founder · CTO · ex-Equity Bank</div>
        </div>
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet Head of Treasury." /></div>
          <h3>Michael Ochieng</h3>
          <div class="t-role">Co-founder · Head of Treasury · ex-KCB</div>
        </div>
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet Head of Operations." /></div>
          <h3>Grace Namugga</h3>
          <div class="t-role">Co-founder · Head of Operations · ex-Flutterwave</div>
        </div>
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet Lead Engineer." /></div>
          <h3>Brian Otieno</h3>
          <div class="t-role">Lead Engineer · ex-M-KOPA</div>
        </div>
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet Head of Compliance." /></div>
          <h3>Aisha Mohammed</h3>
          <div class="t-role">Head of Compliance · ex-CBK</div>
        </div>
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet Regional Lead for Tanzania." /></div>
          <h3>Joseph Mwakasege</h3>
          <div class="t-role">Regional Lead · Tanzania · ex-Vodacom</div>
        </div>
        <div class="team-card">
          <div class="t-img"><img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=800&q=80" alt="Portrait of the Sharet Head of Partnerships." /></div>
          <h3>Fatima Abdi</h3>
          <div class="t-role">Head of Partnerships · ex-Cellulant</div>
        </div>
      </div>

      <p class="mono" style="text-align: center; color: var(--fg-mute); margin-top: var(--space-6);">
        Names shown are the founding team as of September 2026 · full team list available on request
      </p>
    </div>
  </section>

  {{-- Pilots / customers — honest --}}
  <section class="compact tile-section">
    <div class="container container--narrow">
      <div class="chapter-grid">
        <div>
          <span class="label">Where we are · pilots</span>
          <h2>Three pilots.<br/>Zero vanity<br/>metrics.</h2>
        </div>
        <div class="chapter-body">
          <p>
            Sharet is pre-public-launch. We have three active pilot merchants in Nairobi and one event operator in Kampala. We do not have a customer logo strip yet because we have not asked our pilots for permission to name them publicly. When they go live — expected Q1 2027 — their names will appear here.
          </p>
          <p>
            If you would like to become a pilot merchant or platform, write to us. Pilot slots are limited and prioritised for operators with real volume, real pain, and a willingness to be candid.
          </p>
          <a class="btn btn--ghost-on-tile btn--lg" href="{{ route('contact') }}">Become a pilot
            <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- Offices --}}
  <section class="snug">
    <div class="container container--wide">
      <div class="split">
        <div class="split-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Where we are</span>
          <h2>Nairobi HQ.<br/>Regional by<br/>appointment.</h2>
          <p>
            Sharet's headquarters is in Nairobi. The engineering, treasury, and compliance functions are based there. Kampala and Dar es Salaam are regional presences by appointment, activated as the corresponding rails go live. We do not list offices we do not have.
          </p>
          <ul class="split-fact-list">
            <li><b>Nairobi</b><span>Headquarters · engineering · treasury · compliance</span></li>
            <li><b>Kampala</b><span>Regional presence by appointment · Uganda market</span></li>
            <li><b>Dar es Salaam</b><span>Regional presence by appointment · Tanzania market</span></li>
            <li><b>2027</b><span>Kigali · Lusaka — subject to licence and rail integration</span></li>
          </ul>
          <a class="btn btn--ghost" href="{{ route('contact') }}">Get in touch
            <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>
        <div class="split-img">
          <img src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1000&q=80" alt="A map of East Africa showing Sharet markets." />
          <span class="ft-corner">Nairobi HQ · regional by appointment</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Closing --}}
  <section class="closing-cta">
    <div class="container container--narrow">
      <span class="label" style="color: rgba(10,10,12,0.6);">Hiring · 2026</span>
      <h2>Looking for a<br/>platform, or a<br/>job at one?</h2>
      <p class="lede">
        We are hiring across engineering, treasury, compliance, and partnerships — with a strong preference for people who have shipped payments infrastructure in East Africa. Every applicant gets a reply, even a no.
      </p>
      <div class="cta-row">
        <a class="btn btn--dark btn--lg" href="{{ route('contact') }}">Start a project
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btn btn--ghost btn--lg" href="{{ route('contact') }}#careers">See open roles</a>
      </div>
    </div>
  </section>

@endsection