{{-- trade: generated from the approved canvas board WebTradeV2 by scratchpad build_pages.py. Change the design first, then regenerate. --}}
@extends('site.layout')
@section('title', 'Sharet Trade Finance · Pay suppliers now, pay back in 60 days')
@section('description', 'Trade finance for Kenyan importers and suppliers, with Aimzone Ventures as funding partner. Coming after business accounts open.')
@section('content')
<div class="ui web pg">
<div class="wr"><div class="wn"><a class="lg" href="{{ route('home') }}" aria-label="Sharet home">sharet<i>.</i></a><nav><a href="{{ route('personal') }}">Personal</a><a href="{{ route('home') }}#abroad">Abroad</a><a class="on" href="{{ route('merchants') }}">Business</a><a href="{{ route('home') }}#pricing">Pricing</a><a href="{{ route('contact') }}">Help</a></nav><a class="wb o" href="{{ route('login') }}">Log in</a><a class="wb" href="{{ route('register') }}">Open an account</a></div></div>
<div class="wr phero"><div class="split" style="align-items:center"><div><span class="lbl">TRADE · COMING SOON</span><h1 class="mega pg">Pay suppliers now.<br>Pay us back in 60 days.</h1><p class="lead">Trade finance for Kenyan importers and suppliers, with Aimzone Ventures as funding partner. Get your supplier paid today against a purchase order, and repay when your goods sell.</p><div class="row2" style="margin-top:28px;flex-wrap:wrap;gap:12px"><a class="wb" href="#apply">Apply for trade finance</a><span class="soon"><i></i>After business accounts open</span></div></div><div class="panelw ph-trade"><div></div><div class="mini" style="max-width:340px;align-self:center;width:100%;position:relative"><b>LPO-77412 · Naivas</b><div class="kv" style="border:0;padding:0"><span>Advance</span><span>KES 960,000</span></div><div class="kv" style="border:0;padding:0"><span>Repay in 60 days</span><span>KES 993,600</span></div><span class="pill p-ok">SUPPLIER PAID</span></div></div></div></div><div class="wr sect " style=""><span class="lbl">HOW IT WORKS</span><h2 class="h2w" style="margin-top:12px">From purchase order to paid supplier.</h2><ol class="num"><li><b>Upload the order</b><span>An LPO from a buyer like Naivas, or an invoice from your supplier.</span></li><li><b>We check it</b><span>The buyer, the amounts and your history with Sharet.</span></li><li><b>Your supplier is paid</b><span>Straight to their M-Pesa, bank or by SWIFT abroad.</span></li><li><b>You repay in 60 days</b><span>3.5% for 60 days, shown before you accept. Late days add 0.1% a day.</span></li></ol></div><div class="wr sect " style=""><span class="lbl">WHAT'S INCLUDED</span><h2 class="h2w" style="margin-top:12px">Built for traders.</h2><div class="tiles c3"><div class="ft"><span class="tile t-abroad"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18"/></svg></span><b>Pay abroad by SWIFT</b><span>Suppliers in China, the UAE and India, with the rate locked before you pay.</span></div><div class="ft"><span class="tile t-ink"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6v18h12V7z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 17h6"/></svg></span><b>Documents in one place</b><span>Orders, invoices and receipts kept with each payment.</span></div><div class="ft"><span class="tile t-ink"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg></span><b>Two approvers</b><span>Payments over KES 500,000 need a second person to approve.</span></div></div></div><div class="wr sect tail"><div class="panelw brandband" style="min-height:auto;align-items:center;text-align:center;padding-block:72px"><h2 class="h2w">Trade finance is coming.</h2><p class="lead" style="color:#C9C9C2;margin-inline:auto;text-align:center">Join the business waiting list to hear first.</p><div class="row2" style="justify-content:center;flex-wrap:wrap;gap:12px"><a class="wb l" href="#apply">Apply for trade finance</a></div></div></div>
<div class="wr sect" id="apply"><span class="lbl">TRADE FINANCE</span><h2 class="h2w" style="margin-top:12px">Apply, or join the list.</h2><p class="lead">Tell us what you trade and where. We'll be in touch when trade finance opens.</p><div style="margin-top:24px"><form class="form-card sform" method="POST" action="{{ route('trade.apply') }}">
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
        </form></div></div>
<div class="foot"><div class="wr"><div class="cols"><div><span style="font:400 20px var(--f-disp);color:#fff">sharet<span style="color:var(--lime)">.</span></span><p>Payments for Kenya and East Africa. Nairobi.</p></div>
<div><b>Personal</b><a href="{{ route('home') }}#send">Send and receive</a><a href="{{ route('home') }}#everyday">Pay bills</a><a href="{{ route('home') }}#everyday">Withdraw</a><a href="{{ route('home') }}#card">Card</a><a href="{{ route('home') }}#abroad">Across borders</a></div><div><b>Business</b><a href="{{ route('merchants') }}">Merchants</a><a href="{{ route('trade') }}">Trade finance</a><a href="{{ route('developers') }}">Developers</a></div><div><b>Company</b><a href="{{ route('company') }}">About</a><a href="{{ route('blog.index') }}">Blog</a></div><div><b>Help</b><a href="{{ route('contact') }}">Help centre</a><a href="{{ route('contact') }}">Report fraud</a><a href="{{ route('privacy') }}">Privacy</a></div></div>
<p class="legal">Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. <a href="https://www.centralbank.go.ke/national-payments-system/" style="color:#D4FF3D">Check on CBK's licensed directory</a><br> Customer money is held in a separate trust account. Fees, rates and limits are shown in the app before you confirm.</p><!-- Photos (Unsplash licence, credit not required; kept here and in public/images/site/credits.json): Michael Kyule (Kilifi), Dwayne joe (Nairobi), Jonathan Ward (Kampala), Imani Bahati (Kampala), Wietse Jongsma (Entebbe), Nicholas Gray (Nairobi). --></div></div>

</div>
@endsection
