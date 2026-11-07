<?php

namespace App\Http\Controllers\Api\V1;

use App\Api\PaymentRequests;
use App\Http\Controllers\Controller;
use App\Models\ApiPaymentRequest;
use App\Support\ApiProblem;
use Illuminate\Http\Request;

/** Payment requests, money in (docs/v2/INTEGRATION.md §3). Needs the "collect" scope; create takes an Idempotency-Key. */
class PaymentRequestsController extends Controller
{
    public function __construct(private PaymentRequests $requests) {}

    public function store(Request $request)
    {
        $request->validate(['amount' => ['required', 'string', 'max:20'], 'payer' => ['nullable', 'array'], 'channels' => ['nullable', 'array'],
            'reference' => ['nullable', 'string', 'max:140'], 'metadata' => ['nullable', 'array'], 'expires_in_minutes' => ['nullable', 'integer']]);
        $r = $this->requests->create($request->attributes->get('api_key'), $request->user(), $request->all());

        return response()->json(PaymentRequests::present($r), 201);
    }

    public function show(Request $request, string $id)
    {
        $r = $this->find($request, $id);

        return $r ? response()->json(PaymentRequests::present($r)) : $this->missing();
    }

    public function cancel(Request $request, string $id)
    {
        $r = $this->find($request, $id);

        return $r ? response()->json(PaymentRequests::present($this->requests->cancel($r))) : $this->missing();
    }

    /** Retry the split legs of a paid request whose split failed (e.g. after topping up the business wallet). */
    public function settle(Request $request, string $id)
    {
        $r = $this->find($request, $id);

        return $r ? response()->json(PaymentRequests::present($this->requests->settle($r))) : $this->missing();
    }

    private function find(Request $request, string $id): ?ApiPaymentRequest
    {
        $r = ApiPaymentRequest::where('public_id', $id)->where('user_id', $request->user()->id)
            ->where('environment', $request->attributes->get('api_key')->environment)->first();
        if ($r?->order) {
            $this->requests->orderMoved($r->order);
            $r = $r->fresh();
        }

        return $r;
    }

    private function missing()
    {
        return ApiProblem::make(404, 'payment_request_not_found', 'No such payment request', 'Check the id; sandbox and live requests are only visible to keys of the same environment.');
    }
}
