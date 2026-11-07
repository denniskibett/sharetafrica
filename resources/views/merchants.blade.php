{{-- merchants: generated from the approved canvas board WebBusinessV2 by scratchpad build_pages.py. Change the design first, then regenerate. --}}
@extends('site.layout')
@section('title', 'Sharet for Business · Get paid on every wallet')
@section('description', 'One till for M-Pesa, Airtel Money, T-Kash and Sharet, settled to your bank every evening. Business accounts open after the Kenya pilot: join the waiting list.')
@section('content')
<div class="ui web pg">
<div class="wr"><div class="wn"><a class="lg" href="{{ route('home') }}" aria-label="Sharet home">sharet<i>.</i></a><nav><a href="{{ route('personal') }}">Personal</a><a href="{{ route('home') }}#abroad">Abroad</a><a class="on" href="{{ route('merchants') }}">Business</a><a href="{{ route('home') }}#pricing">Pricing</a><a href="{{ route('contact') }}">Help</a></nav><a class="wb o" href="{{ route('login') }}">Log in</a><a class="wb" href="{{ route('register') }}">Open an account</a></div></div>
<div class="wr phero"><div class="split" style="align-items:center"><div><span class="lbl">BUSINESS · COMING SOON</span><h1 class="mega pg">Get paid on every wallet.<br>Settle to your bank.</h1><p class="lead">One till for M-Pesa, Airtel Money, T-Kash and Sharet. One balance, one dashboard, settled to your bank every evening. Business accounts open after the personal pilot.</p><div class="row2" style="margin-top:28px;flex-wrap:wrap;gap:12px"><a class="wb" href="#apply">Join the business waiting list</a><span class="soon"><i></i>Opening after the Kenya pilot</span></div></div><div class="panelw ph-pay" style="color:#fff"><div></div><div class="mini" style="max-width:340px;align-self:center;width:100%;position:relative;gap:12px"><div class="row2" style="align-items:center"><span class="av" style="background:var(--ink);color:#fff">MH</span><span class="grow"><b>Mwangi Hardware</b><br><span class="sm mu">Today · 312 payments</span></span><span class="pill p-ok">LIVE</span></div><div class="amt s">KES 184,350</div><div class="bars" aria-hidden="true"><i style="height:38%"></i><i style="height:52%"></i><i style="height:44%"></i><i style="height:70%"></i><i style="height:58%"></i><i style="height:86%"></i><i style="height:100%"></i></div><div class="kv" style="border:0;padding:0"><span>Settles to Equity ••• 4410</span><span>17:00</span></div></div></div></div></div><div class="wr sect " style=""><span class="lbl">WHAT YOU GET</span><h2 class="h2w" style="margin-top:12px">Everything a shop needs to get paid.</h2><div class="tiles c3"><div class="ft"><span class="tile t-pay"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l2-5h14l2 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg></span><b>One till, every wallet</b><span>Customers pay with M-Pesa, Airtel Money, T-Kash or Sharet. It all lands in one balance.</span></div><div class="ft"><span class="tile t-out"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10h18"/><path d="M12 3l9 5H3z"/><path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8"/><path d="M3 21h18"/></svg></span><b>Settled every evening</b><span>Automatically to your bank at 17:00, or whenever you tap Settle.</span></div><div class="ft"><span class="tile t-in"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></span><b>One dashboard</b><span>Every payment, every wallet, reconciled. Export to your accounts in one click.</span></div><div class="ft"><span class="tile t-send"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14a5 5 0 0 1 6 5"/></svg></span><b>Staff and roles</b><span>Cashiers take payments; only owners move money out.</span></div><div class="ft"><span class="tile t-lime"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7"/><path d="M8 7h9v9"/></svg></span><b>Pay suppliers free</b><span>Free to other Sharet businesses; a small fee to M-Pesa or bank.</span></div><div class="ft"><span class="tile t-abroad"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18"/></svg></span><b>Trade finance</b><span>Get suppliers paid now, pay in 60 days, with Aimzone Ventures.</span><em>SOON</em></div></div></div><div class="wr sect " style=""><span class="lbl">HOW IT WORKS</span><h2 class="h2w" style="margin-top:12px">From sign-up to your first payment.</h2><ol class="num"><li><b>Join the waiting list</b><span>Your business name, registration and contact.</span></li><li><b>We verify</b><span>Directors, KRA PIN and settlement account, usually in a working day.</span></li><li><b>Your till goes live</b><span>A Sharet till number and QR code for the counter.</span></li><li><b>Settle and reconcile</b><span>Daily to your bank, with reports ready for your accountant.</span></li></ol></div><div class="wr sect tail"><div class="panelw brandband" style="min-height:auto;align-items:center;text-align:center;padding-block:72px"><h2 class="h2w">Be one of the first Sharet businesses.</h2><p class="lead" style="color:#C9C9C2;margin-inline:auto;text-align:center">We'll invite businesses in the order they join, once the personal pilot is done.</p><div class="row2" style="justify-content:center;flex-wrap:wrap;gap:12px"><a class="wb l" href="#apply">Join the business waiting list</a></div></div></div>
<div class="wr sect" id="apply"><span class="lbl">BUSINESS WAITING LIST</span><h2 class="h2w" style="margin-top:12px">Join the business waiting list.</h2><p class="lead">We invite businesses in the order they join, once the personal pilot is done.</p><div style="margin-top:24px"><form class="form-card sform" method="POST" action="{{ route('merchants.signup') }}">
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
        </form></div></div>
<div class="foot"><div class="wr"><div class="cols"><div><span style="font:400 20px var(--f-disp);color:#fff">sharet<span style="color:var(--lime)">.</span></span><p>Payments for Kenya and East Africa. Nairobi.</p></div>
<div><b>Personal</b><a href="{{ route('home') }}#send">Send and receive</a><a href="{{ route('home') }}#everyday">Pay bills</a><a href="{{ route('home') }}#everyday">Withdraw</a><a href="{{ route('home') }}#card">Card</a><a href="{{ route('home') }}#abroad">Across borders</a></div><div><b>Business</b><a href="{{ route('merchants') }}">Merchants</a><a href="{{ route('trade') }}">Trade finance</a><a href="{{ route('developers') }}">Developers</a></div><div><b>Company</b><a href="{{ route('company') }}">About</a><a href="{{ route('blog.index') }}">Blog</a></div><div><b>Help</b><a href="{{ route('contact') }}">Help centre</a><a href="{{ route('contact') }}">Report fraud</a><a href="{{ route('privacy') }}">Privacy</a></div></div>
<p class="legal">Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. <a href="https://www.centralbank.go.ke/national-payments-system/" style="color:#D4FF3D">Check on CBK's licensed directory</a><br> Customer money is held in a separate trust account. Fees, rates and limits are shown in the app before you confirm.</p><!-- Photos (Unsplash licence, credit not required; kept here and in public/images/site/credits.json): Michael Kyule (Kilifi), Dwayne joe (Nairobi), Jonathan Ward (Kampala), Imani Bahati (Kampala), Wietse Jongsma (Entebbe), Nicholas Gray (Nairobi). --></div></div>

</div>
@endsection
