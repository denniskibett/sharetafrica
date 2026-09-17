<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Modules\Subscriptions\Services\SubscriptionService;
use App\Modules\Subscriptions\Models\CompanySubscription;
use App\Modules\Subscriptions\Events\SubscriptionExpiringSoon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Register your custom commands here
        \App\Console\Commands\CreateTenantWallets::class,
        \App\Console\Commands\CreateWalletsForActiveTenants::class,
        \App\Console\Commands\InitializeTenantWallets::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Process subscription renewals daily at 2 AM
        $schedule->call(function () {
            app(SubscriptionService::class)->processMonthlyBilling();
        })->dailyAt('02:00')->name('subscription-renewals');
        
        // Handle failed subscription retries
        $schedule->call(function () {
            // Retry failed payments after 3 days
            CompanySubscription::where('status', 'past_due')
                ->where('ends_at', '<', now()->subDays(3))
                ->update(['status' => 'expired']);
        })->daily()->name('failed-subscription-retries');
        
        // Send subscription expiry reminders
        $schedule->call(function () {
            // Send 7 days before expiry
            $expiring = CompanySubscription::where('status', 'active')
                ->whereDate('ends_at', now()->addDays(7))
                ->get();
            
            foreach ($expiring as $subscription) {
                event(new SubscriptionExpiringSoon($subscription));
            }
        })->daily()->name('subscription-expiry-reminders');
        
        // Wallet maintenance: Ensure all active tenants have wallets (run daily)
        $schedule->command('tenants:create-wallets-for-active')->dailyAt('03:00');
        
        // Clean up old wallet transactions (keep last 2 years)
        $schedule->command('wallet:cleanup-transactions --years=2')->weekly()->sundays()->at('04:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}