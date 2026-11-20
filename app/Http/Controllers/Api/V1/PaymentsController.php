<?php

namespace App\Http\Controllers\Api\V1;

use App\Api\Payments;
use App\Http\Controllers\Controller;
use App\Models\ApiPayment;
use App\Support\ApiProblem;
use Illuminate\Http\Request;

/** Payments out (docs/v2/INTEGRATION.md §4). Needs the "payout" scope and an Idempotency-Key. */
class PaymentsController extends Controller
{
    public function __construct(private Payments $payments) {}

    public function store(Request $request)
    {
        $request->validate(['amount' => ['required', 'string', 'max:20'], 'to' => ['required', 'array'], 'reference' => ['nullable', 'string', 'max:140']]);
        $p = $this->payments->create($request->attributes->get('api_key'), $request->user(), $request->all());

        return response()->json(Payments::present($p), 201);
    }

    public function show(Request $request, string $id)
    {
        $key = $request->attributes->get('api_key');
        $p = ApiPayment::where('public_id', $id)->where('user_id', $request->user()->id)->where('environment', $key->environment)->first();
        if (! $p) {
            return ApiProblem::make(404, 'payment_not_found', 'No such payment', 'Check the id; sandbox and live payments are only visible to keys of the same environment.');
        }

        return response()->json(Payments::present($this->payments->current($p)));
    }
}
