<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Mail goes out through the company's (or platform's) SMTP saved in Integrations
        $middleware->web(append: [\App\Http\Middleware\ApplyIntegrations::class]);
        // Analytics beacons are anonymous and carry no state change worth forging
        $middleware->validateCsrfTokens(except: ['t']);

        // Spatie Permission middleware aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            // Sharet API keys (Authorization: Bearer sk_…); alias() replaces, so keep all aliases in this one call
            'api.key' => \App\Http\Middleware\AuthenticateApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Error pages show this reference; the log line carries the same one
        $exceptions->context(fn () => ['ref' => \App\Support\ErrorRef::get(), 'url' => request()->fullUrl()]);
    })->create();