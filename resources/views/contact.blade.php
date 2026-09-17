{{-- resources/views/home/pages/contact.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Contact — Start a project, press, careers')
@section('description', 'Three contact channels for Sharet — merchant onboarding, personal wallets, trade accounts, developer access, press, and careers. Team replies within 48 working hours.')

@section('content')

  {{-- Hero --}}
  <section class="hero">
    <div class="container container--wide">
      <div class="hero-grid">
        <div class="hero-text">
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Reply within 48 working hours</span>
          <h1 class="hero-headline">
            Send a real<br/>
            <span class="lime">first note,</span> get<br/>
            a real reply.
          </h1>
          <p class="hero-sub">
            Every note to Sharet is read by a real person on the team — not an inbox bot. Tell us who you are, what you do, and which page brought you here. We will route it to the right person and reply within 48 working hours.
          </p>
          <div class="hero-cta-row">
            <a class="btn btn--primary btn--lg" href="#intake">Start here</a>
            <a class="btn btn--ghost btn--lg" href="#channels">Other channels</a>
          </div>
          <div class="hero-meta">
            <span><strong>48hr</strong> · target reply</span>
            <span aria-hidden="true">·</span>
            <span><strong>3</strong> · offices in East Africa</span>
            <span aria-hidden="true">·</span>
            <span><strong>1</strong> · ledger</span>
          </div>
        </div>
        <div class="hero-media">
          <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80" alt="A Sharet operations desk with a laptop, a reconciliation sheet, and a Nairobi coffee cup." />
          <div class="floating-tag ft-top">
            <span class="pill">Inbox</span>
            HELLO @ SHARET.AFRICA
          </div>
          <div class="floating-tag ft-bottom">
            NAIROBI · KAMPALA · DAR ES SALAAM
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Channels --}}
  <section id="channels">
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="label">Three channels</span>
          <h2>Pick the right<br/>door to come<br/>through.</h2>
        </div>
        <p class="lede">
          Onboarding enquiries land with the platform team. Press and media land with communications. Careers land with operations. Every channel is a real inbox watched by a real person at Sharet.
        </p>
      </div>

      <div class="channel-grid">
        <article class="channel-card">
          <span class="label">01 / Onboarding</span>
          <h3>For merchants, individuals &amp; traders.</h3>
          <p>The right door for merchants, personal wallets, trade accounts, and platform integrations. Tell us which audience you are, and we will route it.</p>
          <a class="channel-line" href="mailto:hello@sharet.africa">hello @ sharet.africa</a>
          <span class="mono" style="color: var(--fg-mute);">Platform team · 48hr reply target</span>
        </article>

        <article class="channel-card" id="press">
          <span class="label label--lime">02 / Press &amp; media</span>
          <h3>For journalists.</h3>
          <p>Interviews, image licensing, brand assets, commentary on African payments infrastructure, and quotes for stories on fintech and mobile money.</p>
          <a class="channel-line" href="mailto:press@sharet.africa">press @ sharet.africa</a>
          <span class="mono" style="color: var(--fg-mute);">Communications lead · 5-day reply target</span>
        </article>

        <article class="channel-card" id="careers">
          <span class="label">03 / Careers</span>
          <h3>For job-seekers.</h3>
          <p>Hiring across engineering, treasury, compliance, and partnerships — with a preference for people who have shipped payments infrastructure in East Africa.</p>
          <a class="channel-line" href="mailto:careers@sharet.africa">careers @ sharet.africa</a>
          <span class="mono" style="color: var(--fg-mute);">Operations lead · 7–14 day reply window</span>
        </article>
      </div>
    </div>
  </section>

  {{-- Intake --}}
  <section id="intake">
      <div class="container container--narrow">
          <div class="section-head">
              <div>
                  <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Intake</span>
                  <h2>Tell us who<br/>you are first.</h2>
              </div>
              <p class="lede">
                  Pick the audience that fits you best. The form will route your note to the right person. Every note goes to a real person on the team.
              </p>
          </div>

          <form class="form-card" method="POST" action="{{ route('contact.send') }}">
              @csrf

              <div class="form-row form-row--full">
                  <div class="form-field">
                      <label for="c-audience">I am a…</label>
                      <select id="c-audience" name="audience" required>
                          <option value="">Select one</option>
                          <option value="merchant"    @selected(old('audience') === 'merchant')>Merchant — I want to accept payments from any rail</option>
                          <option value="individual"  @selected(old('audience') === 'individual')>Individual — I want to send money across East Africa</option>
                          <option value="trader"      @selected(old('audience') === 'trader')>Trader — I import or export and need to settle across borders</option>
                          <option value="developer"   @selected(old('audience') === 'developer')>Developer or platform — I want to build on the Sharet API</option>
                          <option value="institution" @selected(old('audience') === 'institution')>Institution — I want to license the Sharet stack</option>
                          <option value="press"       @selected(old('audience') === 'press')>Press — I am a journalist or media outlet</option>
                          <option value="career"      @selected(old('audience') === 'career')>Candidate — I am looking for a job at Sharet</option>
                          <option value="other"       @selected(old('audience') === 'other')>Something else</option>
                      </select>
                      @error('audience')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
              </div>

              <div class="form-row">
                  <div class="form-field">
                      <label for="c-name">Your name</label>
                      <input id="c-name" name="name" type="text" required
                            value="{{ old('name') }}" placeholder="Amina Wanjiru" />
                      @error('name')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
                  <div class="form-field">
                      <label for="c-business">Business / project (if relevant)</label>
                      <input id="c-business" name="business" type="text"
                            value="{{ old('business') }}" placeholder="Nairobi Events Co." />
                  </div>
              </div>

              <div class="form-row">
                  <div class="form-field">
                      <label for="c-email">Email</label>
                      <input id="c-email" name="email" type="email" required
                            value="{{ old('email') }}" placeholder="amina @ nairobievents.co.ke" />
                      @error('email')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
                  <div class="form-field">
                      <label for="c-phone">Phone number (optional)</label>
                      <input id="c-phone" name="phone" type="tel"
                            value="{{ old('phone') }}" placeholder="+254 712 XXX XXX" />
                  </div>
              </div>

              <div class="form-row form-row--full">
                  <div class="form-field">
                      <label for="c-country">Country</label>
                      <input id="c-country" name="country" type="text"
                            value="{{ old('country') }}" placeholder="Kenya" />
                  </div>
              </div>

              <div class="form-row form-row--full">
                  <div class="form-field">
                      <label for="c-brief">What brings you here?</label>
                      <textarea id="c-brief" name="brief" required
                                placeholder="One paragraph is enough. Tell us what you do, what you need, and where the pain is today.">{{ old('brief') }}</textarea>
                      @error('brief')<span class="form-error">{{ $message }}</span>@enderror
                  </div>
              </div>

              <div class="form-actions">
                  <small>By submitting, you agree we will reply to the email above. No newsletter signup. No third-party sharing.</small>
                  <button type="submit" class="btn btn--primary btn--lg">
                      Send first note
                      <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
              </div>
          </form>
      </div>
  </section>

  {{-- Locations --}}
  <section class="snug tile-section">
    <div class="container container--wide">
      <div class="split">
        <div class="split-text">
          <span class="eyebrow eyebrow--on-tile"><span class="dot" aria-hidden="true"></span>Three offices</span>
          <h2>Nairobi.<br/>Kampala.<br/>Dar es Salaam.</h2>
          <p>
            Our offices in Nairobi, Kampala, and Dar es Salaam are working spaces — not showrooms. If you would like to visit, write to the platform team at <strong>hello@sharet.africa</strong> with a working window and a reason, and we will schedule time with the right people.
          </p>
          <ul class="split-fact-list">
            <li><b>Nairobi</b><span>Headquarters · Westlands · engineering, treasury, compliance</span></li>
            <li><b>Kampala</b><span>Regional operations · Uganda market</span></li>
            <li><b>Dar es Salaam</b><span>Regional operations · Tanzania market</span></li>
            <li><b>Hours</b><span>Mon–Fri 0900–1800 EAT · public holidays closed</span></li>
          </ul>
          <a class="btn btn--ghost-on-tile btn--lg" href="#intake">Send a first note
            <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        </div>
        <div class="split-img">
          <img src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?auto=format&fit=crop&w=1000&q=80" alt="A map of East Africa with Sharet's three offices marked." />
          <span class="ft-corner">Nairobi · Kampala · Dar es Salaam</span>
        </div>
      </div>
    </div>
  </section>

  {{-- Press kit --}}
  <section>
    <div class="container container--wide">
      <div class="section-head">
        <div>
          <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Press kit</span>
          <h2>Everything a<br/>journalist needs<br/>in one folder.</h2>
        </div>
        <p class="lede">
          Founder portraits, brand assets, a 600-word boilerplate, and a usage agreement. Drop a note to <strong>press@sharet.africa</strong> if you would like a passworded archive link to past releases, commentary, or data.
        </p>
      </div>

      <div class="channel-grid">
        <article class="channel-card">
          <span class="label">Press pack · ZIP</span>
          <h3>Brand assets.</h3>
          <p>Sharet logo (SVG, PNG), founder portraits, office imagery, a one-liner, and a 600-word boilerplate. Updated quarterly.</p>
          <a class="channel-line" href="#">Download press pack ↓</a>
          <span class="mono" style="color: var(--fg-mute);">Last updated · Sep 2026</span>
        </article>
        <article class="channel-card">
          <span class="label">Bio · founders</span>
          <h3>Founder bios.</h3>
          <p>Short and long-form bios for the founding team, available in English and Swahili. Quotation rights granted on request.</p>
          <a class="channel-line" href="#">Read founder bios →</a>
          <span class="mono" style="color: var(--fg-mute);">EN · SW · last reviewed Sep 2026</span>
        </article>
        <article class="channel-card">
          <span class="label">Commentary</span>
          <h3>Quotes &amp; data.</h3>
          <p>Commentary on African payments infrastructure, mobile money, and cross-border settlement. On-record quotes available on request.</p>
          <a class="channel-line" href="mailto:press@sharet.africa">press @ sharet.africa</a>
          <span class="mono" style="color: var(--fg-mute);">Communications lead · always-on</span>
        </article>
      </div>
    </div>
  </section>

  {{-- Closing --}}
  <section class="closing-cta">
    <div class="container container--narrow">
      <span class="label" style="color: rgba(10,10,12,0.6);">Final note</span>
      <h2>Send a real note.<br/>We will send a<br/>real one back.</h2>
      <p class="lede">
        We do not use intake forms to filter people out. We use them to read the brief faster. If a form is the wrong shape for your project, write directly — every channel above is a real inbox watched by a real person at Sharet.
      </p>
      <div class="cta-row">
        <a class="btn btn--dark btn--lg" href="#intake">Open the form
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a class="btn btn--ghost btn--lg" href="mailto:hello@sharet.africa">Or write directly</a>
      </div>
    </div>
  </section>

@endsection