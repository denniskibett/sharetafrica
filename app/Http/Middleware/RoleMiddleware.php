<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Attempt to get the role name from relationship or direct field
        $userRole = null;
        
        if (method_exists($user, 'role') && $user->role) {
            $userRole = $user->role->name ?? null;
        } elseif (isset($user->roles()->pluck('name') )) {
            // If role_id exists, you'd need to fetch the role from a Role model
            // For now, let's assume it's a string
            $userRole = $user->role ?? null;
        } else {
            // Fallback: allow access to all authenticated users (temporary)
            return $next($request);
        }

        // If no specific roles are required, allow access
        if (empty($roles)) {
            return $next($request);
        }

        // If user role is null, deny
        if (!$userRole) {
            abort(403, 'Unauthorized - No role assigned.');
        }

        // Check if user's role is in the allowed roles list
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized - You do not have the required role.');
    }
}