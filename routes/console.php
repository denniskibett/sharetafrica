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

// API payment requests past their time are closed and the business is told (payment_request.expired)
\Illuminate\Support\Facades\Artisan::command('api:requests-expire', function () {
    $this->info(app(\App\Api\PaymentRequests::class)->expireDue().' payment requests expired.');
})->purpose('Expire API payment requests that are past their time');
\Illuminate\Support\Facades\Schedule::command('api:requests-expire')->everyMinute()->withoutOverlapping();

// KYC backfill: re-read the ID photos already uploaded; what the card says replaces what was typed (old values kept under 'replaced')
\Illuminate\Support\Facades\Artisan::command('kyc:reread {--user= : One user id or email} {--dry : Show what would change, save nothing}', function () {
    $q = \App\Models\User::query()->whereNotNull('kyc');
    if ($u = $this->option('user')) {
        $q->where(fn ($w) => $w->where('id', $u)->orWhere('email', $u));
    }
    $mask = fn ($f, $v) => $v === null ? '—' : ($f === 'id_number' ? str_repeat('•', max(strlen($v) - 3, 0)).substr($v, -3) : $v);
    $read = $changed = 0;
    $q->orderBy('id')->each(function ($user) use (&$read, &$changed, $mask) {
        $kyc = \App\Support\Kyc::data($user);
        if (! isset($kyc['documents']['id_front']) && ! isset($kyc['documents']['id_back'])) {
            return;
        }
        $read++;
        $changes = \App\Support\Kyc::reread($user, ! $this->option('dry'));
        if ($changes) {
            $changed++;
            $this->line("#{$user->id} ".collect($changes)->map(fn ($c, $f) => "$f: ".$mask($f, $c[0]).' → '.$mask($f, $c[1]))->implode('; '));
        }
    });
    $this->info(($this->option('dry') ? '[dry run] ' : '')."$read people with ID photos read, $changed updated.");
    if (config('services.tesseract.reader') !== 'tesseract') {
        $this->warn('ID_READER is not "tesseract", so nothing could be read. Install tesseract-ocr and set ID_READER=tesseract.');
    }
})->purpose('Re-read uploaded ID photos and fill KYC details from them');
