{{-- Privacy notice. Describes what this website and dashboard actually collect and why. Have it reviewed by counsel
     before relying on it; update it whenever a new kind of data is collected. --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Privacy — What we collect and why')
@section('description', 'How Sharet Africa collects, uses, protects and shares personal data, and your rights under the Kenya Data Protection Act, 2019.')

@section('content')
  <section class="hero hero--compact">
    <div class="container container--narrow">
      <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Privacy · updated 31 October 2026</span>
      <h1 class="article-title">Your data, plainly.</h1>
      <p class="hero-sub">What Sharet Africa collects, why, who sees it, how long we keep it, and how to ask us to change or delete it.</p>
    </div>
  </section>

  <article class="snug">
    <div class="container container--narrow article-body">
      <h2>Who we are</h2>
      <p>Sharet Africa ("Sharet", "we") runs sharet.africa and the Sharet dashboard from Nairobi, Kenya. We are the data controller for the personal data described here. Contact us about privacy at <a href="mailto:hello@sharet.africa">hello@sharet.africa</a>.</p>

      <h2>What we collect</h2>
      <ul>
        <li><strong>Account details</strong>: your name, email, M-Pesa number and password (stored only as a one-way hash).</li>
        <li><strong>Verification details</strong>, when you choose bank-grade verification: ID type and number, date of birth, KRA PIN, address, occupation, income band, source of funds, next of kin, photos of your ID, three selfies and your signature. These are encrypted, kept on private storage, and seen only by our compliance team.</li>
        <li><strong>Business details</strong>, if you register a business: its registration, tax and contact details, its directors and owners, and its settlement account.</li>
        <li><strong>Messages</strong> you send through our forms, and our replies.</li>
        <li><strong>Payments</strong> you make or receive through your Sharet wallet.</li>
        <li><strong>Website use</strong>: which pages are viewed, how far down people scroll and where they click. We do this without cookies, without storing IP addresses and without third-party trackers; the anonymous visitor code changes every day. If your browser sends "Do Not Track" or "Global Privacy Control", we record nothing.</li>
      </ul>

      <h2>Why we use it</h2>
      <ul>
        <li>To open and run your account and move money you ask us to move.</li>
        <li>To verify who you are and prevent fraud and money laundering, as the law requires of payment providers.</li>
        <li>To answer you, and to send account emails (sign-up, invitations, receipts). We do not send marketing without your consent.</li>
        <li>To understand which pages help people and improve the site.</li>
      </ul>

      <h2>Who we share it with</h2>
      <p>Only who we must, to do what you ask: the mobile money and banking partners that carry a payment (for example Safaricom M-Pesa), our hosting and email providers acting on our instructions, and regulators or law enforcement when the law requires it. We do not sell personal data.</p>

      <h2>How long we keep it</h2>
      <p>Account and verification records are kept while your account is open and afterwards for as long as financial and anti-money-laundering law requires. Website analytics are anonymous. Messages are kept while they are useful to answer you.</p>

      <h2>Your rights</h2>
      <p>Under the Kenya Data Protection Act, 2019 you can ask to see the data we hold about you, correct it, object to some uses, ask us to delete it where the law allows, and complain to the Office of the Data Protection Commissioner. Write to <a href="mailto:hello@sharet.africa">hello@sharet.africa</a>; we answer within the time the law sets.</p>

      <h2>Keeping it safe</h2>
      <p>Connections are encrypted (HTTPS). Passwords are hashed, API keys are stored only as fingerprints, and verification details, documents and provider credentials are encrypted at rest. Access inside Sharet is limited by role.</p>

      <h2>Changes</h2>
      <p>When this notice changes we update the date at the top, and tell account holders by email about any change that matters to them.</p>
    </div>
  </article>
@endsection
