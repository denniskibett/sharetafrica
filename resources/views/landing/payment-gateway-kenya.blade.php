{{-- /payment-gateway-kenya: "payment gateway Kenya", "accept M-Pesa payments online". Facts from /docs only (no card acceptance claimed). --}}
@extends('landing.layout')
@section('title', 'Payment gateway for Kenya · Accept M-PESA payments online · Sharet')
@section('description', 'Accept M-PESA payments on your website or app with Sharet: hosted pay links, an M-PESA prompt to the customer\'s phone, Sharet wallet payments, webhooks and split settlement. Sandbox first.')
@php
    $lp = [
        'key' => 'gateway',
        'crumb' => 'Payment gateway Kenya',
        'label' => 'PAYMENT GATEWAY · KENYA',
        'h1' => 'Accept M-PESA payments online.',
        'lead' => 'Sharet is a payment gateway for Kenyan businesses that sell online. Create a pay link from your checkout, your customer pays by M-PESA prompt or from a Sharet wallet, and the money lands in one balance you can split, settle and pay out from.',
        'docs' => '#quickstart',
        'code' => '<span class="c"># Your checkout creates a pay link…</span>
<span class="m">POST</span> /api/v1/payment-requests
{ <span class="k">"amount"</span>: <span class="s">"4200.00"</span>, <span class="k">"reference"</span>: <span class="s">"Cart 88213"</span>,
  <span class="k">"expires_in_minutes"</span>: 60 }

<span class="c"># …sends the customer to pay_url…</span>
{ <span class="k">"pay_url"</span>: <span class="s">"https://sharet.africa/r/…"</span> }

<span class="c"># …and your server hears back</span>
<span class="m">POST</span> https://shop.example/hooks/sharet
Sharet-Signature: t=…,v1=…
{ <span class="k">"type"</span>: <span class="s">"payment_request.paid"</span>, … }',
        'stats' => ['M-PESA' => 'Prompt to any Kenyan phone', 'Sharet wallet' => 'Free, approved with a PIN', 'Splits' => 'Up to 10 legs, all or nothing', 'One balance' => 'Collect, split, pay out'],
        'steps_title' => 'Add M-PESA checkout to your site.',
        'steps' => [
            '<b>Request sandbox keys</b> and build against <code>sk_test_</code>: simulated money, the same endpoints as live.',
            '<b>At checkout, create a payment request</b> with the order total and reference, and send the customer to its <code>pay_url</code>.',
            '<b>The customer pays</b> by M-PESA (a prompt to their phone, approved with their M-PESA PIN) or from their Sharet wallet.',
            '<b>Fulfil the order</b> when <code>payment_request.paid</code> reaches your webhook. Split it to partners automatically, or pay out to M-PESA or a bank when you are ready.',
        ],
        'features_title' => 'A gateway built for how Kenya pays.',
        'features' => [
            'Hosted pay page' => 'No payment form to build or secure. The pay page handles the M-PESA prompt and Sharet wallet payments and tells the customer what happened.',
            'Split settlement' => 'Add <code>splits</code> to a request and, once it is paid, every leg goes to its Sharet account together or not at all. Good for marketplaces, landlords and agents.',
            'Connected accounts' => 'Platforms can collect and settle for their own users, in the users\' Sharet accounts, with consent approved by PIN and revocable at any time.',
            'Fees before money moves' => 'Sharet to Sharet is free. Every quote and payment shows the fee and total before anything is paid.',
            'Webhooks and retries' => 'Signed events for paid and expired requests, payouts and settlements, retried for 24 hours.',
            'Plain errors' => 'Problem JSON with a stable <code>type</code>, listed in the docs, so your checkout can say exactly what went wrong.',
        ],
        'faq_title' => 'Payment gateway questions.',
        'faq' => [
            'How do I accept M-PESA payments on my website?' => 'Create a payment request from your checkout with POST /payment-requests, send the customer to the pay_url it returns, and fulfil the order when the payment_request.paid webhook arrives. The customer approves an M-PESA prompt on their phone.',
            'Which payment methods can customers use?' => 'On the pay page, customers pay by M-PESA or from a Sharet wallet. Sharet wallet payments are free and approved with the customer\'s Sharet PIN.',
            'Where does the money go?' => 'Into your Sharet business wallet. From there you can split it to other Sharet accounts, or pay out to M-PESA, Airtel Money, T-Kash, a paybill, a till or a Kenyan bank.',
            'Can I split a payment between several people?' => 'Yes. Add up to 10 split legs to a payment request, as percentages or fixed amounts. Once it is paid, all legs are paid together or none are.',
            'Is there a sandbox?' => 'Yes. sk_test_ keys use simulated money on the same endpoints, and sandbox pay links have Simulate paid and Simulate cancel buttons.',
            'Can any business go live today?' => 'Live keys need a verified Sharet business. Business accounts are opening after the Kenya pilot; join the business waiting list or request sandbox access to start building now.',
            'Who holds the money?' => 'Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. Customer money is held in a separate trust account.',
        ],
        'cta' => 'Add M-PESA to your checkout.',
    ];
@endphp
