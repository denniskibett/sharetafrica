{{-- developers: generated from the approved canvas board WebDevelopersV2 by scratchpad build_pages.py. Change the design first, then regenerate. --}}
@extends('site.layout')
@section('title', 'Sharet Developers · Build on Sharet')
@section('description', 'One API for every wallet in Kenya: JSON, idempotent writes, signed webhooks and plain errors. Sandbox access on request.')
@section('content')
<div class="ui web pg">
<div class="wr"><div class="wn"><a class="lg" href="{{ route('home') }}" aria-label="Sharet home">sharet<i>.</i></a><nav><a href="{{ route('personal') }}">Personal</a><a href="{{ route('home') }}#abroad">Abroad</a><a href="{{ route('merchants') }}">Business</a><a href="{{ route('home') }}#pricing">Pricing</a><a href="{{ route('contact') }}">Help</a></nav><a class="wb o" href="{{ route('login') }}">Log in</a><a class="wb" href="{{ route('register') }}">Open an account</a></div></div>
<div class="wr phero"><div class="split" style="align-items:center"><div><span class="lbl">DEVELOPERS · SANDBOX ON REQUEST</span><h1 class="mega pg">Build on Sharet.</h1><p class="lead">One API for every wallet in Kenya. JSON in, JSON out, idempotent writes, signed webhooks and plain errors. Start in the sandbox with simulated M-Pesa and banks.</p><div class="row2" style="margin-top:28px;flex-wrap:wrap;gap:12px"><a class="wb" href="#apply">Request sandbox access</a><a class="wb o" href="{{ route('docs') }}">Read the docs</a></div></div><div><div class="code"><span class="c"># Send KES 1,500 to an M-Pesa number</span>
<span class="m">POST</span> /v1/payments
Idempotency-Key: 9f1c2a7e-4b2d

{
  <span class="k">"amount"</span>: <span class="s">"1500.00"</span>,
  <span class="k">"currency"</span>: <span class="s">"KES"</span>,
  <span class="k">"to"</span>: { <span class="k">"type"</span>: <span class="s">"mpesa"</span>, <span class="k">"phone"</span>: <span class="s">"0722 000 904"</span> },
  <span class="k">"note"</span>: <span class="s">"Order 1042"</span>
}

<span class="c"># 202 Accepted</span>
{ <span class="k">"id"</span>: <span class="s">"pay_7Q2K81P"</span>, <span class="k">"status"</span>: <span class="s">"paying"</span>,
  <span class="k">"name"</span>: <span class="s">"WANJIKU MWANGI"</span>, <span class="k">"fee"</span>: <span class="s">"15.00"</span> }</div></div></div></div><div class="wr"><div class="stat"><div><b>REST</b><span>JSON over HTTPS</span></div><div><b>Idempotent</b><span>Safe to retry every write</span></div><div><b>HMAC</b><span>Signed webhooks</span></div><div><b>RFC 7807</b><span>Errors you can read</span></div></div></div>@if ($android = \App\Support\AndroidRelease::latest())
