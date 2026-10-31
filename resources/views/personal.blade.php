{{-- resources/views/home/pages/personal.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Personal — Send to anyone in East Africa. Cheaper. No signup required on their side.')
@section('description', 'Send money to anyone in East Africa — M-PESA, MTN, Airtel, Vodacom, or bank — from one Sharet wallet. Zero internal fees. Recipient never needs to sign up. Worked cost comparison inside.')

@section('content')

  {{-- Hero --}}
  <section class="hero">
    <div class="container container--wide">
      <div class="hero-grid">
        <div class="hero-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>For individuals · East Africa</span>
          <h1 class="hero-headline">
            Send to anyone.<br/>
            <span class="lime">They don't need</span><br/>
            an account.
          </h1>
          <p class="hero-sub">
            You have a Sharet wallet. Your family, your friends, your landlord, your supplier — they have M-PESA, MTN, Airtel, or a bank account. You type their number, confirm the amount, and they get paid. They never download anything. You pay less than the traditional route, and often much less.
          </p>
          <div class="hero-cta-row">
            <a class="btn btn--primary btn--lg" href="#intake">Create your wallet
              <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a class="btn btn--ghost btn--lg" href="#how">See a worked example</a>
          </div>
          <div class="hero-meta">
            <span><strong>0%</strong> · Sharet-to-Sharet fee</span>
            <span aria-hidden="true">·</span>
            <span><strong>~60%</strong> · cheaper than traditional hops</span>
            <span aria-hidden="true">·</span>
            <span><strong>0</strong> · signups needed on the other side</span>
          </div>
        </div>
        <div class="hero-media">
          <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80" alt="A Sharet user sending money from their phone in Nairobi." />
          <div class="floating-tag ft-top">
            <span class="pill">Send</span>
            TO +255 · VODACOM
          </div>
          <div class="floating-tag ft-bottom">
            THEY GET PAID ON THEIR RAIL
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- The mechanic — explained in plain steps --}}
  <section id="how" class="tile-section">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">How it works</span>
          <h2>What happens<br/>when you press<br/>"Send".</h2>
        </div>
        <p class="lede">
          No black box. Here is exactly what Sharet does when you send money to a phone number that is not on Sharet. The recipient never needs to know any of this.
        </p>
      </div>

      <div class="process-grid">
        <article class="process-card">
          <h3>You type a number</h3>
          <p>For example <code style="color:var(--lime)">+255 754 000 000</code>. You choose the amount: KES 10,000. You confirm.</p>
        </article>
        <article class="process-card">
          <h3>Sharet reads the number</h3>
          <p>Country code +255 → Tanzania. Prefix 754 → Vodacom. Currency: TZS. Rail: Vodacom M-Pesa.</p>
        </article>
        <article class="process-card">
          <h3>Sharet checks liquidity</h3>
          <p>Verifies your balance, checks the Tanzania TZS pool, confirms the recipient number is in good standing.</p>
        </article>
        <article class="process-card">
          <h3>Sharet converts and routes</h3>
          <p>KES is converted to TZS at the displayed rate. A payout is executed through the Vodacom rail.</p>
        </article>
        <article class="process-card">
          <h3>The recipient gets paid</h3>
          <p>They receive a standard mobile money notification: "You have received TZS … from Sharet."</p>
        </article>
        <article class="process-card">
          <h3>You get a receipt</h3>
          <p>A Sharet ledger entry: amount, FX rate, fee, recipient, timestamp. Exportable for taxes or records.</p>
        </article>
        <article class="process-card">
          <h3>Nothing else happens</h3>
          <p>No signup on the recipient's side. No app install. No account creation. No phone call. Just the payment.</p>
        </article>
        <article class="process-card">
          <h3>If it fails, you know</h3>
          <p>You get an immediate webhook, a notification in the app, and a refund of the full amount if the rail could not complete.</p>
        </article>
      </div>
    </div>
  </section>

  {{-- Cost comparison --}}
  <section>
    <div class="container container--narrow">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Cost comparison</span>
          <h2>KES 10,000 from<br/>Nairobi to a<br/>Vodacom number.</h2>
        </div>
        <p class="lede">
          Here is what the same transfer costs on the traditional path versus on Sharet. Both numbers are real, sourced from published rates as of September 2026. Your mileage may vary by a small margin.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--wide">
          <span class="cap-num">Traditional path · M-PESA → Bank → Forex → Vodacom</span>
          <h3>Three hops.<br/>Three fees.</h3>
          <ul class="split-fact-list" style="border-top: 1px solid var(--rule); margin-top: var(--space-4);">
            <li><b>M-PESA withdrawal</b><span>KES 165</span></li>
            <li><b>Bank wire fee</b><span>KES 250</span></li>
            <li><b>Forex spread (~3%)</b><span>KES 300</span></li>
            <li><b>Vodacom deposit</b><span>KES 135</span></li>
            <li><b>Total fees</b><span style="color:var(--lime)">KES 850</span></li>
            <li><b>Recipient receives</b><span>~ TZS 145,000</span></li>
            <li><b>Settlement time</b><span>1 – 3 business days</span></li>
          </ul>
        </article>

        <article class="cap-card cap-card--wide">
          <span class="cap-num">Sharet path · one ledger, one hop</span>
          <h3>One payment.<br/>One fee.</h3>
          <ul class="split-fact-list" style="border-top: 1px solid var(--rule); margin-top: var(--space-4);">
            <li><b>Sharet fee</b><span>KES 320</span></li>
            <li><b>FX spread (transparent, ~1%)</b><span>Included</span></li>
            <li><b>Vodacom deposit</b><span>Included</span></li>
            <li><b>Total fees</b><span style="color:var(--lime)">KES 320</span></li>
            <li><b>Recipient receives</b><span>~ TZS 165,000</span></li>
            <li><b>Settlement time</b><span>Minutes</span></li>
          </ul>
        </article>
      </div>

      <p class="mono" style="text-align: center; color: var(--fg-mute); margin-top: var(--space-7);">
        Savings: KES 530 (~62%) · recipient receives ~14% more · settlement ~1000× faster
      </p>
    </div>
  </section>

  {{-- What you can do --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">What you can do</span>
          <h2>Everything you<br/>already do today.<br/>Just cheaper.</h2>
        </div>
        <p class="lede">
          A Sharet personal wallet replaces the four or five apps and portals you currently use to move money. Here is what is inside.
        </p>
      </div>

      <div class="cap-bento">
        <article class="cap-card cap-card--std">
          <span class="cap-num">01</span>
          <h3>Free internal<br/>transfers.</h3>
          <p>Send to any Sharet user in East Africa for free. Instantly. Permanently.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">02</span>
          <h3>Send across<br/>rails.</h3>
          <p>Send to M-PESA, MTN, Airtel, Vodacom, or bank accounts across Kenya, Tanzania, and Uganda.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">03</span>
          <h3>Receive from<br/>abroad.</h3>
          <p>Friends and family abroad send to your Sharet wallet. You receive directly on M-PESA, MTN, or bank.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">04</span>
          <h3>Pay for<br/>goods.</h3>
          <p>Pay merchants, marketplaces, and suppliers by phone number, business code, or bank account.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">05</span>
          <h3>QR<br/>payments.</h3>
          <p>Pay in person with a QR code. No number typing required.</p>
        </article>
        <article class="cap-card cap-card--std">
          <span class="cap-num">06</span>
          <h3>Full<br/>history.</h3>
          <p>Every payment, filterable by rail, country, amount, and date. Exportable.</p>
        </article>
      </div>
    </div>
  </section>

  {{-- Why now --}}
  <section class="chapter">
    <div class="container container--narrow">
      <div class="chapter-grid">
        <div>
          <span class="label">Why this is possible now</span>
          <h2>You could not do<br/>this in 2020.<br/>You can in 2026.</h2>
        </div>
        <div class="chapter-body">
          <p>
            Until recently, sending money between mobile money networks in East Africa was not something a small company could build. Each telco kept its API behind a bilateral commercial agreement. That began to change in 2021 when Safaricom opened the M-PESA Daraja API to third parties, and continued through 2024 as MTN, Airtel, and Vodacom each opened their platforms.
          </p>
          <p>
            Sharet is built on that shift. We are not the first company to notice it, but we are one of the first to build a single wallet that routes across all of them — because for the first time, the integration is an engineering problem rather than a multi-year business development exercise.
          </p>
        </div>
      </div>
    </div>
  </section>

  {{-- Intake --}}
  <section id="intake" class="snug tile-section">
      <div class="container container--narrow">
          <div class="section-head">
              <div>
                  <span class="label">Join the list</span>
                  <h2>Minutes to<br/>join. Free to<br/>hold.</h2>
              </div>
              <p class="lede">
                  Sign up with your phone number. We will invite you as soon as your wallet is ready in your market. No monthly fees. No minimum balance.
              </p>
          </div>

          @include('partials.alert.flash')

          <form class="form-card" method="POST" action="{{ route('personal.signup') }}">
              @csrf

              <div class="form-row">
                  <div class="form-field">
                      <label for="p-name">Your name</label>
                      <input id="p-name" name="name" type="text" required
                            value="{{ old('name') }}"
                            placeholder="Brian Otieno" />
                      @error('name')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
                  <div class="form-field">
                      <label for="p-phone">Phone number</label>
                      <input id="p-phone" name="phone" type="tel"
                            value="{{ old('phone') }}"
                            placeholder="+254 712 XXX XXX" />
                      @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
              </div>

              <div class="form-row">
                  <div class="form-field">
                      <label for="p-email">Email</label>
                      <input id="p-email" name="email" type="email" required
                            value="{{ old('email') }}"
                            placeholder="brian @ email.com" />
                      @error('email')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
                  <div class="form-field">
                      <label for="p-country">Country</label>
                      <select id="p-country" name="country">
                          <option value="KE" @selected(old('country') === 'KE')>Kenya</option>
                          <option value="TZ" @selected(old('country') === 'TZ')>Tanzania</option>
                          <option value="UG" @selected(old('country') === 'UG')>Uganda</option>
                          <option value=""  @selected(old('country') === '')>Diaspora (outside East Africa)</option>
                      </select>
                      @error('country')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
              </div>

              <div class="form-actions">
                  <small>By submitting, you agree to Sharet's terms of service and privacy policy. We will reply to the email above.</small>
                  <button type="submit" class="btn btn--primary btn--lg">
                      Join the waiting list
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
      <h2>Send across<br/>East Africa.<br/>Cheaper. Any rail.</h2>
      <p class="lede">
        One wallet for every rail in East Africa. Zero internal fees. The recipient never needs Sharet. Sign up today.
      </p>
      <div class="cta-row">
        <a class="btn btn--dark btn--lg" href="#intake">Create your wallet
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btn btn--ghost btn--lg" href="{{ route('merchants') }}">For merchants</a>
      </div>
    </div>
  </section>

@endsection