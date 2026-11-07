<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Website analytics retention: page views ~13 months (year-on-year reports), clicks 6 months (heatmaps)
\Illuminate\Support\Facades\Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('page_views')->where('created_at', '<', now()->subMonths(13))->delete();
    \Illuminate\Support\Facades\DB::table('page_clicks')->where('created_at', '<', now()->subMonths(6))->delete();
})->daily()->name('analytics:prune')->withoutOverlapping();

// API webhooks: send deliveries that are due (first tries happen at once; this retries failures for 24 hours)
\Illuminate\Support\Facades\Artisan::command('api:webhooks-retry', function () {
    $this->info(app(\App\Api\Webhooks::class)->retryDue().' webhook deliveries tried.');
})->purpose('Retry API webhook deliveries that are due');
\Illuminate\Support\Facades\Schedule::command('api:webhooks-retry')->everyMinute()->withoutOverlapping();
