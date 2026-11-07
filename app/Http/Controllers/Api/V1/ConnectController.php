<?php

namespace App\Http\Controllers\Api\V1;

use App\Api\Connections;
use App\Http\Controllers\Controller;
use App\Models\ApiConnection;
use App\Support\ApiProblem;
use Illuminate\Http\Request;

/** Connected accounts, the platform's side (docs/v2/INTEGRATION.md §6). Needs the "connect" scope. */
class ConnectController extends Controller
{
    public function __construct(private Connections $connections) {}

    public function link(Request $request)
    {
        $request->validate(['scopes' => ['required', 'array'], 'reference' => ['nullable', 'string', 'max:140'], 'return_url' => ['nullable', 'string', 'max:500']]);
        $c = $this->connections->createLink($request->attributes->get('api_key'), $request->user(), $request->all());

        return response()->json(Connections::present($c), 201);
    }

    public function index(Request $request)
    {
        return response()->json(['data' => $this->mine($request)->whereIn('status', ['active', 'pending'])->latest()->limit(500)->get()
            ->map(fn (ApiConnection $c) => Connections::present($c))]);
    }

    public function disconnect(Request $request, string $id)
    {
        $c = $this->mine($request)->where('public_id', $id)->first();

        return $c ? response()->json(Connections::present($this->connections->revoke($c)))
            : ApiProblem::make(404, 'connection_not_found', 'No such connection', 'Check the id.');
    }

    private function mine(Request $request)
    {
        return ApiConnection::where('platform_user_id', $request->user()->id)->where('environment', $request->attributes->get('api_key')->environment)->with('account');
    }
}
