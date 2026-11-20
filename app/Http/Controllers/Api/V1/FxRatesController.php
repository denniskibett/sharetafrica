<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Fx\Rates;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/v1/fx/rates: Sharet's reference rates (Safaricom's M-PESA rate recorded by treasury), no margin.
 * DISPLAY ONLY: Breve, scharge and other Sharet products show converted figures with them. Nothing is quoted, held
 * or converted here; a real conversion is always a quote (Rates::quote) with the margin and a 15-minute lock.
 * Public and read-only (no key needed, rate limited). Each pair is the latest recorded on or before today; a pair
 * whose rate is not today's is listed under "stale".
 */
class FxRatesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = now(config('fx.timezone'))->toDateString();
        $pairs = $dates = [];
        foreach (config('fx.pairs') as $pair) {
            $row = DB::table('fx_rates')->where('pair', $pair)->where('rate_date', '<=', $today)
                ->orderByDesc('rate_date')->orderByDesc('id')->first(['rate', 'rate_date']);
            if ($row) {
                $pairs[$pair] = bcadd((string) $row->rate, '0', Rates::SCALE);
                $dates[$pair] = substr((string) $row->rate_date, 0, 10);
            }
        }

        return response()->json([
            'base' => 'KES',
            'date' => $dates ? max($dates) : null,
            'pairs' => $pairs,
            'dates' => $dates,
            'stale' => array_keys(array_filter($dates, fn ($d) => $d !== $today)),
            'source' => config('fx.source'),
            'margin' => 'none',
            'use' => 'display_only',
        ])->header('Cache-Control', 'public, max-age=300');
    }
}
