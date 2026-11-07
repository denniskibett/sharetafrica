<?php

namespace App\Http\Controllers;

use App\Api\PaymentRequests;
use App\Models\ApiPaymentRequest;
use App\Ramp\RampOrders;
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

        return response()->json(['status' => $r->status, 'collecting' => (bool) ($r->order && ! $r->order->final()), 'failure' => $r->order?->status === 'failed' ? $r->order->failure : null]);
    }

    public function sharet(Request $request, string $token)
    {
        $data = $request->validate(['pin' => ['required', 'string', 'max:8']]);
        $this->requests->payWithSharet($this->find($token), $request->user(), $data['pin']);

        return redirect()->route('pay-request.show', $token)->with('paid', true);
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
        if ($r->status === 'open' && $r->expires_at->isPast() && ! ($r->order && ! $r->order->fresh()->final())) {
            $this->requests->expireDue();
        }

        return $r->fresh(['payee', 'payer', 'order', 'key.company']);
    }
}
