<?php
// app/Http/Middleware/SetCompanyContext.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetCompanyContext
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->company_id) {
            // Set session variable for current company
            session(['current_company_id' => Auth::user()->company_id]);
            
            // Optional: Set config values based on company
            $company = Auth::user()->company;
            if ($company) {
                config(['app.company_name' => $company->name]);
            }
        }
        
        return $next($request);
    }
}