<div class="wr sect apks" id="android" style=""><span class="lbl">FOR TESTING ONLY</span><h2 class="h2w" style="margin-top:12px">Android test build.</h2><p class="lead">A test build of the Sharet app for testers and developers. It talks to sharet.africa in test mode, so money is simulated. It isn't the store version: your phone may ask you to allow installs from your browser.</p><div class="apkrow"><a class="badge apkb" href="{{ route('app.android') }}" download><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="#3DDC84" d="M17.6 9.48l1.84-3.18c.16-.31.04-.69-.26-.85a.64.64 0 0 0-.83.22l-1.88 3.24a11.4 11.4 0 0 0-8.94 0L5.65 5.67a.64.64 0 0 0-.87-.2c-.28.18-.37.54-.22.83L6.4 9.48A10.8 10.8 0 0 0 1 18h22a10.8 10.8 0 0 0-5.4-8.52M7 15.25a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5m10 0a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5"/></svg><span class="bl"><small>Download for Android</small>APK · {{ \App\Support\AndroidRelease::size() }}</span></a>@if (\App\Support\AndroidRelease::path('armv7'))<a class="wb o" href="{{ route('app.android', 'armv7') }}" download>Older phones (32-bit)</a>@endif<span class="sm mu">Version {{ $android['version'] }} · test mode</span></div></div>@endif
<div class="wr sect " style=""><span class="lbl" id="endpoints">ENDPOINTS · V1</span><h2 class="h2w" style="margin-top:12px">The whole surface, on one page.</h2><p class="lead">Six endpoints for paying, checking and reconciling. Sandbox keys work the same as live ones.</p><div class="eps"><div class="ep"><i class="">POST</i><code>/v1/payments</code><span>Send to M-Pesa, Airtel Money, T-Kash, a bank or a Sharet wallet. Idempotent.</span></div><div class="ep"><i class="g">GET</i><code>/v1/payments/{id}</code><span>Status, fee, recipient name and settlement time.</span></div><div class="ep"><i class="">POST</i><code>/v1/quotes</code><span>The exact fee and, for other currencies, the locked rate before you send.</span></div><div class="ep"><i class="g">GET</i><code>/v1/balance</code><span>Your wallet balance, available and held.</span></div><div class="ep"><i class="g">GET</i><code>/v1/ledger</code><span>Every entry, filterable by date, type and status. Paginated.</span></div><div class="ep"><i class="">POST</i><code>/v1/webhooks</code><span>payment.paid, payment.failed and payment.refunded, signed with HMAC-SHA256.</span></div></div></div><div class="wr sect tail"><div class="panelw brandband" style="min-height:auto;align-items:center;text-align:center;padding-block:72px"><h2 class="h2w">Ready to build?</h2><p class="lead" style="color:#C9C9C2;margin-inline:auto;text-align:center">Tell us what you're making and we'll send sandbox keys.</p><div class="row2" style="justify-content:center;flex-wrap:wrap;gap:12px"><a class="wb l" href="#apply">Request sandbox access</a></div></div></div>
<div class="wr sect" id="apply"><span class="lbl">SANDBOX ACCESS</span><h2 class="h2w" style="margin-top:12px">Tell us what you're building.</h2><p class="lead">We'll reply with sandbox keys and the docs.</p><div style="margin-top:24px"><form class="form-card sform" method="POST" action="{{ route('developers.apply') }}">
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
          </form></div></div>
<div class="foot"><div class="wr"><div class="cols"><div><span style="font:400 20px var(--f-disp);color:#fff">sharet<span style="color:var(--lime)">.</span></span><p>Payments for Kenya and East Africa. Nairobi.</p></div>
<div><b>Personal</b><a href="{{ route('home') }}#send">Send and receive</a><a href="{{ route('home') }}#everyday">Pay bills</a><a href="{{ route('home') }}#everyday">Withdraw</a><a href="{{ route('home') }}#card">Card</a><a href="{{ route('home') }}#abroad">Across borders</a></div><div><b>Business</b><a href="{{ route('merchants') }}">Merchants</a><a href="{{ route('trade') }}">Trade finance</a><a href="{{ route('developers') }}">Developers</a><a href="{{ route('docs') }}">API docs</a></div><div><b>Company</b><a href="{{ route('company') }}">About</a><a href="{{ route('blog.index') }}">Blog</a></div><div><b>Help</b><a href="{{ route('contact') }}">Help centre</a><a href="{{ route('contact') }}">Report fraud</a><a href="{{ route('privacy') }}">Privacy</a></div></div>
<p class="legal">Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. <a href="https://www.centralbank.go.ke/national-payments-system/" style="color:#D4FF3D">Check on CBK's licensed directory</a><br> Customer money is held in a separate trust account. Fees, rates and limits are shown in the app before you confirm.</p><!-- Photos (Unsplash licence, credit not required; kept here and in public/images/site/credits.json): Michael Kyule (Kilifi), Dwayne joe (Nairobi), Jonathan Ward (Kampala), Imani Bahati (Kampala), Wietse Jongsma (Entebbe), Nicholas Gray (Nairobi). --></div></div>

</div>
@endsection
