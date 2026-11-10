{{-- /bulk-payments-kenya: "bulk payments Kenya", "B2C payouts Kenya", "bulk M-Pesa payouts". Facts from /docs only. --}}
@extends('landing.layout')
@section('title', 'Bulk payments and B2C payouts in Kenya · M-PESA and banks · Sharet')
@section('description', 'Pay up to 500 people in one API call: M-PESA, Airtel Money, T-Kash, paybills, tills, Kenyan banks and Sharet wallets. Checked before anything moves, with a second approval for large batches.')
@php
    $lp = [
        'key' => 'bulk',
        'crumb' => 'Bulk payments Kenya',
        'label' => 'BULK PAYOUTS · B2C · KENYA',
        'h1' => 'Bulk payments to M-PESA and banks, in one call.',
        'lead' => 'Pay staff, suppliers, agents, landlords or customers from your Sharet wallet. One payout or 500 in a batch, to M-PESA, Airtel Money, T-Kash, paybills, tills, Kenyan banks or Sharet wallets. Every item is checked before anything is paid.',
        'docs' => '#settlements',
        'code' => '<span class="c"># Pay a batch: checked first, then paid item by item</span>
<span class="m">POST</span> /api/v1/settlements
Idempotency-Key: payroll-2026-11

{ <span class="k">"reference"</span>: <span class="s">"Riders · week 45"</span>, <span class="k">"items"</span>: [
  { <span class="k">"amount"</span>: <span class="s">"3200.00"</span>,
    <span class="k">"to"</span>: { <span class="k">"type"</span>: <span class="s">"mpesa"</span>, <span class="k">"phone"</span>: <span class="s">"0722 000 904"</span> } },
  { <span class="k">"amount"</span>: <span class="s">"22000.00"</span>,
    <span class="k">"to"</span>: { <span class="k">"type"</span>: <span class="s">"bank"</span>, <span class="k">"bank"</span>: <span class="s">"kcb"</span>, <span class="k">"account"</span>: <span class="s">"1102345678"</span> } }
] }

<span class="c"># 201 completed / partially_failed, or 202 awaiting_approval</span>',
        'stats' => ['500' => 'Items in one batch', '7 rails' => 'Sharet, M-PESA, Airtel, T-Kash, paybill, till, bank', 'Two people' => 'Approve live batches over KES 500,000', 'IP allow-list' => 'Live payouts only from your servers'],
        'steps_title' => 'How a payout batch runs.',
        'steps' => [
            '<b>Top up your Sharet wallet</b> (by M-PESA or from collections) so the batch has money to pay from.',
            '<b>Send the batch</b> with <code>POST /settlements</code>. Every item is checked first; if any is wrong, nothing is paid and <code>errors</code> lists them by index.',
            '<b>Large live batches wait for a second person.</b> Over KES 500,000 the batch returns <code>awaiting_approval</code> until a different person from your business approves it in the dashboard.',
            '<b>Items are paid one by one.</b> A refused item is recorded as failed without stopping the others, and a failed payout\'s money comes back to your wallet. <code>settlement.completed</code> or <code>settlement.failed</code> tells your server.',
        ],
        'features_title' => 'Payouts you can run unattended.',
        'features' => [
            'Single payouts too' => '<code>POST /payments</code> pays one person. Status goes <code>paying</code> while M-PESA or the bank works, then <code>paid</code>, <code>failed</code> or <code>refunded</code>.',
            'Quotes first' => '<code>POST /quotes</code> shows the exact fee, excise duty and total before you send. Nothing moves.',
            'Kenyan banks by PesaLink' => 'Equity, KCB, NCBA, I&amp;M, Co-op, Absa, Stanbic and Family Bank, plus paybills and tills.',
            'Free inside Sharet' => 'Payouts to Sharet wallets are free and instant, so paying people who already use Sharet costs nothing.',
            'Safe retries' => 'Every money call takes an <code>Idempotency-Key</code>: retry after a timeout and nobody is paid twice.',
            'Controls for finance teams' => 'Scoped keys (read, collect, payout, connect), IP allow-lists for live payouts, and a second approver for large batches.',
        ],
        'faq_title' => 'Bulk payment questions.',
        'faq' => [
            'Can I send B2C payments to M-PESA with Sharet?' => 'Yes. POST /payments pays one M-PESA number and POST /settlements pays up to 500 in a batch, from your Sharet wallet. Airtel Money, T-Kash, paybills, tills, Kenyan banks and Sharet wallets work the same way.',
            'What happens if one payment in a batch fails?' => 'Every item is checked before anything is paid; if any item is invalid, nothing is paid and the errors are listed by index. Once the batch runs, a refused item is recorded as failed without stopping the others, and a failed payout\'s money comes back to your wallet.',
            'Is there an approval step for large payouts?' => 'Yes. Live batches over KES 500,000 return awaiting_approval and run only after a different person from the same business approves them in the dashboard.',
            'How much do payouts cost?' => 'Payouts to Sharet wallets are free. Payouts to M-PESA, other wallets and banks carry a fee by movement type and amount, with a discount based on your volume that month. POST /quotes shows it before you pay.',
            'Which banks can I pay?' => 'Bank payouts go by PesaLink to Equity, KCB, NCBA, I&M, Co-op, Absa, Stanbic and Family Bank.',
            'Can I test bulk payouts?' => 'Yes. With an sk_test_ key batches run on simulated money. The M-PESA number 0700 000 002 always fails in the sandbox, so you can test your failure handling; every other destination succeeds.',
            'Who holds the money?' => 'Sharet Africa Ltd is not a bank. Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider. Customer money is held in a separate trust account.',
        ],
        'cta' => 'Run your first batch in the sandbox.',
    ];
@endphp
