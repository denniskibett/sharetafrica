{{-- /mpesa-api: "M-Pesa API Kenya", "M-Pesa STK push API", "M-Pesa API integration". Facts from /docs only. --}}
@extends('landing.layout')
@section('title', 'M-PESA API for Kenya · STK push collections and payouts · Sharet')
@section('description', 'Integrate M-PESA without Daraja paperwork: one Sharet API call sends an M-PESA prompt (STK push) to your customer, lands the money in your wallet and signs a webhook to your server. Sandbox first.')
@php
    $lp = [
        'key' => 'mpesa_api',
        'crumb' => 'M-PESA API',
        'label' => 'M-PESA API · KENYA',
        'h1' => 'The M-PESA API for Kenyan developers.',
        'lead' => 'Ask a customer to pay with one call. They get an M-PESA prompt (STK push) on their phone, enter their M-PESA PIN, and the money lands in your Sharet wallet. Your server hears about it through a signed webhook. Then pay out to M-PESA, other wallets and banks from the same balance.',
        'docs' => '#requests',
        'code' => '<span class="c"># Ask for KES 1,500, pay by M-PESA prompt or Sharet wallet</span>
<span class="m">POST</span> /api/v1/payment-requests
Authorization: Bearer sk_test_…
Idempotency-Key: order-1042

{
  <span class="k">"amount"</span>: <span class="s">"1500.00"</span>,
  <span class="k">"reference"</span>: <span class="s">"Order 1042"</span>,
  <span class="k">"payer"</span>: { <span class="k">"phone"</span>: <span class="s">"0722 000 904"</span> },
  <span class="k">"channels"</span>: [<span class="s">"mpesa"</span>, <span class="s">"sharet"</span>]
}

<span class="c"># 201 Created</span>
{ <span class="k">"id"</span>: <span class="s">"pr_…"</span>, <span class="k">"status"</span>: <span class="s">"open"</span>,
  <span class="k">"pay_url"</span>: <span class="s">"https://sharet.africa/r/…"</span> }',
        'stats' => ['STK push' => 'M-PESA prompt to any Kenyan phone', 'Webhooks' => 'payment_request.paid, signed', 'Sandbox' => 'Simulated M-PESA, same endpoints', 'KES 10' => 'Smallest amount, whole shillings'],
        'steps_title' => 'From API call to money in your wallet.',
        'steps' => [
            '<b>Create a payment request.</b> <code>POST /payment-requests</code> with the amount, your reference and, if you know it, the payer\'s phone. You get back a <code>pay_url</code>.',
            '<b>Your customer pays.</b> On the hosted pay page they choose M-PESA and an M-PESA prompt (STK push) appears on their phone. They approve it with their M-PESA PIN. Sharet members can pay from their wallet instead, free.',
            '<b>You get a webhook.</b> <code>payment_request.paid</code> arrives at your endpoint, signed with HMAC-SHA256. Check the signature, then confirm with <code>GET /payment-requests/{id}</code> if it matters.',
            '<b>Use the money.</b> It sits in your Sharet wallet. Pay suppliers, staff or customers out to M-PESA, Airtel Money, T-Kash, a paybill, a till or a bank from the same API.',
        ],
        'features_title' => 'Everything an M-PESA integration needs, in one API.',
        'features' => [
            'M-PESA prompt (STK push)' => 'The pay page sends the prompt to the payer\'s phone; the collection lands in your business wallet. No shortcode or Daraja app of your own to run.',
            'Hosted pay link' => 'Every request has a <code>pay_url</code> you can send by SMS, WhatsApp or email, or open from your checkout. It expires when you say.',
            'Signed webhooks' => 'Events for paid, expired and cancelled requests, payouts and settlements. Signed, retried for 24 hours, safe to replay by <code>id</code>.',
            'Idempotent writes' => 'Every money call takes an <code>Idempotency-Key</code>, so a retry after a timeout never charges or pays twice.',
            'Payouts from the same balance' => 'Send to M-PESA, Airtel Money, T-Kash, paybills, tills, Kenyan banks (PesaLink) or any Sharet wallet with <code>POST /payments</code>.',
            'Errors you can read' => 'RFC 7807 problem JSON with a stable <code>type</code> for your code and a plain <code>detail</code> for people.',
        ],
        'faq_title' => 'M-PESA API questions.',
        'faq' => [
            'Do I need my own Safaricom shortcode or Daraja app?' => 'No. You integrate with the Sharet API. The M-PESA prompt is sent from the hosted pay page and the money lands in your Sharet business wallet, which you can pay out from with the same API.',
            'What is an STK push?' => 'It is the M-PESA prompt that pops up on the customer\'s phone asking them to approve a payment with their M-PESA PIN. With Sharet the customer gets it after choosing M-PESA on the pay page; nothing moves until they approve.',
            'How do I know a customer has paid?' => 'Register a webhook endpoint with POST /webhooks. Sharet sends payment_request.paid, signed with HMAC-SHA256 in the Sharet-Signature header, and retries for 24 hours. You can also check GET /payment-requests/{id} at any time.',
            'Can I test without real money?' => 'Yes. Use an sk_test_ key: same endpoints, simulated money. Sandbox pay links have Simulate paid and Simulate cancel buttons, and webhooks fire in the sandbox too. Request sandbox access on the Developers page.',
            'What does it cost?' => 'Payments between Sharet accounts are free. Payouts outside Sharet carry a fee that every quote and payment shows, with your discount, before any money moves. See Pricing in the API docs.',
            'What do I need for live keys?' => 'A verified Sharet business. Live payouts also need your servers\' IP addresses listed on the key. Until then, build against the sandbox with an sk_test_ key.',
            'Who holds the money?' => 'Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. Customer money is held in a separate trust account.',
        ],
        'cta' => 'Start your M-PESA integration in the sandbox.',
    ];
@endphp
