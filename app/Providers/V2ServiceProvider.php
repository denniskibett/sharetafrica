<?php

namespace App\Providers;

use App\Integrations\Contracts\IdReader;
use App\Integrations\IdReader\NullIdReader;
use App\Integrations\IdReader\TesseractIdReader;
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
        $this->app->singleton(IdReader::class, fn () => config('services.tesseract.reader') === 'tesseract' && TesseractIdReader::available()
            ? new TesseractIdReader : new NullIdReader);
    }

    public function boot(): void
    {
        Route::middleware('web')->group(base_path('routes/v2.php'));
        // Safaricom posts STK results here: no session or CSRF; the secret path segment is the guard
        Route::post('rails/daraja/callback/{token}', \App\Integrations\Daraja\DarajaCallbackController::class)
            ->middleware('throttle:120,1')->name('rails.daraja.callback');
        Route::post('rails/daraja/result/{token}', [\App\Integrations\Daraja\DarajaResultController::class, 'result'])
            ->middleware('throttle:120,1')->name('rails.daraja.result');
        Route::post('rails/daraja/timeout/{token}', [\App\Integrations\Daraja\DarajaResultController::class, 'timeout'])
            ->middleware('throttle:120,1')->name('rails.daraja.timeout');

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            // Proof the cron runs at all (sharet:golive reads it)
            $schedule->call(fn () => @file_put_contents(\App\Console\Commands\SharetGoLive::heartbeatPath(), (string) time()))->everyMinute()->name('heartbeat');
            $schedule->command('ledger:check')->hourly()->withoutOverlapping();
            $schedule->command('ramp:resolve')->everyMinute()->withoutOverlapping();
            $schedule->command('recon:run')->dailyAt('00:30')->withoutOverlapping();
            $schedule->command('db:backup')->dailyAt('01:30')->timezone('Africa/Nairobi')->withoutOverlapping();
        });
    }
}
