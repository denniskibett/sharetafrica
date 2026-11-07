<?php

namespace App\Http\Middleware;

use App\Support\ApiProblem;
use Closure;
use Illuminate\Http\Request;

/** "api.scope:payout": the request's API key must carry that scope (docs/v2/INTEGRATION.md §2). */
class ApiScope
{
    public function handle(Request $request, Closure $next, string $scope)
    {
        $key = $request->attributes->get('api_key');
        if (! $key || ! $key->allows($scope)) {
            return ApiProblem::make(403, 'missing_scope', 'This key can\'t do that', "Give the key the \"$scope\" scope in Developers › API keys.");
        }

        $connection = $request->attributes->get('api_connection');
        if ($connection && ! $connection->allows($scope)) {
            return ApiProblem::make(403, 'not_allowed_by_account', 'That person didn\'t allow this', "Their Sharet account approved only: ".implode(', ', $connection->scopes).'. Send a new connect link asking for "'.$scope.'".');
        }

        return $next($request);
    }
}
