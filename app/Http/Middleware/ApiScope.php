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

        // Live money out only from the business's own servers (INTEGRATION.md §2)
        if ($scope === 'payout' && $key->isLive()) {
            if (empty($key->allowed_ips)) {
                return ApiProblem::make(403, 'ip_allow_list_required', 'Add your server addresses first', 'Live keys can only pay out from listed IP addresses. Add them to this key in Developers › API keys.');
            }
            if (! $key->ipAllowed($request->ip())) {
                return ApiProblem::make(403, 'ip_not_allowed', 'This address isn\'t allowed', 'This key pays out only from its listed addresses. We saw '.$request->ip().'.', ['ip' => $request->ip()]);
            }
        }

        $connection = $request->attributes->get('api_connection');
        if ($connection && ! $connection->allows($scope)) {
            return ApiProblem::make(403, 'not_allowed_by_account', 'That person didn\'t allow this', "Their Sharet account approved only: ".implode(', ', $connection->scopes).'. Send a new connect link asking for "'.$scope.'".');
        }

        return $next($request);
    }
}
