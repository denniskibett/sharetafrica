<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use App\Modules\System\Models\System;
use App\Helpers\SystemHelper;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super admin bypasses every permission check
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return true;
            }
        });

        // System model event listener
        if (class_exists(System::class)) {
            System::updated(function ($system) {
                SystemHelper::clearCache();
            });
        }

        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives(): void
    {
        // Role check
        Blade::if('role', function ($role) {
            return Auth::check() && Auth::user()->hasRole($role);
        });

        Blade::if('anyrole', function (...$roles) {
            return Auth::check() && Auth::user()->hasAnyRole($roles);
        });

        Blade::if('allroles', function (...$roles) {
            return Auth::check() && Auth::user()->hasAllRoles($roles);
        });

        // Sharet-specific checks
        Blade::if('admin', function () {
            return Auth::check() && Auth::user()->isAdmin();
        });

        Blade::if('waiting', function () {
            return Auth::check() && Auth::user()->isOnWaitingList();
        });

        Blade::if('onboarded', function () {
            return Auth::check() && Auth::user()->isOnboarded();
        });

        Blade::if('individual', function () {
            return Auth::check() && Auth::user()->isIndividual();
        });

        Blade::if('merchant', function () {
            return Auth::check() && Auth::user()->isMerchant();
        });

        Blade::if('business', function () {
            return Auth::check() && Auth::user()->isBusiness();
        });

        Blade::if('techie', function () {
            return Auth::check() && Auth::user()->isTechie();
        });
    }
}