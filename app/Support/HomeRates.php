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
     * The USD reference line, USD first (Sharet's main reference): "1 USD = KES 131.58", the KES a customer pays for one
     * dollar at Safaricom's rate less our margin. Today's rate only, unless $latest: then the last recorded rate,
     * dated when it isn't today's ("1 USD = KES 131.58 · 21 Nov"). Null before treasury has ever recorded one.
     */
    public static function usd(bool $latest = false): ?array
    {
        $today = now(config('fx.timezone'))->toDateString();
        $row = DB::table('fx_rates')->where('pair', 'KES/USD')->when(! $latest, fn ($q) => $q->where('rate_date', $today))
            ->where('rate_date', '<=', $today)->orderByDesc('rate_date')->orderByDesc('id')->first();
        if (! $row || bccomp((string) $row->rate, '0', 8) <= 0) {
            return null;
        }
        $m = bcdiv((string) app(Rates::class)->marginBps('KES/USD'), '10000', 8);
        $kesPerUsd = bcdiv('1', bcmul((string) $row->rate, bcsub('1', $m, 8), 8), 8);
        $rate = number_format((float) $kesPerUsd, 2);
        $isToday = $row->rate_date === $today;
        $line = "1 USD = KES $rate".($isToday ? '' : ' · '.\Illuminate\Support\Carbon::parse($row->rate_date)->format('j M'));

        return ['rate' => $rate, 'date' => $row->rate_date, 'today' => $isToday, 'line' => $line];
    }

    /** The rates line: "1 USD = KES 131.58" (today's), or with $latest the last recorded one, dated. */
    public static function line(bool $latest = false): ?string
    {
        return self::usd($latest)['line'] ?? null;
    }
}
