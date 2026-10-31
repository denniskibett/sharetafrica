{{-- resources/views/home/pages/trade.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Trade — Pay abroad. Get paid from abroad. Finance the gap. Settle locally.')
@section('description', 'Sharet sits on both sides of the African trade corridor — payments, FX, settlement, and embedded trade finance. Pay suppliers in China, the UAE, India, and Europe. Receive from international buyers. Settle locally in KES, TZS, or UGX. Order now, pay later — we carry the credit gap.')

@section('content')

  {{-- Hero --}}
  <section class="hero">
    <div class="container container--wide">
      <div class="hero-grid">
        <div class="hero-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>For importers, exporters &amp; traders · East Africa</span>
          <h1 class="hero-headline">
            Pay abroad.<br/>
            <span class="lime">Finance the gap.</span><br/>
            Settle local.
          </h1>
          <p class="hero-sub">
            Sharet sits on both sides of the African trade corridor — payments, FX, settlement, and embedded trade finance. Importers pay suppliers in China, the UAE, India, and Europe. Exporters receive from international buyers and settle locally in KES, TZS, or UGX. And when the gap between order and payment would otherwise kill the deal, Sharet carries the credit so the trade actually happens.
          </p>
          <div class="hero-cta-row">
            <a class="btn btn--primary btn--lg" href="#intake">Start trading on Sharet
              <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a class="btn btn--ghost btn--lg" href="#finance">See trade finance</a>
          </div>
          <div class="hero-meta">
            <span><strong>USD · EUR · GBP</strong> · inbound</span>
            <span aria-hidden="true">·</span>
            <span><strong>KES · TZS · UGX</strong> · local settlement</span>
            <span aria-hidden="true">·</span>
            <span><strong>30 · 60 · 90</strong> · credit terms</span>
          </div>
        </div>
        <div class="hero-media">
          <img src="https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=1200&q=80" alt="A container port in Mombasa — the trade corridor between Africa and the world." />
          <div class="floating-tag ft-top">
            <span class="pill">Corridor</span>
            MOMBASA · DUBAI · GUANGZHOU
          </div>
          <div class="floating-tag ft-bottom">
            IN AND OUT OF AFRICA · ON CREDIT
          </div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-cell">
          <div class="stat-num"><span class="lime">60</span>%</div>
          <div class="stat-label">Cheaper than traditional intermediary paths</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">4<span class="lime">+</span></div>
          <div class="stat-label">Global trade corridors supported</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">90<span class="lime">d</span></div>
          <div class="stat-label">Maximum credit terms available</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">1</div>
          <div class="stat-label">Ledger across the corridor</div>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 01: Two sides of the corridor --}}
  <section class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Two sides of the corridor · 01</span>
          <h2>Inbound.<br/>Outbound.<br/>One ledger.</h2>
        </div>
        <p class="lede">
          Whether you are paying a supplier in Guangzhou or receiving payment from a buyer in Hamburg, Sharet handles the settlement, the FX, and the local payout — through one account, one dashboard, one ledger.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--wide">
          <span class="cap-num">01 / Imports</span>
          <h3>Pay your suppliers<br/>abroad.</h3>
          <p>Send funds to a supplier in China, the UAE, India, or Europe. Settle in USD, EUR, or GBP. Receive confirmation with a full audit trail for customs and accounting. Your supplier receives payment in their preferred currency or rail.</p>
          <span class="label">China · UAE · India · Turkey · Europe</span>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">02 / Exports</span>
          <h3>Get paid<br/>from abroad.</h3>
          <p>International buyers send payment into your Sharet wallet. You receive it in USD, EUR, or GBP — settle locally in KES, TZS, or UGX.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">03 / Intra-Africa</span>
          <h3>Trade across<br/>East Africa.</h3>
          <p>Buy from Kenya, sell to Tanzania, source from Uganda. Settle across borders without the traditional multi-hop path.</p>
        </article>

        <article class="cap-card cap-card--tall">
          <img src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1000&q=80" alt="A map showing East Africa and the trade corridors connecting Africa to global markets." />
          <div class="cap-tall-meta">
            <span class="cap-num lime-text">04 / Diaspora</span>
            <h3>Send home from anywhere.</h3>
            <p>Friends and family abroad send money home. It arrives on M-PESA, MTN, Airtel, or bank — no signup required on the receiving side.</p>
          </div>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">05 / Compliance</span>
          <h3>Customs &amp;<br/>documentation.</h3>
          <p>Transaction records ready for customs declarations, VAT filings, and accounting. Exportable, auditable, and formatted for your ERP.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">06 / FX</span>
          <h3>Transparent<br/>rates.</h3>
          <p>Real-time FX with a small, clearly-disclosed margin. No hidden spreads. See the rate before you confirm.</p>
        </article>

        <article class="cap-card cap-card--wide">
          <span class="cap-num">07 / Working capital</span>
          <h3>Hold funds. Move them<br/>when the timing is right.</h3>
          <p>Trade flows are lumpy. Keep funds in your Sharet wallet between transactions, move them internally for free, and only pay an external fee when you finally settle. No monthly charges, no holding costs.</p>
          <span class="label">Free to hold · free to move internally · fee only on external settlement</span>
        </article>
      </div>
    </div>
  </section>

  {{-- Section 02: The credit gap — narrative --}}
  <section id="finance" class="snug">
    <div class="container container--wide">
      <div class="split">
        <div class="split-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Trade finance · 02</span>
          <h2>The gap between<br/>order and payment<br/>is where trade dies.</h2>
          <p>
            A Kenyan buyer places an order with a Tanzanian supplier. The Tanzanian supplier sources from a Chinese manufacturer. The Chinese manufacturer will not release goods until payment lands. The Kenyan buyer will not release payment until goods arrive. Meanwhile the Tanzanian supplier sits in the middle, exposed on both sides, unable to finance the wait.
          </p>
          <p>
            This is the primary bottleneck in African trade. Not demand. Not supply. <strong>Credit.</strong> The gap between order and payment kills more deals than any other single factor.
          </p>
          <ul class="split-fact-list">
            <li><b>Problem</b><span>Credit gap between order and payment kills trade</span></li>
            <li><b>Solution</b><span>Sharet extends credit at the point of order</span></li>
            <li><b>Terms</b><span>30, 60, or 90 days from delivery</span></li>
            <li><b>Risk</b><span>Sharet carries it; suppliers never exposed</span></li>
            <li><b>Settlement</b><span>Every party paid in full, on time, in their currency</span></li>
            <li><b>Coverage</b><span>Kenya ↔ Tanzania ↔ China · UAE · India · Europe</span></li>
          </ul>
          <a class="btn btn--ghost" href="#intake">Apply for a credit line
            <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>
        <div class="split-img">
          <img src="https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=1000&q=80" alt="A container ship at port — the physical movement of goods that Sharet finances end to end." />
          <span class="ft-corner">Order · finance · ship · repay</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 03: Trade finance offering bento --}}
  <section id="finance-detail" class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">The finance loop · 03</span>
          <h2>Order now.<br/>Pay later.<br/>We carry the gap.</h2>
        </div>
        <p class="lede">
          Sharet steps into the credit gap at the point of order. We pay your supplier upfront so goods are released, then you repay us on agreed terms. Your supplier never carries your credit risk. We do.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--wide">
          <span class="cap-num">The loop · worked example</span>
          <h3>Nairobi buyer.<br/>Dar supplier.<br/>Guangzhou factory.</h3>
          <p>A Kenyan buyer orders goods from a Tanzanian supplier. The supplier sources in bulk from a Chinese manufacturer. Payment has to reach China before goods are released; payment only reaches Tanzania after goods are delivered and accepted in Kenya. Sharet steps into the gap — pays China upfront on credit, collects from Kenya on agreed terms, and settles Tanzania on time.</p>
          <span class="label">One loop · three countries · one ledger</span>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">01 / Credit to buy</span>
          <h3>Sharet pays<br/>the supplier.</h3>
          <p>Approved buyers get a credit line on the Sharet ledger. When you order, Sharet pays your supplier — in China, the UAE, India, or anywhere else — so goods are released without you tying up cash upfront.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">02 / Terms</span>
          <h3>30 · 60 · 90<br/>days.</h3>
          <p>Repay Sharet over agreed terms from the day goods are delivered to you. Standard terms are 30, 60, or 90 days. Bespoke terms available for larger or repeat orders.</p>
        </article>

        <article class="cap-card cap-card--tall">
          <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1000&q=80" alt="A container port with goods in transit — Sharet carries the credit risk while goods move." />
          <div class="cap-tall-meta">
            <span class="cap-num lime-text">03 / Risk</span>
            <h3>Sharet carries it.</h3>
            <p>Your Tanzanian supplier and your Chinese manufacturer are never exposed to your credit. Sharet prices the risk, carries the exposure, and settles both parties in full and on time — regardless of when you pay us back.</p>
          </div>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">04 / Settlement</span>
          <h3>Everyone<br/>gets paid.</h3>
          <p>Chinese manufacturer paid on order. Tanzanian supplier paid on delivery. Kenyan buyer pays Sharet on terms. No party waits on another party's cash flow.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">05 / Visibility</span>
          <h3>Every leg,<br/>one ledger.</h3>
          <p>Goods in transit, payments in escrow, credit outstanding, repayments scheduled — all visible in one dashboard across all three countries.</p>
        </article>

        <article class="cap-card cap-card--wide">
          <span class="cap-num">06 / Working capital</span>
          <h3>Grow without<br/>tying up cash.</h3>
          <p>Most importers and traders grow as fast as their working capital allows. Sharet credit lets you order more, more often, without draining your balance sheet — because the goods pay for themselves once they land and sell. Financing is priced transparently, disclosed before you confirm, and repaid on terms that match your sales cycle.</p>
          <span class="label">Order more · sell faster · pay on terms · build a trade track record</span>
        </article>
      </div>
    </div>
  </section>

  {{-- Section 04: Corridors --}}
  <section id="corridors">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Corridors · 04</span>
          <h2>Where we<br/>operate today.</h2>
        </div>
        <p class="lede">
          Sharet's trade corridors cover the flows that matter most to East African importers, exporters, and diaspora. New corridors are added as customer demand justifies them.
        </p>
      </div>

      <ul class="split-fact-list" style="max-width: 100%;">
        <li><b>Kenya ↔ UAE</b><span>Dubai trade corridor · AED and USD settlement</span></li>
        <li><b>Kenya ↔ China</b><span>Guangzhou, Yiwu, Shenzhen · USD settlement</span></li>
        <li><b>Kenya ↔ India</b><span>Mumbai, Delhi · USD settlement</span></li>
        <li><b>Kenya ↔ Europe</b><span>EU, UK · EUR and GBP settlement</span></li>
        <li><b>Tanzania ↔ UAE</b><span>Dubai trade corridor · USD settlement</span></li>
        <li><b>Uganda ↔ UAE</b><span>Dubai trade corridor · USD settlement</span></li>
        <li><b>Kenya ↔ Tanzania</b><span>Intra-East Africa · KES/TZS settlement</span></li>
        <li><b>Kenya ↔ Uganda</b><span>Intra-East Africa · KES/UGX settlement</span></li>
        <li><b>UK ↔ East Africa</b><span>Diaspora remittance · GBP settlement</span></li>
        <li><b>US ↔ East Africa</b><span>Diaspora remittance · USD settlement</span></li>
      </ul>
    </div>
  </section>

  {{-- Section 05: Payment worked example --}}
  <section class="chapter">
    <div class="container container--narrow">
      <div class="chapter-grid">
        <div>
          <span class="label">Worked example · 05</span>
          <h2>Nairobi importer<br/>pays Guangzhou<br/>supplier.</h2>
        </div>
        <div class="chapter-body">
          <p>
            A Nairobi electronics importer orders goods from a supplier in Guangzhou. She needs to pay USD 8,000. Traditionally, this involves: a bank wire, an intermediary in USD, a 3–5 business day settlement window, and a spread that is hard to calculate. It costs her time and money she cannot easily quantify.
          </p>
          <blockquote class="pull-quote">
            She sees the rate, sees the fee, sees the delivery — before she confirms.
            <cite>— Sharet trade principle</cite>
          </blockquote>
          <p>
            On Sharet, she enters the supplier's bank details, chooses USD, sees the rate and fee in one screen, and confirms. The supplier receives USD in their account in Guangzhou. She gets a transaction record formatted for customs and accounting. The whole thing takes minutes to initiate, and the settlement window is days faster than the traditional wire.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 06: Trade finance worked example --}}
  <section class="chapter tile-section">
    <div class="container container--narrow">
      <div class="chapter-grid">
        <div>
          <span class="label">Worked example · finance · 06</span>
          <h2>Nairobi buyer,<br/>Dar supplier,<br/>Guangzhou factory.</h2>
        </div>
        <div class="chapter-body">
          <p>
            A Nairobi electronics buyer has a KES 4.5 million order it wants to place with a Tanzanian supplier. The Tanzanian supplier needs to source from a Guangzhou factory and pay in advance. The Kenyan buyer does not have KES 4.5 million in cash — but it has steady sales, a working shop, and a track record on Sharet.
          </p>
          <blockquote class="pull-quote">
            The order is good. The cash flow is the problem. We finance the gap.
            <cite>— Sharet trade finance</cite>
          </blockquote>
          <p>
            Sharet approves a 60-day credit line. On confirmation, Sharet pays the Guangzhou manufacturer in USD. Goods ship. Two weeks later, goods clear at Mombasa and are delivered to the Kenyan buyer. The buyer sells the goods over the next four weeks. On day 60, the buyer repays Sharet KES 4.5 million plus a transparent financing fee. Sharet had already settled the Tanzanian supplier on delivery and the Chinese manufacturer on order. Every party in the chain is paid in full, in their currency, on time.
          </p>
          <p>
            The Kenyan buyer never tied up cash. The Tanzanian supplier never carried credit risk. The Chinese manufacturer never waited for payment. Sharet carried the financing, earned the margin, and built a track record that lets the buyer qualify for a larger line on the next order.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- Section 07: Rates --}}
  <section id="rates">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Rates · 07</span>
          <h2>Simple pricing.<br/>Transparent terms.</h2>
        </div>
        <p class="lede">
          Every trade customer sees the same rate card. No negotiated side deals. No hidden spreads. Financing is priced upfront, disclosed before you confirm, and repaid on terms that match your sales cycle.
        </p>
      </div>

      <ul class="split-fact-list" style="max-width: 100%;">
        <li><b>Imports · USD/EUR/GBP</b><span>Standard FX rate · small Sharet fee · disclosed upfront</span></li>
        <li><b>Exports · inbound settlement</b><span>Standard receiving rate · small Sharet fee</span></li>
        <li><b>Intra-Africa payouts</b><span>Local rail rate · small Sharet fee</span></li>
        <li><b>Diaspora remittance</b><span>Standard receiving rate · no Sharet markup on inbound</span></li>
        <li><b>Trade finance (30d)</b><span>1.5% – 2.5% per month on outstanding credit · disclosed upfront</span></li>
        <li><b>Trade finance (60d)</b><span>1.75% – 3.0% per month · larger limits available</span></li>
        <li><b>Trade finance (90d)</b><span>2.0% – 3.5% per month · by application</span></li>
        <li><b>Credit line setup</b><span>Free · subject to KYC, trade history, and business verification</span></li>
        <li><b>Early repayment</b><span>Discounted · you pay only for the days you used the credit</span></li>
      </ul>

      <p class="mono" style="text-align: center; color: var(--fg-mute); margin-top: var(--space-7);">
        Full rate card on request · all fees in local currency · all settlements reconciled daily.
      </p>
    </div>
  </section>

  {{-- Section 08: Eligibility & process --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Eligibility &amp; process · 08</span>
          <h2>From application<br/>to credit line.</h2>
        </div>
        <p class="lede">
          A trade finance credit line is not a free-for-all. It is priced credit extended against a verifiable trade history. Here is how Sharet decides, and how fast.
        </p>
      </div>

      <div class="process-grid">
        <article class="process-card">
          <h3>Apply</h3>
          <p>Tell us what you trade, your corridors, and the order sizes you need to finance.</p>
        </article>
        <article class="process-card">
          <h3>Verify</h3>
          <p>Business registration, trade history, and (for larger lines) financial statements. Typical turnaround: 3–5 working days.</p>
        </article>
        <article class="process-card">
          <h3>Approval</h3>
          <p>A credit limit is issued on the Sharet ledger, tied to your account. Terms agreed upfront — 30, 60, or 90 days.</p>
        </article>
        <article class="process-card">
          <h3>Use</h3>
          <p>When you place an order, Sharet pays the supplier directly. Credit is drawn, goods are released, delivery scheduled.</p>
        </article>
        <article class="process-card">
          <h3>Delivery</h3>
          <p>Goods arrive. Sharet tracks the shipment and confirms delivery. Your repayment clock starts on delivery day.</p>
        </article>
        <article class="process-card">
          <h3>Repay</h3>
          <p>Repay Sharet on agreed terms — from sales revenue, another financing source, or your own cash. Early repayment is discounted.</p>
        </article>
        <article class="process-card">
          <h3>Renew</h3>
          <p>Repay on time and your limit increases on the next cycle. Track record compounds.</p>
        </article>
        <article class="process-card">
          <h3>Scale</h3>
          <p>Add corridors, add suppliers, increase line size — without changing the fundamental structure.</p>
        </article>
      </div>
    </div>
  </section>

  {{-- Section 09: FAQ --}}
  <section class="tile-section">
    <div class="container container--narrow">
      <div class="section-head">
        <div>
          <span class="label">Frequent questions · 09</span>
          <h2>The questions<br/>traders ask first.</h2>
        </div>
        <p class="lede">
          These come up on almost every trade conversation. If your question is not here, send the Sharet trade team a note.
        </p>
      </div>

      <div class="faq-grid">
        <div class="faq-card">
          <h3>Who is eligible for a credit line?</h3>
          <p>Established importers and traders with a verifiable trade history, a business registration in Kenya, Tanzania, or Uganda, and consistent monthly order volume. Larger lines require financial statements.</p>
        </div>
        <div class="faq-card">
          <h3>How is credit priced?</h3>
          <p>Between 1.5% and 3.5% per month on outstanding credit, depending on terms and risk profile. The rate is disclosed upfront, before you confirm any order. No hidden spreads, no arrangement fees.</p>
        </div>
        <div class="faq-card">
          <h3>What if goods are delayed?</h3>
          <p>Repayment starts on delivery day. If goods are delayed at customs or in transit, the repayment clock does not start until they are in your hands. Sharet absorbs that delay.</p>
        </div>
        <div class="faq-card">
          <h3>What if I cannot repay on time?</h3>
          <p>Contact us before the due date. Extensions are available, priced transparently, and agreed in advance. Non-communication is the problem; extension is not.</p>
        </div>
        <div class="faq-card">
          <h3>Do you finance the Chinese side too?</h3>
          <p>Yes. Sharet pays the Chinese manufacturer directly on order from our USD liquidity. The manufacturer receives full payment before goods ship. This is the same credit we extend to you, presented to them as an on-time payment.</p>
        </div>
        <div class="faq-card">
          <h3>What currencies can be financed?</h3>
          <p>USD, EUR, GBP on the inbound and outbound side. KES, TZS, UGX on local settlement. Multi-currency credit lines available for larger traders.</p>
        </div>
      </div>
    </div>
  </section>

{{-- Section 10: Intake --}}
<section id="intake" class="snug">
    <div class="container container--narrow">
        <div class="section-head">
            <div>
                <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Start trading on Sharet · 10</span>
                <h2>Tell us your<br/>corridor.</h2>
            </div>
            <p class="lede">
                Onboarding for trade accounts typically takes one to two weeks. Tell us your corridors, volumes, and financing needs.
            </p>
        </div>

        <form class="form-card" method="POST" action="{{ route('trade.apply') }}">
            @csrf

            <div class="form-row">
                <div class="form-field">
                    <label for="t-name">Your name</label>
                    <input id="t-name" name="name" type="text" required
                           value="{{ old('name') }}" placeholder="Grace Mwangi" />
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field">
                    <label for="t-business">Business name</label>
                    <input id="t-business" name="business" type="text" required
                           value="{{ old('business') }}" placeholder="Nairobi Electronics Ltd." />
                    @error('business')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="t-email">Email</label>
                    <input id="t-email" name="email" type="email" required
                           value="{{ old('email') }}" placeholder="grace @ nairobielectronics.co.ke" />
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field">
                    <label for="t-phone">Phone number</label>
                    <input id="t-phone" name="phone" type="tel" required
                           value="{{ old('phone') }}" placeholder="+254 722 XXX XXX" />
                    @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="t-trade_type">Trade type</label>
                    <select id="t-trade_type" name="trade_type" required>
                        <option value="importer"     @selected(old('trade_type') === 'importer')>Importer — paying suppliers abroad</option>
                        <option value="exporter"     @selected(old('trade_type') === 'exporter')>Exporter — receiving from buyers abroad</option>
                        <option value="both"         @selected(old('trade_type') === 'both')>Both — two-way trade</option>
                        <option value="diaspora"     @selected(old('trade_type') === 'diaspora')>Diaspora — sending home</option>
                        <option value="intra_africa" @selected(old('trade_type') === 'intra_africa')>Intra-Africa trader</option>
                    </select>
                    @error('trade_type')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field">
                    <label for="t-corridor">Main corridor</label>
                    <select id="t-corridor" name="corridor" data-corridor-select="t-corridor-other-wrapper">
                      <option value="">Select a corridor</option>
                      @foreach(\App\Models\Corridor::grouped() as $groupLabel => $corridors)
                          <optgroup label="{{ $groupLabel }}">
                              @foreach($corridors as $code => $label)
                                  <option value="{{ $code }}" @selected(old('corridor') === $code)>
                                      {{ $label }}
                                  </option>
                              @endforeach
                          </optgroup>
                      @endforeach
                  </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="t-need">What do you need?</label>
                    <select id="t-need" name="need" required>
                        <option value="payment_abroad"      @selected(old('need') === 'payment_abroad')>Send a payment abroad</option>
                        <option value="receive_from_abroad" @selected(old('need') === 'receive_from_abroad')>Receive a payment from abroad</option>
                        <option value="intra_africa"        @selected(old('need') === 'intra_africa')>Trade across East Africa</option>
                        <option value="credit_line"         @selected(old('need') === 'credit_line')>Apply for a trade credit line</option>
                        <option value="all"                 @selected(old('need') === 'all')>All of the above</option>
                    </select>
                    @error('need')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field">
                    <label for="t-monthly_volume">Monthly trade volume (approx.)</label>
                    <input id="t-monthly_volume" name="monthly_volume" type="text"
                           value="{{ old('monthly_volume') }}"
                           placeholder="e.g. USD 50,000 – 200,000" />
                </div>
            </div>

            <div class="form-row form-row--full">
                <div class="form-field">
                    <label for="t-expected_order_size">If applying for credit — expected order size and frequency</label>
                    <input id="t-expected_order_size" name="expected_order_size" type="text"
                           value="{{ old('expected_order_size') }}"
                           placeholder="e.g. KES 3–5M per order · monthly" />
                </div>
            </div>

            <div class="form-row form-row--full">
                <div class="form-field">
                    <label for="t-brief">Tell us about your trade</label>
                    <textarea id="t-brief" name="brief" required
                              placeholder="What do you buy or sell, who are your suppliers or buyers, and where is the credit gap?">{{ old('brief') }}</textarea>
                    @error('brief')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-actions">
                <small>By submitting, you agree we will reply to the email above. No newsletter signup. No third-party sharing.</small>
                <button type="submit" class="btn btn--primary btn--lg">
                    Submit trade application
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
      <h2>Trade across<br/>the world.<br/>Settle locally.<br/>Finance the gap.</h2>
      <p class="lede">
        Payments, FX, settlement, and embedded trade finance — one ledger across the corridor. Order now, pay later, and let the goods pay for themselves.
      </p>
      <div class="cta-row">
        <a class="btn btn--dark btn--lg" href="#intake">Start trading
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btn btn--ghost btn--lg" href="{{ route('developers') }}">See the infrastructure</a>
      </div>
    </div>
  </section>

@endsection