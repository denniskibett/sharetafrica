<?php

namespace App\Http\Controllers\Api\V1;

use App\Api\Settlements;
use App\Http\Controllers\Controller;
use App\Models\ApiSettlement;
use App\Support\ApiProblem;
use Illuminate\Http\Request;

/** Batch settlements (docs/v2/INTEGRATION.md §4). Needs the "payout" scope and an Idempotency-Key. */
class SettlementsController extends Controller
{
    public function __construct(private Settlements $settlements) {}

    public function store(Request $request)
    {
        $request->validate(['items' => ['required', 'array'], 'reference' => ['nullable', 'string', 'max:140']]);
        $s = $this->settlements->create($request->attributes->get('api_key'), $request->user(), $request->all());

        return response()->json(Settlements::present($s), $s->status === 'awaiting_approval' ? 202 : 201);
    }

    public function show(Request $request, string $id)
    {
        $s = ApiSettlement::where('public_id', $id)->where('user_id', $request->user()->id)
            ->where('environment', $request->attributes->get('api_key')->environment)->first();

        return $s ? response()->json(Settlements::present($this->settlements->refresh($s)))
            : ApiProblem::make(404, 'settlement_not_found', 'No such settlement', 'Check the id; sandbox and live batches are only visible to keys of the same environment.');
    }
}
