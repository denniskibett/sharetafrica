{{-- privacy: generated from the approved canvas board WebPrivacyV2 by scratchpad build_pages.py. Change the design first, then regenerate. --}}
@extends('site.layout')
@section('title', 'Sharet · Privacy — What we collect and why')
@section('description', 'How Sharet Africa collects, uses, protects and shares personal data, and your rights under the Kenya Data Protection Act, 2019.')
@section('content')
<div class="ui web pg">
<div class="wr"><div class="wn"><a class="lg" href="{{ route('home') }}" aria-label="Sharet home">sharet<i>.</i></a><nav><a href="{{ route('personal') }}">Personal</a><a href="{{ route('home') }}#abroad">Abroad</a><a href="{{ route('merchants') }}">Business</a><a href="{{ route('home') }}#pricing">Pricing</a><a href="{{ route('contact') }}">Help</a></nav><a class="wb o" href="{{ route('login') }}">Log in</a><a class="wb" href="{{ route('register') }}">Open an account</a></div></div>
<div class="wr phero"><span class="lbl">PRIVACY · UPDATED 31 OCTOBER 2026</span><h1 class="mega pg">Your data, plainly.</h1><p class="lead">What Sharet Africa collects, why, who sees it, how long we keep it, and how to ask us to change or delete it.</p></div><div class="wr sect" style="padding-top:32px"><div class="doc"><nav class="toc"><a class="on" href="#p0">Who we are</a><a class="" href="#p1">What we collect</a><a class="" href="#p2">Why we use it</a><a class="" href="#p3">Who we share it with</a><a class="" href="#p4">How long we keep it</a><a class="" href="#p5">Your rights</a><a class="" href="#p6">Keeping it safe</a><a class="" href="#p7">Changes</a></nav><div><div class="tldr"><b>In short</b><p>We collect what we need to run your account and stop fraud. We never sell it. Website use is anonymous, with no cookies. You can ask to see, correct or delete your data.</p></div>
      <h2 id="p0">Who we are</h2>
      <p>Sharet Africa ("Sharet", "we") runs sharet.africa and the Sharet dashboard from Nairobi, Kenya. We are the data controller for the personal data described here. Contact us about privacy at <a href="mailto:hello&#64;sharet.africa">hello&#64;sharet.africa</a>.</p>

      <h2 id="p1">What we collect</h2>
      <ul>
        <li><strong>Account details</strong>: your name, email, M-Pesa number and password (stored only as a one-way hash).</li>
        <li><strong>Verification details</strong>, when you choose bank-grade verification: ID type and number, date of birth, KRA PIN, address, occupation, income band, source of funds, next of kin, photos of your ID, three selfies and your signature. These are encrypted, kept on private storage, and seen only by our compliance team.</li>
        <li><strong>Sign-up and verification records</strong>: when you create an account or send verification details, we record the IP address, approximate country, browser and device description, browser languages, the page used and the time. We use these to prevent fraud and identity theft and to meet our anti-money-laundering duties. They are encrypted with your verification details and seen only by our compliance team. (This is separate from website use below, which stays anonymous.)</li>
        <li><strong>Business details</strong>, if you register a business: its registration, tax and contact details, its directors and owners, and its settlement account.</li>
        <li><strong>Messages</strong> you send through our forms, and our replies.</li>
        <li><strong>Payments</strong> you make or receive through your Sharet wallet.</li>
        <li><strong>Website use</strong>: which pages are viewed, how far down people scroll and where they click. We do this without cookies, without storing IP addresses and without third-party trackers; the anonymous visitor code changes every day. If your browser sends "Do Not Track" or "Global Privacy Control", we record nothing.</li>
      </ul>

      <h2 id="p2">Why we use it</h2>
      <ul>
        <li>To open and run your account and move money you ask us to move.</li>
        <li>To verify who you are and prevent fraud and money laundering, as the law requires of payment providers.</li>
        <li>To answer you, and to send account emails (sign-up, invitations, receipts). We do not send marketing without your consent.</li>
        <li>To understand which pages help people and improve the site.</li>
      </ul>

      <h2 id="p3">Who we share it with</h2>
      <p>Only who we must, to do what you ask: the mobile money and banking partners that carry a payment (for example Safaricom M-Pesa), our hosting and email providers acting on our instructions, and regulators or law enforcement when the law requires it. We do not sell personal data.</p>

      <h2 id="p4">How long we keep it</h2>
      <p>Account and verification records are kept while your account is open and afterwards for as long as financial and anti-money-laundering law requires. Website analytics are anonymous. Messages are kept while they are useful to answer you.</p>

      <h2 id="p5">Your rights</h2>
      <p>Under the Kenya Data Protection Act, 2019 you can ask to see the data we hold about you, correct it, object to some uses, ask us to delete it where the law allows, and complain to the Office of the Data Protection Commissioner. Write to <a href="mailto:hello&#64;sharet.africa">hello&#64;sharet.africa</a>; we answer within the time the law sets.</p>

      <h2 id="p6">Keeping it safe</h2>
      <p>Connections are encrypted (HTTPS). Passwords are hashed, API keys are stored only as fingerprints, and verification details, documents and provider credentials are encrypted at rest. Access inside Sharet is limited by role.</p>

      <h2 id="p7">Changes</h2>
      <p>When this notice changes we update the date at the top, and tell account holders by email about any change that matters to them.</p>
    </div></div></div>
<div class="foot"><div class="wr"><div class="cols"><div><span style="font:400 20px var(--f-disp);color:#fff">sharet<span style="color:var(--lime)">.</span></span><p>Payments for Kenya and East Africa. Nairobi.</p></div>
<div><b>Personal</b><a href="{{ route('home') }}#send">Send and receive</a><a href="{{ route('home') }}#everyday">Pay bills</a><a href="{{ route('home') }}#everyday">Withdraw</a><a href="{{ route('home') }}#card">Card</a><a href="{{ route('home') }}#abroad">Across borders</a></div><div><b>Business</b><a href="{{ route('merchants') }}">Merchants</a><a href="{{ route('trade') }}">Trade finance</a><a href="{{ route('developers') }}">Developers</a><a href="{{ route('docs') }}">API docs</a></div><div><b>Company</b><a href="{{ route('company') }}">About</a><a href="{{ route('blog.index') }}">Blog</a></div><div><b>Help</b><a href="{{ route('contact') }}">Help centre</a><a href="{{ route('contact') }}">Report fraud</a><a href="{{ route('privacy') }}">Privacy</a></div></div>
<p class="legal">Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. <a href="https://www.centralbank.go.ke/national-payments-system/" style="color:#D4FF3D">Check on CBK's licensed directory</a><br> Customer money is held in a separate trust account. Fees, rates and limits are shown in the app before you confirm.</p><!-- Photos (Unsplash licence, credit not required; kept here and in public/images/site/credits.json): Michael Kyule (Kilifi), Dwayne joe (Nairobi), Jonathan Ward (Kampala), Imani Bahati (Kampala), Wietse Jongsma (Entebbe), Nicholas Gray (Nairobi). --></div></div>

</div>
@endsection
