<!--platform.blade.php-->
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sharet · Platform — Architecture, Ledger, Treasury, Adapters</title>
  <meta name="description" content="The Sharet platform — a double-entry ledger, country-level liquidity pools, dynamic rail adapters, and one dashboard across East Africa. The financial operating system for African business." />
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
          <a href="platform.html" aria-current="page">Platform</a>
          <a href="company.html">Company</a>
          <a href="services.html">Services</a>
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
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Platform · architecture</span>
            <h1 class="hero-headline">
              One ledger.<br/>
              <span class="lime">Many rails.</span><br/>
              Zero silos.
            </h1>
            <p class="hero-sub">
              Sharet is built on a small set of primitives: a wallet, a double-entry ledger, a treasury engine, and a set of rail adapters. Everything else — merchant tools, dashboards, APIs, white-label wallets — is built on top. This is the layer underneath African money movement.
            </p>
            <div class="hero-cta-row">
              <a class="btn btn--primary btn--lg" href="#ledger">See the ledger</a>
              <a class="btn btn--ghost btn--lg" href="#adapters">See the adapters</a>
            </div>
            <div class="hero-meta">
              <span><strong>6</strong> · core primitives</span>
              <span aria-hidden="true">·</span>
              <span><strong>3</strong> · countries at launch</span>
              <span aria-hidden="true">·</span>
              <span><strong>1</strong> · unified ledger</span>
            </div>
          </div>
          <div class="hero-media">
            <img src="https://images.unsplash.com/photo-1639762681485-074b7f938ba0?auto=format&fit=crop&w=1200&q=80" alt="A stylised architecture diagram showing mobile money rails converging into one Sharet ledger and treasury." />
            <div class="floating-tag ft-top">
              <span class="pill">Layer 01</span>
              WALLET · LEDGER · TREASURY
            </div>
            <div class="floating-tag ft-bottom">
              M-PESA · MTN · AIRTEL · BANK · CARD
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 1: Wallet -->
    <section id="wallet">
      <div class="container container--wide">
        <div class="section-head">
          <div>
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Primitive 01</span>
            <h2>The wallet.</h2>
          </div>
          <p class="lede">
            The Sharet wallet is a single account that holds value in any supported currency and connects to every rail. For a consumer, it replaces three apps. For a business, it replaces several dashboards and settlement accounts.
          </p>
        </div>

        <div class="split">
          <div class="split-text">
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>What a wallet does</span>
            <h2>One balance<br/>across every<br/>rail in Africa.</h2>
            <p>
              A Sharet wallet is not tied to a single mobile money network. It is tied to the Sharet ledger. When you deposit KES 10,000 from M-PESA, your wallet balance shows KES 10,000 — full stop. When you send KES 5,000 to another Sharet user, the ledger records it, and no external rail is touched. The wallet shows the new balance immediately.
            </p>
            <ul class="split-fact-list">
              <li><b>Balance</b><span>One number, one currency view, all rails</span></li>
              <li><b>Deposit</b><span>From any supported external rail into Sharet</span></li>
              <li><b>Internal</b><span>Sharet-to-Sharet transfers are ledger-only, free</span></li>
              <li><b>Withdraw</b><span>Routed to the appropriate rail in the recipient's country</span></li>
              <li><b>Merchant mode</b><span>Receive payments, hold balance, settle externally on demand</span></li>
            </ul>
          </div>
          <div class="split-img">
            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1000&q=80" alt="A Sharet user viewing a single unified balance that combines M-PESA, MTN, and bank rails." />
            <span class="ft-corner">Wallet view · one balance</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 2: Ledger -->
    <section id="ledger" class="tile-section">
      <div class="container container--narrow">
        <div class="chapter-grid">
          <div>
            <span class="label">Primitive 02 · ledger</span>
            <h2>The double-entry<br/>ledger is the<br/>kernel.</h2>
          </div>
          <div class="chapter-body">
            <p>
              Every Sharet movement is recorded as a pair of debits and credits. When a user sends KES 5,000 to another Sharet user, the ledger does not "process a payment" — it records two entries: a debit on the sender's account, and a credit on the recipient's. External rails are not touched. Nothing crosses a border. No telco is invoked. The ledger simply updates ownership.
            </p>
            <blockquote class="pull-quote">
              The ledger is the cheapest place for money to move. Everything else is a rail.
              <cite>— Sharet engineering principle</cite>
            </blockquote>
            <p>
              This is why internal transfers on Sharet are free. They are ledger events, not payment events. External rails only come into play when funds need to cross back into the wider financial system — M-PESA, MTN, Airtel, a bank, or a card scheme. Every transaction has clear ownership, an audit trail, and a settlement path.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 3: Treasury -->
    <section id="treasury">
      <div class="container container--wide">
        <div class="section-head">
          <div>
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Primitive 03</span>
            <h2>The treasury.</h2>
          </div>
          <p class="lede">
            Sharet does not move every customer's funds across borders per transaction. It maintains a locally settled liquidity position in each market — connected by one global ledger — and reconciles movement across all of them.
          </p>
        </div>

        <div class="cap-bento">
          <article class="cap-card cap-card--wide">
            <span class="cap-num">Treasury / Kenya</span>
            <h3>KES liquidity pool.</h3>
            <p>Safeguarded funds held with Kenyan banks and M-PESA settlement accounts. Ready to fulfil deposits and withdrawals instantly.</p>
            <span class="label">M-PESA · Bank · Card settlement</span>
          </article>

          <article class="cap-card cap-card--std">
            <span class="cap-num">Treasury / Tanzania</span>
            <h3>TZS liquidity pool.</h3>
            <p>Local settlement position with Vodacom M-Pesa and Tanzanian bank partners.</p>
          </article>

          <article class="cap-card cap-card--std">
            <span class="cap-num">Treasury / Uganda</span>
            <h3>UGX liquidity pool.</h3>
            <p>Local settlement position with MTN MoMo and Ugandan bank partners.</p>
          </article>

          <article class="cap-card cap-card--tall">
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1000&q=80" alt="A treasury dashboard showing liquidity pools across Kenya, Tanzania, and Uganda." />
            <div class="cap-tall-meta">
              <span class="cap-num lime-text">Reconciliation</span>
              <h3>Monitored every minute.</h3>
              <p>Available liquidity, reserved funds, pending settlements, inbound collections, outbound payments — reconciled daily, alertable hourly, top-upable automatically.</p>
            </div>
          </article>

          <article class="cap-card cap-card--std">
            <span class="cap-num">Auto top-up</span>
            <h3>Thresholds, not<br/>guesswork.</h3>
            <p>Each country pool has a minimum balance. When it dips below, the treasury engine raises an alert or triggers a transfer automatically.</p>
          </article>

          <article class="cap-card cap-card--std">
            <span class="cap-num">Settlement</span>
            <h3>In-country, not<br/>cross-border.</h3>
            <p>Payouts settle locally. Cross-border movement is a treasury rebalancing operation, not a per-customer transaction.</p>
          </article>

          <article class="cap-card cap-card--wide">
            <span class="cap-num">Monitoring</span>
            <h3>Real-time visibility<br/>across every market.</h3>
            <p>Every movement in every pool is visible to the treasury team and to business customers through the Sharet dashboard. Settlement reports are exportable, auditable, and ready for reconciliation against your own accounting stack.</p>
            <span class="label">Daily reconciliation · exportable reports · audit trail</span>
          </article>
        </div>
      </div>
    </section>

    <!-- Section 4: Adapters -->
    <section id="adapters" class="snug">
      <div class="container container--wide">
        <div class="split">
          <div class="split-text">
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Primitive 04</span>
            <h2>Adapters: the<br/>rail routing<br/>layer.</h2>
            <p>
              Every external rail — M-PESA Kenya, Vodacom M-Pesa Tanzania, MTN MoMo Uganda, Airtel, banks, cards — is implemented as a separate adapter behind the same Sharet interface. The user does not choose a rail. Sharet does.
            </p>
            <ul class="split-fact-list">
              <li><b>Detect</b><span>Parse the recipient (phone, PayBill, Till, bank code)</span></li>
              <li><b>Resolve</b><span>Identify country, network, and available rail</span></li>
              <li><b>Check</b><span>Verify balance, liquidity, and limit availability</span></li>
              <li><b>Route</b><span>Execute through the appropriate regulated channel</span></li>
              <li><b>Reconcile</b><span>Post the result back to the Sharet ledger</span></li>
            </ul>
            <a class="btn btn--ghost" href="#routing">See the routing example
              <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
          <div class="split-img">
            <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1000&q=80" alt="A routing diagram showing multiple payment rails connecting into a single Sharet adapter layer." />
            <span class="ft-corner">Adapter layer · six rails</span>
          </div>
        </div>

        <!-- Routing example -->
        <div id="routing" class="chapter" style="padding-top: var(--space-9);">
          <div class="chapter-grid">
            <div>
              <span class="label">Routing · worked example</span>
              <h2>Sharet → +255<br/>Vodacom number.</h2>
            </div>
            <div class="chapter-body">
              <p>
                A Sharet merchant in Nairobi wants to pay a supplier in Dar es Salaam. They enter the supplier's +255 phone number into Sharet and request a payout. Sharet's adapter layer detects the country code, looks up the prefix, resolves the network (Vodacom M-Pesa Tanzania), checks the treasury pool for TZS liquidity, and executes the payout through the Vodacom rail.
              </p>
              <blockquote class="pull-quote">
                The user sees one wallet. Sharet handles the country, network, rail, and settlement.
                <cite>— Adapter design principle</cite>
              </blockquote>
              <p>
                The same request format works for +256 MTN Uganda numbers, Kenyan PayBill codes, Till numbers, and bank accounts. The user types the destination; Sharet does the routing.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 5: API -->
    <section id="api">
      <div class="container container--wide">
        <div class="section-head">
          <div>
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Primitive 05</span>
            <h2>The API.</h2>
          </div>
          <p class="lede">
            REST endpoints, signed webhooks, idempotent transactions, and a sandbox that simulates Kenya, Tanzania, and Uganda rails. Integrate once, settle everywhere Sharet operates.
          </p>
        </div>

        <div class="channel-grid">
          <article class="channel-card">
            <span class="label">POST /transactions</span>
            <h3>Send money.</h3>
            <p>Route a payment to any supported rail — M-PESA, MTN, Airtel, bank, or another Sharet wallet — with a single idempotent call.</p>
            <a class="channel-line" href="contact.html">Request API access →</a>
            <span class="mono" style="color: var(--fg-mute);">Idempotency key · signed · webhook on settle</span>
          </article>
          <article class="channel-card">
            <span class="label">GET /ledger</span>
            <h3>Query the ledger.</h3>
            <p>Every movement in the Sharet ledger is queryable with filters by account, currency, date, and rail.</p>
            <a class="channel-line" href="contact.html">See the ledger spec →</a>
            <span class="mono" style="color: var(--fg-mute);">Double-entry · auditable · exportable</span>
          </article>
          <article class="channel-card">
            <span class="label">POST /webhooks</span>
            <h3>Subscribe to events.</h3>
            <p>Signed webhooks on transaction.settled, transaction.failed, treasury.low, and rail.degraded.</p>
            <a class="channel-line" href="contact.html">See webhook schema →</a>
            <span class="mono" style="color: var(--fg-mute);">HMAC-signed · retried · replayable</span>
          </article>
        </div>
      </div>
    </section>

    <!-- Section 6: Dashboard -->
    <section id="dashboard" class="snug">
      <div class="container container--wide">
        <div class="split split--reverse">
          <div class="split-text">
            <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Primitive 06</span>
            <h2>One dashboard<br/>across every<br/>market.</h2>
            <p>
              Merchants, businesses, and platform partners get one view of every rail, every country, every movement. Reconciliation, liquidity, settlements, and reporting — in one place. The multi-portal era of African payments ends here.
            </p>
            <ul class="split-fact-list">
              <li><b>Reconciliation</b><span>Daily settlement reports, exportable</span></li>
              <li><b>Liquidity</b><span>Country-level pool balances and thresholds</span></li>
              <li><b>Transactions</b><span>Filterable by rail, country, status, amount</span></li>
              <li><b>Merchants</b><span>Multi-merchant management for platforms</span></li>
              <li><b>Team</b><span>Role-based access for finance, ops, and admin</span></li>
            </ul>
            <a class="btn btn--ghost" href="services.html">See services &amp; rates
              <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
          <div class="split-img">
            <img src="https://images.unsplash.com/photo-1601597111158-2fceff292cdc?auto=format&fit=crop&w=1000&q=80" alt="A Sharet dashboard showing transactions, liquidity, and settlement across Kenya, Tanzania, and Uganda." />
            <span class="ft-corner">Dashboard · all rails, one view</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Closing CTA -->
    <section class="closing-cta">
      <div class="container container--narrow">
        <span class="label" style="color: rgba(10,10,12,0.6);">Booking · 2026</span>
        <h2>Want to build<br/>on the Sharet<br/>ledger?</h2>
        <p class="lede">
          We work with businesses, platforms, and partners that already have payment volume and want to stop integrating rails one by one. Request sandbox access or a first call with the team.
        </p>
        <div class="cta-row">
          <a class="btn btn--dark btn--lg" href="contact.html">Start a project
            <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          <a class="btn btn--ghost btn--lg" href="services.html">See services &amp; rates</a>
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