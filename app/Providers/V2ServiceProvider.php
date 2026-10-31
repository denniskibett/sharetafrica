<?php

namespace App\Providers;

use App\Ledger\Ledger;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/** Wires the v2 money platform (routes/v2.php, the ledger, its hourly check) without touching v1 files. */
class V2ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Ledger::class);
    }

    public function boot(): void
    {
        Route::middleware('web')->group(base_path('routes/v2.php'));

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('ledger:check')->hourly()->withoutOverlapping();
            $schedule->command('ramp:resolve')->everyMinute()->withoutOverlapping();
            $schedule->command('recon:run')->dailyAt('00:30')->withoutOverlapping();
        });
    }
}
