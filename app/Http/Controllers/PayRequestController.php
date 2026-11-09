<?php

namespace App\Http\Controllers;

use App\Api\PaymentRequests;
use App\Models\ApiPaymentRequest;
use App\Ramp\RampOrders;
use App\Support\TransactionPin;
use Illuminate\Http\Request;

/**
 * The hosted pay page for API payment requests: /r/{token} (docs/v2/INTEGRATION.md §3).
 * Anyone with the link can see it and pay by M-PESA; paying from a Sharet wallet needs the payer signed in and their PIN.
 */
class PayRequestController extends Controller
{
    public function __construct(private PaymentRequests $requests) {}

    public function show(Request $request, string $token)
    {
        $r = $this->find($token);
        if (! $request->user()) {
            session()->put('url.intended', route('pay-request.show', $token));   // signing in comes straight back here
        }

        return view('pay-request.show', ['r' => $r, 'payee' => $r->payee, 'company' => $r->key?->company]);
    }

    public function status(string $token)
    {
        $r = $this->find($token);

        $topUp = auth()->user() ? PaymentRequests::topUpFor($r, auth()->user(), true) : null;

        return response()->json(['status' => $r->status, 'collecting' => (bool) (($r->order && ! $r->order->final()) || $topUp), 'failure' => $r->order?->status === 'failed' ? $r->order->failure : null]);
    }

    public function sharet(Request $request, string $token)
    {
        $data = $request->validate(['pin' => ['required', 'string', 'max:8']]);
        $this->requests->payWithSharet($this->find($token), $request->user(), $data['pin']);

        return redirect()->route('pay-request.show', $token)->with('paid', true);
    }

    /**
     * A signed-in payer without a Sharet PIN creates one here (same rules as POST pay.pin). Nothing is paid: the page
     * comes back asking for the new PIN to approve the payment.
     */
    public function pin(Request $request, string $token)
    {
        $this->find($token);
        abort_if(TransactionPin::isSet($request->user()), 422, 'You already have a Sharet PIN. Change it in Profile › Security.');
        $data = $request->validate(['pin' => ['required', 'string'], 'pin_confirmation' => ['required', 'same:pin']],
            ['pin_confirmation.same' => 'The two PINs do not match.']);
        TransactionPin::set($request->user(), $data['pin']);

        return redirect()->route('pay-request.show', $token)->with('pin_created', true);
    }

    public function topUp(Request $request, string $token)
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $this->requests->topUpAndPay($this->find($token), $request->user(), $data['phone']);

        return redirect()->route('pay-request.show', $token);
    }

    public function mpesa(Request $request, string $token)
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $this->requests->payWithMpesa($this->find($token), $data['phone']);

        return redirect()->route('pay-request.show', $token);
    }

    public function sandbox(Request $request, string $token)
    {
        $this->requests->sandbox($this->find($token), $request->input('outcome') === 'pay');

        return redirect()->route('pay-request.show', $token);
    }

    private function find(string $token): ApiPaymentRequest
    {
        $r = ApiPaymentRequest::where('token', $token)->with(['payee', 'payer', 'order', 'key.company'])->firstOrFail();
        if ($r->order && ! $r->order->final()) {
            app(RampOrders::class)->refresh($r->order);   // an M-PESA prompt may have been answered: ask the rail
        }
        if ($r->order) {
            $this->requests->orderMoved($r->order->fresh());
        }
        if (auth()->user() && ($t = PaymentRequests::topUpFor($r, auth()->user(), true))) {
            app(RampOrders::class)->refresh($t);   // the payer's top-up prompt may have been answered: ask the rail (pays on)
        }
        if ($r->status === 'open' && $r->expires_at->isPast() && ! ($r->order && ! $r->order->fresh()->final())) {
            $this->requests->expireDue();
        }

        return $r->fresh(['payee', 'payer', 'order', 'key.company']);
    }
}
