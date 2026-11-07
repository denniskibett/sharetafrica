{{-- contact: generated from the approved canvas board WebContactV2 by scratchpad build_pages.py. Change the design first, then regenerate. --}}
@extends('site.layout')
@section('title', 'Contact Sharet · Talk to a real person')
@section('description', 'Report fraud, get help with a payment, or talk to us about business, partnerships or press. Every message is read by someone in Nairobi.')
@section('content')
<div class="ui web pg">
<div class="wr"><div class="wn"><a class="lg" href="{{ route('home') }}" aria-label="Sharet home">sharet<i>.</i></a><nav><a href="{{ route('personal') }}">Personal</a><a href="{{ route('home') }}#abroad">Abroad</a><a href="{{ route('merchants') }}">Business</a><a href="{{ route('home') }}#pricing">Pricing</a><a class="on" href="{{ route('contact') }}">Help</a></nav><a class="wb o" href="{{ route('login') }}">Log in</a><a class="wb" href="{{ route('register') }}">Open an account</a></div></div>
<div class="wr phero"><div><span class="lbl">CONTACT</span><h1 class="mega pg">Talk to a real person.</h1><p class="lead">Every message is read by someone on the Sharet team in Nairobi. Pick the right door and we'll get back to you fast.</p></div></div><div class="wr"><div class="doors"><div class="door red"><span class="tile t-bad"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></span><b>Report fraud</b><span>Someone asked for your PIN, or a payment you didn't make? Tell us now and we can stop your account while we look.</span><a href="mailto:support@sharet.africa">support@sharet.africa</a></div><div class="door"><span class="tile t-send"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 .5c0 1.5-2.5 2-2.5 3.5"/><path d="M12 17h.01"/></svg></span><b>Customers</b><span>Payments, your account, verification and limits. We reply within one working day.</span><a href="mailto:support@sharet.africa">support@sharet.africa</a></div><div class="door"><span class="tile t-pay"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l2-5h14l2 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg></span><b>Businesses and partners</b><span>Business accounts, the API, and partnerships.</span><a href="mailto:hello@sharet.africa">hello@sharet.africa</a></div><div class="door"><span class="tile t-ink"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v11H8l-4 4z"/></svg></span><b>Press</b><span>Interviews, facts and figures, and brand assets.</span><a href="mailto:hello@sharet.africa">hello@sharet.africa</a></div></div></div><div class="wr sect " style=""><span class="lbl">WRITE TO US</span><h2 class="h2w" style="margin-top:12px">Tell us who you are first.</h2><p class="lead">We route your note to the right person. No newsletter, no sharing.</p><div style="margin-top:24px" id="write"><form class="form-card sform" method="POST" action="{{ route('contact.send') }}">
              @csrf
              <div class="form-status" role="status" aria-live="polite" hidden></div>

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
          </form></div></div><div class="wr sect " style="padding-top:0"><span class="lbl">VISIT</span><h2 class="h2w" style="margin-top:12px">Nairobi, Kenya.</h2><p class="lead">Sharet Africa Ltd. Meetings by appointment.</p></div>
<div class="foot"><div class="wr"><div class="cols"><div><span style="font:400 20px var(--f-disp);color:#fff">sharet<span style="color:var(--lime)">.</span></span><p>Payments for Kenya and East Africa. Nairobi.</p></div>
<div><b>Personal</b><a href="{{ route('home') }}#send">Send and receive</a><a href="{{ route('home') }}#everyday">Pay bills</a><a href="{{ route('home') }}#everyday">Withdraw</a><a href="{{ route('home') }}#card">Card</a><a href="{{ route('home') }}#abroad">Across borders</a></div><div><b>Business</b><a href="{{ route('merchants') }}">Merchants</a><a href="{{ route('trade') }}">Trade finance</a><a href="{{ route('developers') }}">Developers</a><a href="{{ route('docs') }}">API docs</a></div><div><b>Company</b><a href="{{ route('company') }}">About</a><a href="{{ route('blog.index') }}">Blog</a></div><div><b>Help</b><a href="{{ route('contact') }}">Help centre</a><a href="{{ route('contact') }}">Report fraud</a><a href="{{ route('privacy') }}">Privacy</a></div></div>
<p class="legal">Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. <a href="https://www.centralbank.go.ke/national-payments-system/" style="color:#D4FF3D">Check on CBK's licensed directory</a><br> Customer money is held in a separate trust account. Fees, rates and limits are shown in the app before you confirm.</p><!-- Photos (Unsplash licence, credit not required; kept here and in public/images/site/credits.json): Michael Kyule (Kilifi), Dwayne joe (Nairobi), Jonathan Ward (Kampala), Imani Bahati (Kampala), Wietse Jongsma (Entebbe), Nicholas Gray (Nairobi). --></div></div>

</div>
@endsection
