<?php

namespace App\Http\Middleware;

use App\Services\Integrations;
use Closure;
use Illuminate\Http\Request;

/** Use the signed-in person's company email settings (else the platform's) for mail sent in this request. */
class ApplyIntegrations
{
    public function handle(Request $request, Closure $next)
    {
        Integrations::applyMail($request->user()?->company_id);

        return $next($request);
    }
}
