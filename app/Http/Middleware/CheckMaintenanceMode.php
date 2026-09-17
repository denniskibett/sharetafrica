<?php

namespace App\Http\Middleware;

use App\Helpers\SystemHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if maintenance mode is enabled
        if ($this->isMaintenanceModeEnabled()) {
            // Check if user is authenticated and has admin privileges
            if (!$this->hasAdminAccess($request)) {
                // Return maintenance page response
                return $this->maintenanceResponse();
            }
        }

        return $next($request);
    }

    /**
     * Check if maintenance mode is enabled
     */
    protected function isMaintenanceModeEnabled(): bool
    {
        try {
            return SystemHelper::isMaintenanceMode();
        } catch (\Exception $e) {
            // If there's an error (e.g., system table doesn't exist), fall back to config
            return config('app.maintenance_mode', false);
        }
    }

    /**
     * Check if user has admin access
     */
    protected function hasAdminAccess(Request $request): bool
    {
        $user = $request->user();
        
        // Check if user is authenticated
        if (!$user) {
            return false;
        }

        // Check if user has admin role (adjust based on your application's role system)
        if (method_exists($user, 'isAdmin')) {
            return $user->isAdmin();
        }

        // Alternative: Check for admin role or permission
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin');
        }

        // Alternative: Check for admin permission
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo('access_maintenance_mode');
        }

        // Default: Check if user has admin email or specific attribute
        return in_array($user->email, config('app.maintenance_allowed_emails', []));
    }

    /**
     * Return maintenance page response
     */
    protected function maintenanceResponse()
    {
        // Get custom maintenance message from system settings if available
        $message = null;
        try {
            $message = SystemHelper::get('maintenance_message');
        } catch (\Exception $e) {
            // Use default message
        }

        // Get custom title from system settings
        $title = SystemHelper::appName() . ' - Maintenance';

        // Return maintenance view with custom data
        return response()->view('maintenance', [
            'title' => $title,
            'message' => $message,
            'timezone' => SystemHelper::timezone(),
            'contact_email' => SystemHelper::contactEmail(),
            'contact_phone' => SystemHelper::contactPhone(),
        ], 503);
    }
}