<?php

namespace App\Support;

use App\Fx\Rates;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** The line under "Habari, Juma": the person's country and today's customer rates (Safaricom's rate less our margin). */
class HomeRates
{
    public const COUNTRIES = ['KE' => 'Kenya', 'UG' => 'Uganda', 'TZ' => 'Tanzania', 'RW' => 'Rwanda'];

    public static function country(User $user): array
    {
        $c = strtoupper((string) $user->country);
        $code = isset(self::COUNTRIES[$c]) ? $c : (array_search(ucfirst(strtolower((string) $user->country)), self::COUNTRIES, true) ?: 'KE');

        return [$code, self::COUNTRIES[$code]];
    }

    /**
     * The USD reference line, USD first (Sharet's main reference): "USD 1 = KES 131.95", the KES a customer pays for one
     * dollar: the reference rate plus the buy markup. Today's rate only, unless $latest: then the last recorded rate,
     * dated when it isn't today's ("USD 1 = KES 131.58 · 21 Nov"). Null before treasury has ever recorded one.
     */
    public static function usd(bool $latest = false): ?array
    {
        $today = now(config('fx.timezone'))->toDateString();
        $row = DB::table('fx_rates')->where('pair', 'KES/USD')->when(! $latest, fn ($q) => $q->where('rate_date', $today))
            ->where('rate_date', '<=', $today)->orderByDesc('rate_date')->orderByDesc('id')->first();
        if (! $row || bccomp((string) $row->rate, '0', 8) <= 0) {
            return null;
        }
        // what a customer pays for one dollar: the reference plus the buy markup
        $rate = number_format((float) app(Rates::class)->kesPer('USD', 'buy', bcdiv('1', (string) $row->rate, 8)), 2);
        $isToday = $row->rate_date === $today;
        $line = "USD 1 = KES $rate".($isToday ? '' : ' · '.\Illuminate\Support\Carbon::parse($row->rate_date)->format('j M'));

        return ['rate' => $rate, 'date' => $row->rate_date, 'today' => $isToday, 'line' => $line];
    }

    /** The rates line: "USD 1 = KES 131.58" (today's), or with $latest the last recorded one, dated. */
    public static function line(bool $latest = false): ?string
    {
        return self::usd($latest)['line'] ?? null;
    }
}
