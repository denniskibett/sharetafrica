{{-- resources/views/home/pages/merchants.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Merchants — Accept from any rail. Settle anywhere.')
@section('description', 'One Sharet wallet for every rail — M-PESA, MTN, Airtel, cards, banks. Receive payments from anyone, settle externally on demand. Onboard in a day. Cheaper than rail-to-rail hops.')

@section('content')

  {{-- Hero --}}
  <section class="hero">
    <div class="container container--wide">
      <div class="hero-grid">
        <div class="hero-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>For merchants · East Africa</span>
          <h1 class="hero-headline">
            Accept from<br/>
            <span class="lime">any rail.</span><br/>
            Settle anywhere.
          </h1>
          <p class="hero-sub">
            One Sharet wallet for every payment that lands in your business — M-PESA, MTN, Airtel, cards, and bank transfers. Your customers pay however they want. You see one balance, one dashboard, and settle externally on demand. Onboarding takes a day.
          </p>
          <div class="hero-cta-row">
            <a class="btn btn--primary btn--lg" href="#intake">Onboard your business
              <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a class="btn btn--ghost btn--lg" href="#rates">See rates</a>
          </div>
          <div class="hero-meta">
            <span><strong>1 day</strong> · onboarding</span>
            <span aria-hidden="true">·</span>
            <span><strong>0%</strong> · internal transfer fee</span>
            <span aria-hidden="true">·</span>
            <span><strong>1</strong> · dashboard for all rails</span>
          </div>
        </div>
        <div class="hero-media">
          <img src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=1200&q=80" alt="A Nairobi merchant accepting a mobile money payment at their shop — customer pays from M-PESA, funds land in the merchant's Sharet wallet." />
          <div class="floating-tag ft-top">
            <span class="pill">Merchant</span>
            NAIROBI · KILIMANI
          </div>
          <div class="floating-tag ft-bottom">
            M-PESA · MTN · AIRTEL · CARD · BANK
          </div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-cell">
          <div class="stat-num"><span class="lime">1</span>day</div>
          <div class="stat-label">From signup to first payment</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">6<span class="lime">+</span></div>
          <div class="stat-label">Rails you can accept from</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">0<span class="lime">%</span></div>
          <div class="stat-label">Fee on internal movement</div>
        </div>
        <div class="stat-cell">
          <div class="stat-num">1</div>
          <div class="stat-label">Dashboard for all rails</div>
        </div>
      </div>
    </div>
  </section>

  {{-- What merchants get --}}
  <section class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">What you get</span>
          <h2>Everything your<br/>business needs<br/>to get paid.</h2>
        </div>
        <p class="lede">
          You do not have to integrate rail by rail, chase telco contracts, or reconcile across multiple portals. Sharet gives you one wallet, one dashboard, and one settlement process — for every rail your customers use.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--wide">
          <span class="cap-num">01 / Accept</span>
          <h3>Every rail into<br/>one wallet.</h3>
          <p>Your customers pay however they want — M-PESA, MTN MoMo, Airtel, Vodacom, cards, or bank transfer. Every payment lands in your Sharet wallet as one balance, in one currency view.</p>
          <span class="label">M-PESA · MTN · Airtel · Vodacom · Card · Bank</span>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">02 / Settle</span>
          <h3>Withdraw on<br/>your terms.</h3>
          <p>Move funds to M-PESA, bank, or any other rail when you need to — or hold them inside Sharet and pay suppliers, staff, or partners for free.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">03 / Reconcile</span>
          <h3>One dashboard,<br/>one truth.</h3>
          <p>Every transaction, every rail, every day — reconciled in one place. Export to your accounting stack. No spreadsheets across portals.</p>
        </article>

        <article class="cap-card cap-card--tall">
          <img src="https://images.unsplash.com/photo-1601597111158-2fceff292cdc?auto=format&fit=crop&w=1000&q=80" alt="A merchant dashboard showing transactions from multiple rails in one view." />
          <div class="cap-tall-meta">
            <span class="cap-num lime-text">04 / Merchant tools</span>
            <h3>Built for operators.</h3>
            <p>Multi-user access, roles, refunds, partial payments, and QR codes for in-person checkout — all included at no extra cost.</p>
          </div>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">05 / Pay out</span>
          <h3>Pay suppliers,<br/>staff, partners.</h3>
          <p>Send to any Sharet user for free. Send to M-PESA, MTN, Airtel, or bank for a small external fee.</p>
        </article>

        <article class="cap-card cap-card--std">
          <span class="cap-num">06 / Grow</span>
          <h3>Cross-border<br/>ready.</h3>
          <p>Accept from customers across East Africa. Settle locally in KES, TZS, or UGX. Expand market by market without re-integrating.</p>
        </article>

        <article class="cap-card cap-card--wide">
          <span class="cap-num">07 / For platforms</span>
          <h3>Multi-merchant<br/>under one account.</h3>
          <p>Marketplaces, event operators, and platforms can onboard many merchants under one Sharet account, with individual wallets, unified settlement, and programmatic payouts via API.</p>
          <span class="label">Marketplaces · events · SACCOs · payroll · franchising</span>
        </article>
      </div>
    </div>
  </section>

  {{-- How it works --}}
  <section>
    <div class="container container--wide">
      <div class="split">
        <div class="split-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>How it works</span>
          <h2>From signup<br/>to settled<br/>in one day.</h2>
          <p>
            Onboarding is deliberately simple. You sign up, verify your business, and get a Sharet wallet, a merchant ID, and a QR code. Your customers pay into your wallet however they pay today. You settle externally whenever you need to.
          </p>
          <ul class="split-fact-list">
            <li><b>Step 01</b><span>Sign up · business name, KYC, contact details</span></li>
            <li><b>Step 02</b><span>Verification · typically within a working day</span></li>
            <li><b>Step 03</b><span>Wallet live · merchant ID + QR code issued</span></li>
            <li><b>Step 04</b><span>Accept payments · from any supported rail</span></li>
            <li><b>Step 05</b><span>Settle externally · to M-PESA, bank, or another rail</span></li>
            <li><b>Step 06</b><span>Reconcile · one dashboard, exportable reports</span></li>
          </ul>
          <a class="btn btn--ghost" href="#intake">Start onboarding
            <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>
        <div class="split-img">
          <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1000&q=80" alt="A merchant checking their Sharet wallet balance — one number covering every rail." />
          <span class="ft-corner">One balance · all rails</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Rates --}}
  <section id="rates" class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Merchant rates · 2026</span>
          <h2>Simple pricing.<br/>No surprises.</h2>
        </div>
        <p class="lede">
          Every merchant sees the same rate card. No negotiated side deals. No volume games. You pay for external movement and settlement — nothing else.
        </p>
      </div>

      <ul class="split-fact-list">
        <li><b>Sharet → Sharet</b><span>FREE · internal transfers across any Sharet wallet</span></li>
        <li><b>Receive from M-PESA</b><span>Standard M-PESA collection rate · no Sharet markup</span></li>
        <li><b>Receive from MTN · Airtel</b><span>Standard rail collection rate · no Sharet markup</span></li>
        <li><b>Receive from card</b><span>Standard processing rate · small Sharet fee</span></li>
        <li><b>Receive from bank</b><span>Standard bank settlement rate · no Sharet markup</span></li>
        <li><b>Withdraw to M-PESA</b><span>Standard payout rate · small Sharet fee</span></li>
        <li><b>Withdraw to bank</b><span>Standard settlement rate · small Sharet fee</span></li>
        <li><b>PayBill / Till</b><span>Standard rail rate · small Sharet fee</span></li>
        <li><b>Multi-merchant platform</b><span>From $250/month · unlimited merchants · full API</span></li>
      </ul>

      <p class="mono" style="text-align: center; color: var(--fg-on-tile-mute); margin-top: var(--space-7);">
        Full rate card on request · all fees in local currency · all settlements reconciled daily.
      </p>
    </div>
  </section>

  {{-- Use cases --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Built for</span>
          <h2>Who uses Sharet<br/>for merchants.</h2>
        </div>
        <p class="lede">
          Any business that receives money from customers on more than one rail — or wants to pay suppliers, staff, or partners across rails — fits naturally on Sharet.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--std">
          <span class="cap-num">01</span>
          <h3>Retail &amp;<br/>hospitality.</h3>
          <p>Shops, restaurants, hotels, and service businesses accepting from any rail.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">02</span>
          <h3>Events &amp;<br/>ticketing.</h3>
          <p>Concert, festival, and conference operators needing cashless payment rails.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">03</span>
          <h3>Marketplaces.</h3>
          <p>Online and offline marketplaces managing many merchants under one platform.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">04</span>
          <h3>Multi-branch<br/>businesses.</h3>
          <p>Retail chains, franchise networks, and multi-location businesses.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">05</span>
          <h3>Logistics &amp;<br/>transport.</h3>
          <p>Fleet operators, delivery platforms, and transport businesses paying drivers.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">06</span>
          <h3>B2B suppliers.</h3>
          <p>Businesses paying suppliers across East Africa and settling externally.</p>
        </article>
      </div>
    </div>
  </section>

{{-- Intake --}}
<section id="intake" class="snug">
    <div class="container container--narrow">
        <div class="section-head">
            <div>
                <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Onboard as a merchant</span>
                <h2>Start in<br/>one working day.</h2>
            </div>
            <p class="lede">
                Tell us about your business and we will get you set up. Verification typically happens within one working day.
            </p>
        </div>

        <form class="form-card" method="POST" action="{{ route('merchants.signup') }}">
            @csrf

            <div class="form-row">
                <div class="form-field">
                    <label for="m-name">Your name</label>
                    <input id="m-name" name="name" type="text" required
                           value="{{ old('name') }}" placeholder="Amina Wanjiru" />
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field">
                    <label for="m-business">Business name</label>
                    <input id="m-business" name="business" type="text" required
                           value="{{ old('business') }}" placeholder="Nairobi Events Co." />
                    @error('business')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="m-email">Email</label>
                    <input id="m-email" name="email" type="email" required
                           value="{{ old('email') }}" placeholder="amina @ nairobievents.co.ke" />
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-field">
                    <label for="m-phone">Phone number</label>
                    <input id="m-phone" name="phone" type="tel" required
                           value="{{ old('phone') }}" placeholder="+254 712 XXX XXX" />
                    @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="m-country">Country of operation</label>
                    <select id="m-country" name="country">
                        <option value="KE" @selected(old('country') === 'KE')>Kenya</option>
                        <option value="TZ" @selected(old('country') === 'TZ')>Tanzania</option>
                        <option value="UG" @selected(old('country') === 'UG')>Uganda</option>
                        <option value=""  @selected(old('country') === '')>Multiple East African markets</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="m-rails">Rails you accept from today</label>
                    <input id="m-rails" name="rails" type="text"
                           value="{{ old('rails') }}"
                           placeholder="M-PESA · MTN · Airtel · Card" />
                </div>
            </div>

            <div class="form-row form-row--full">
                <div class="form-field">
                    <label for="m-volume">Monthly transaction volume (approx.)</label>
                    <input id="m-volume" name="volume" type="text"
                           value="{{ old('volume') }}"
                           placeholder="e.g. KES 500,000 – 2M" />
                </div>
            </div>

            <div class="form-row form-row--full">
                <div class="form-field">
                    <label for="m-brief">Tell us about your business</label>
                    <textarea id="m-brief" name="brief" required
                              placeholder="What does your business do, who pays you, how do they pay today, and where is the pain?">{{ old('brief') }}</textarea>
                    @error('brief')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-actions">
                <small>By submitting, you agree we will reply to the email above. No newsletter signup. No third-party sharing.</small>
                <button type="submit" class="btn btn--primary btn--lg">
                    Start onboarding
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
      <h2>Every rail.<br/>One wallet.<br/>One dashboard.</h2>
      <p class="lede">
        Stop chasing payment integrations rail by rail. Start accepting from everyone — and settle wherever your business needs to be.
      </p>
      <div class="cta-row">
        <a class="btn btn--dark btn--lg" href="#intake">Onboard now
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btn btn--ghost btn--lg" href="{{ route('developers') }}">See the infrastructure</a>
      </div>
    </div>
  </section>

@endsection