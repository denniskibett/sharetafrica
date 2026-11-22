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

// FX: the KES/USD reference from the Central Bank of Kenya, every morning (owner, 22 Nov 2026). CBK posts its indicative
// rate at the market open; this reads it at 9:30, again at 12:00 and 15:00 in case it was late. A rate treasury set by
// hand today (fx:rate) is never overwritten. A jump over fx.max_daily_move_pct is not recorded: treasury checks it and
// confirms with fx:rate --confirm. Weekends and holidays: CBK's last posted rate carries over, recorded for the day.
\Illuminate\Support\Facades\Artisan::command('fx:cbk {--force : Replace a rate set by hand today} {--confirm : Record it even if it moved more than the daily limit}', function () {
    $tz = config('fx.timezone');
    $today = now($tz)->toDateString();
    $todays = \Illuminate\Support\Facades\DB::table('fx_rates')->where('pair', 'KES/USD')->where('rate_date', $today)->orderByDesc('id')->first();
    if ($todays && $todays->source !== 'cbk' && ! $this->option('force')) {
        $this->info("KES/USD for $today was set by hand ({$todays->rate}); leaving it. Use --force to replace it with CBK's.");

        return 0;
    }
    try {
        $cbk = app(\App\Fx\Cbk::class)->latest();
        $kesPerUsd = $cbk['kes_per']['USD'];
        $rate = bcdiv('1', $kesPerUsd, \App\Fx\Rates::SCALE);   // KES/USD is dollars per shilling
        if ($todays && $todays->source === 'cbk' && bccomp((string) $todays->rate, $rate, \App\Fx\Rates::SCALE) === 0) {
            $this->info("KES/USD for $today is already CBK's (USD 1 = KES $kesPerUsd, posted {$cbk['posted']}).");

            return 0;
        }
        app(\App\Fx\Rates::class)->record('KES/USD', $rate, $today, null, (bool) $this->option('confirm'), "CBK mean USD 1 = KES $kesPerUsd, posted {$cbk['posted']}", 'cbk');
        $this->info("Recorded KES/USD = $rate for $today from CBK (USD 1 = KES $kesPerUsd, posted {$cbk['posted']}).");
    } catch (\App\Fx\FxException $e) {
        \Illuminate\Support\Facades\Log::error('fx:cbk could not record today\'s rate', ['reason' => $e->reason, 'message' => $e->getMessage()]);
        $this->error($e->getMessage());

        return 1;
    }

    return 0;
})->purpose("Record today's KES/USD reference rate from the Central Bank of Kenya");
foreach (['09:30', '12:00', '15:00'] as $at) {
    \Illuminate\Support\Facades\Schedule::command('fx:cbk')->dailyAt($at)->timezone('Africa/Nairobi')->withoutOverlapping();
}

// Merchant end of day (Phase 2): at each shop's time (Kenyan time, default 17:00) every till closes and, once switched
// on (MERCHANT_AUTO_SETTLE), the shop's balance goes to its bank or M-PESA. Once a day per shop.
\Illuminate\Support\Facades\Artisan::command('merchant:close-day', function () {
    $now = now(config('merchant.timezone'))->format('H:i');
    $n = 0;
    \App\Models\Company::whereIn('id', \App\Models\MerchantTill::select('company_id'))->orderBy('id')->each(function ($shop) use ($now, &$n) {
        if ($now >= \App\Merchant\Shop::settings($shop)['settle_at']) {
            $r = app(\App\Merchant\Sales::class)->closeDay($shop);
            $n += $r['closed'];
            $r['settlement'] && $this->line($shop->name.': settlement '.$r['settlement']['status']);
        }
    });
    $this->info($n.' tills closed.');
})->purpose('Close merchant tills at each shop\'s end of day and settle if switched on');
\Illuminate\Support\Facades\Schedule::command('merchant:close-day')->everyFiveMinutes()->withoutOverlapping();
