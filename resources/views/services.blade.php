<!--services.blade.php-->
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sharet · Services &amp; Rates — Wallet-as-a-Service Tiers</title>
  <meta name="description" content="Three tiers of Sharet — Merchant, Platform, and Enterprise. One wallet, one ledger, many rails. Zero-fee internal transfers, transparent external fees, and white-label WaaS on the Enterprise tier." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Boldonse&family=Inter+Tight:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap" />
  <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>

  <header class="site-header">
    <div class="container container--wide">
      <nav class="nav" aria-label="Primary">
        <a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true"></span> Sharet!</a>
        <div class="nav-links" role="navigation">
          <a href="platform.html">Platform</a>
          <a href="company.html">Company</a>
          <a href="services.html" aria-current="page">Services</a>
          <a href="contact.html">Contact</a>
        </div>
        <div class="nav-cta-row">
          <a href="contact.html" class="btn btn--primary btn--sm">Start a project
            <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          <button class="nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer"><span aria-hidden="true"></span></button>
        </div>
      </nav>
    </div>
  </header>

  <div class="mobile-drawer" id="mobile-drawer" aria-hidden="true">
    <button class="drawer-close" aria-label="Close menu">Close</button>
    <a href="index.html">Index</a>
    <a href="platform.html">Platform</a>
    <a href="company.html">Company</a>
    <a href="services.html">Services</a>
    <a href="contact.html">Contact</a>
  </div>

  <main id="main">

    <!-- Hero -->
    <section class="hero">
      <div class="container container--wide">
        <div class="hero-grid">
          <div class="hero-text">
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Three tiers · One ledger</span>
            <h1 class="hero-headline">
              Three tiers.<br/>
              <span class="lime">One rate card.</span><br/>
              No surprises.
            </h1>
            <p class="hero-sub">
              Most payment providers bury their pricing in a "request a quote" form. We do not. Every business that uses Sharet fits one of three tiers, and the rate is on the card. Internal movement is free across every tier. External settlement is priced transparently.
            </p>
            <div class="hero-cta-row">
              <a class="btn btn--primary btn--lg" href="#tiers">See the tiers</a>
              <a class="btn btn--ghost btn--lg" href="#process">See the process</a>
            </div>
            <div class="hero-meta">
              <span><strong>0%</strong> · internal transfer fee</span>
              <span aria-hidden="true">·</span>
              <span><strong>3</strong> · rails minimum per market</span>
              <span aria-hidden="true">·</span>
              <span><strong>1</strong> · unified dashboard</span>
            </div>
          </div>
          <div class="hero-media">
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80" alt="A Sharet pricing preview showing merchant, platform, and enterprise tiers." />
            <div class="floating-tag ft-top">
              <span class="pill">Tier 02</span>
              PLATFORM · WHITE-LABEL
            </div>
            <div class="floating-tag ft-bottom">
              0% internal · fee on exit
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tiers -->
    <section id="tiers">
      <div class="container container--wide">
        <div class="section-head">
          <div>
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Service tiers</span>
            <h2>Merchant.<br/>Platform.<br/>Enterprise.</h2>
          </div>
          <p class="lede">
            The tier maps to the shape of the business, not the size of the brand. A single Nairobi merchant is on Tier 01. An event platform running cashless across three countries is on Tier 02. A bank launching a white-label wallet is on Tier 03.
          </p>
        </div>

        <div class="tier-grid">
          <article class="tier-card">
            <span class="label">Tier 01</span>
            <h3>Merchant</h3>
            <span class="tier-meta">Free setup · 0% internal · fee on exit</span>
            <hr class="tier-divider" />
            <p>For merchants, small businesses, and individual operators who want to receive payments from any rail into one wallet and settle externally on demand.</p>
            <ul class="tier-list">
              <li>One Sharet wallet · KES at launch</li>
              <li>Deposits from M-PESA, bank, card</li>
              <li>Zero-fee Sharet-to-Sharet transfers</li>
              <li>PayBill / Till / phone-number payouts</li>
              <li>Transaction history and reconciliation</li>
              <li>Standard support · community + email</li>
            </ul>
            <a class="btn btn--ghost" href="contact.html">Enquire about merchant</a>
          </article>

          <article class="tier-card featured">
            <span class="label">Tier 02 · most booked</span>
            <h3>Platform</h3>
            <span class="tier-meta">From $250/mo · multi-merchant · API</span>
            <hr class="tier-divider" />
            <p>For marketplaces, event operators, and platforms that need to onboard many merchants, route payments programmatically, and reconcile across rails and countries in one dashboard.</p>
            <ul class="tier-list">
              <li>Unlimited merchant wallets under one platform</li>
              <li>Full REST API and signed webhooks</li>
              <li>Multi-currency: KES · TZS · UGX</li>
              <li>Cross-border payouts (KE ↔ TZ ↔ UG)</li>
              <li>Team access with role-based permissions</li>
              <li>Priority support · dedicated onboarding</li>
              <li>Sandbox environment with simulated rails</li>
            </ul>
            <a class="btn btn--dark" href="contact.html">Enquire about platform</a>
          </article>

          <article class="tier-card">
            <span class="label">Tier 03</span>
            <h3>Enterprise</h3>
            <span class="tier-meta">From $2,500/mo · white-label WaaS</span>
            <hr class="tier-divider" />
            <p>For banks, fintechs, SACCOs, and large institutions that want to launch their own branded wallet on top of the Sharet ledger — without building rails, treasury, or reconciliation from scratch.</p>
            <ul class="tier-list">
              <li>White-label wallet and dashboard</li>
              <li>Direct ledger access and dedicated API quota</li>
              <li>Dedicated treasury and liquidity management</li>
              <li>Custom rails and bespoke integrations</li>
              <li>Regulatory and compliance support</li>
              <li>Named account team</li>
              <li>Service-level agreement (SLA) on uptime and settlement</li>
            </ul>
            <a class="btn btn--ghost" href="contact.html">Enquire about enterprise</a>
          </article>
        </div>

        <p class="mono" style="text-align: center; color: var(--fg-mute); margin-top: var(--space-7);">
          Internal Sharet transfers are free across every tier · external fees are transparent · no hidden settlement or licensing charges.
        </p>
      </div>
    </section>

    <!-- Rate card -->
    <section id="rates" class="tile-section">
      <div class="container container--wide">
        <div class="section-head">
          <div>
            <span class="label">Rate card · 2026</span>
            <h2>What costs what.</h2>
          </div>
          <p class="lede">
            Every Sharet business sees the same rate card. No negotiated side deals. No volume games. What you see is what you pay.
          </p>
        </div>

        <ul class="split-fact-list">
          <li><b>Sharet → Sharet</b><span>FREE · ledger-only · zero external rail invoked</span></li>
          <li><b>Customer → Customer</b><span>FREE · internal transfers across any Sharet wallet</span></li>
          <li><b>Merchant internal payments</b><span>FREE · same ledger model</span></li>
          <li><b>Deposit from M-PESA</b><span>Standard M-PESA collection rate · no Sharet markup</span></li>
          <li><b>Withdraw to M-PESA</b><span>Standard M-PESA payout rate + small Sharet fee</span></li>
          <li><b>PayBill / Till payment</b><span>Standard rail rate + small Sharet fee</span></li>
          <li><b>Bank withdrawal</b><span>Standard bank settlement rate + small Sharet fee</span></li>
          <li><b>Card processing</b><span>Standard processing rate + Sharet processing fee</span></li>
          <li><b>Cross-border payout</b><span>Local rail rate in recipient country + FX margin where applicable</span></li>
          <li><b>White-label (Tier 03)</b><span>Monthly platform fee · revenue share on external settlement</span></li>
        </ul>

        <p class="mono" style="text-align: center; color: var(--fg-on-tile-mute); margin-top: var(--space-7);">
          Full rate card available on request · all fees in local currency · all settlements reconciled daily.
        </p>
      </div>
    </section>

    <!-- Process -->
    <section id="process">
      <div class="container container--wide">
        <div class="section-head">
          <div>
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>How we onboard</span>
            <h2>From first call<br/>to first live<br/>transaction.</h2>
          </div>
          <p class="lede">
            Every Sharet business moves through the same onboarding path. Merchant onboarding takes a day. Platform onboarding takes a week. Enterprise onboarding takes two to six weeks depending on regulatory scope.
          </p>
        </div>

        <div class="process-grid">
          <article class="process-card">
            <h3>Discovery</h3>
            <p>Twenty-minute first call with the Sharet team. Understand your rails, volumes, countries, and pain points.</p>
          </article>
          <article class="process-card">
            <h3>Scope</h3>
            <p>Within 48 hours, a proposed tier, rate card, and integration path. Fixed, no estimate creep.</p>
          </article>
          <article class="process-card">
            <h3>Sandbox</h3>
            <p>Immediate access to a Sharet sandbox with simulated rails for Kenya, Tanzania, and Uganda.</p>
          </article>
          <article class="process-card">
            <h3>KYC</h3>
            <p>Business documentation, director verification, and (for Tier 03) regulatory review.</p>
          </article>
          <article class="process-card">
            <h3>Integration</h3>
            <p>API keys, webhook registration, treasury configuration, and team access setup.</p>
          </article>
          <article class="process-card">
            <h3>Go live</h3>
            <p>First live transaction. Dedicated onboarding engineer on standby for the first 72 hours.</p>
          </article>
          <article class="process-card">
            <h3>Reconcile</h3>
            <p>Daily settlement reports, weekly liquidity reviews, and a named account contact.</p>
          </article>
          <article class="process-card">
            <h3>Scale</h3>
            <p>Add rails, add countries, add merchants — without re-integrating or re-onboarding.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="tile-section">
      <div class="container container--narrow">
        <div class="section-head">
          <div>
            <span class="label">Frequent questions</span>
            <h2>The questions<br/>businesses ask first.</h2>
          </div>
          <p class="lede">
            These come up on almost every first call. If your question is not here, send the Sharet team a note — the address is on the contact page.
          </p>
        </div>

        <div class="faq-grid">
          <div class="faq-card">
            <h3>Is Sharet regulated?</h3>
            <p>Sharet operates under the payment services frameworks of each market it enters. Client funds are held in safeguarded accounts with partner banks, and all operations are subject to local KYC, AML, and data protection rules.</p>
          </div>
          <div class="faq-card">
            <h3>How does Sharet make money?</h3>
            <p>On external settlement. Money that enters, moves internally, and stays inside Sharet does not generate a fee. Money that needs to leave Sharet to M-PESA, MTN, Airtel, bank, or card rails generates a small fee that is shared with the underlying rail.</p>
          </div>
          <div class="faq-card">
            <h3>What currencies are supported?</h3>
            <p>KES, TZS, and UGX at launch. Additional African currencies will be added market by market. Multi-currency wallets are available on Tier 02 and above.</p>
          </div>
          <div class="faq-card">
            <h3>Do you offer white-label?</h3>
            <p>Yes — on Tier 03. Banks, fintechs, SACCOs, and platforms can launch their own branded wallet on top of Sharet without building ledger, treasury, or rail integrations themselves.</p>
          </div>
          <div class="faq-card">
            <h3>What if a rail goes down?</h3>
            <p>Sharet routes around rail outages. If M-PESA is degraded, payouts can fall back to an alternative Kenyan rail where appropriate. The Sharet dashboard shows rail status in real time.</p>
          </div>
          <div class="faq-card">
            <h3>How fast is settlement?</h3>
            <p>Internal Sharet transfers settle instantly — they are ledger events. External payouts settle at the speed of the underlying rail: M-PESA in seconds, bank transfers same-day or next-day depending on the bank.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Closing CTA -->
    <section class="closing-cta">
      <div class="container container--narrow">
        <span class="label" style="color: rgba(10,10,12,0.6);">Booking · 2026</span>
        <h2>Tell us the<br/>shape of your<br/>business.</h2>
        <p class="lede">
          A member of the Sharet team will read your first note within 48 working hours, and we will reply the same week with availability, a proposed tier, and a first read on integration effort.
        </p>
        <div class="cta-row">
          <a class="btn btn--dark btn--lg" href="contact.html">Start a project
            <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          <a class="btn btn--ghost btn--lg" href="platform.html">See the platform</a>
        </div>
      </div>
    </section>

  </main>

  <footer class="site-footer">
    <div class="container container--wide">
      <div class="footer-top">
        <div class="footer-brand">
          <span class="brand"><span class="brand-mark" aria-hidden="true"></span> Sharet!</span>
          <p>One wallet. Multiple payment rails. One African payment layer. Wallet-as-a-Service and financial operating system for East Africa and beyond.</p>
          <span class="label">Nairobi · Kampala · Dar es Salaam</span>
        </div>
        <div>
          <h4>Platform</h4>
          <ul>
            <li><a href="platform.html">Architecture</a></li>
            <li><a href="platform.html#ledger">Ledger</a></li>
            <li><a href="platform.html#treasury">Treasury</a></li>
            <li><a href="platform.html#adapters">Adapters</a></li>
          </ul>
        </div>
        <div>
          <h4>Services</h4>
          <ul>
            <li><a href="services.html">Wallet-as-a-Service</a></li>
            <li><a href="services.html#rates">Rates</a></li>
            <li><a href="services.html#tiers">Tiers</a></li>
            <li><a href="services.html#faq">FAQ</a></li>
          </ul>
        </div>
        <div>
          <h4>Connect</h4>
          <ul>
            <li><a href="contact.html">Start a project</a></li>
            <li><a href="contact.html#press">Press</a></li>
            <li><a href="contact.html#careers">Careers</a></li>
            <li><a href="#">Newsletter</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 Sharet Africa · Nairobi · Kampala · Dar es Salaam</span>
        <div class="footer-meta-links">
          <a href="#">Privacy</a>
          <a href="#">Imprint</a>
          <a href="#">Sitemap</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="assets/js/site.js" defer></script>
</body>
</html>