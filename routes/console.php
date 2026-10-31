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
