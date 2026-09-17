<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Check if the class already exists before binding
        if (!class_exists('SystemHelper')) {
            $this->app->bind('systemhelper', function () {
                return new \App\Helpers\SystemHelper();
            });
            
            // Only create alias if it doesn't exist
            if (!class_exists('SystemHelper')) {
                class_alias(\App\Helpers\SystemHelper::class, 'SystemHelper');
            }
        }
    }

    public function boot(): void
    {
        //
    }
}