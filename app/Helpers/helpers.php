<?php

use App\Models\System;

if (!function_exists('system_settings')) {
    /**
     * Get system settings
     */
    function system_settings($key = null, $default = null)
    {
        try {
            $settings = System::first();
            
            if (!$settings) {
                // Create default settings if none exist
                $settings = System::create([
                    'name' => config('app.name', 'Laravel'),
                    'timezone' => config('app.timezone', 'UTC'),
                    'currency' => 'USD',
                    'currency_symbol' => '$',
                ]);
            }
            
            if ($key === null) {
                return $settings;
            }
            
            return $settings->{$key} ?? $default;
        } catch (\Exception $e) {
            // Fallback to config values if database isn't ready
            if ($key === 'name') {
                return config('app.name', 'Laravel');
            }
            if ($key === 'timezone') {
                return config('app.timezone', 'UTC');
            }
            return $default;
        }
    }
}