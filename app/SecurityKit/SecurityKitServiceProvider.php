<?php

namespace App\SecurityKit;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Sharet security kit (ops/security-kit in the scharge repo). Drop-in for any Laravel 10+ app:
 *  - logs every login / sign-up / password / code attempt to storage/logs/kyc/attempts-YYYY-MM-DD.jsonl
 *  - refuses those requests from networks or accounts that are guessing (KycGuard)
 *  - /admin/security-report: this site's findings from the VPS defense agent + the attempt log (admins only)
 * Registered in bootstrap/providers.php. Settings: env SECURITY_* (see README).
 */
class SecurityKitServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        if (! KycLog::setting('SECURITY_KIT', true)) {
            return;
        }
        // Through the HTTP kernel: since Laravel 11 it re-syncs its groups to the router, dropping router-only pushes.
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        foreach ([['web', KycGuard::class], ['api', KycGuard::class], ['web', SecurityBadge::class]] as [$group, $mw]) {
            if (method_exists($kernel, 'appendMiddlewareToGroup')) {
                try {
                    $kernel->appendMiddlewareToGroup($group, $mw);
                    continue;
                } catch (\Throwable $e) {
                    // group not defined in this app
                }
            }
            $router->pushMiddlewareToGroup($group, $mw);
        }
        $this->loadViewsFrom(__DIR__.'/views', 'securitykit');

        $this->app->booted(function () {
            Route::middleware(['web', 'auth'])->get('/admin/security-report', [SecurityReportController::class, 'index'])
                ->name('securitykit.report');
        });
    }
}
