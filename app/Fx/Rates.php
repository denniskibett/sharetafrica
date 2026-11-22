<?php

namespace App\Fx;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fiat-only FX on the day's reference rate (CBK for KES/USD, else Safaricom's) plus Sharet's buy markup or less its
 * sell markdown (config/fx.php).
 *
 *   record('KES/UGX', '28.65')            treasury, each morning
 *   quote('KES', 'UGX', 1_000_000)        KES 10,000.00 → UGX the customer gets, locked 15 min
 *
 * Amounts are integer minor units (cents). Rates are decimal strings worked with bcmath, never floats.
 * The customer rate is always worse than the reference by the margin, and payouts round DOWN, so every
 * conversion favours Sharet; the difference is reported as `spread` for booking to FX income.
 */
class Rates
{
    public const SCALE = 8;

    public function record(string $pair, string $rate, ?string $date = null, ?int $by = null, bool $confirm = false, ?string $note = null, ?string $source = null): object
    {
        [$base, $quote] = $this->pair($pair);
        if (! is_numeric($rate) || bccomp($rate, '0', self::SCALE) <= 0) {
            throw FxException::make('invalid_rate', 'A rate must be a positive number.');
        }
        $date ??= now(config('fx.timezone'))->toDateString();
        $previous = DB::table('fx_rates')->where('pair', "$base/$quote")->where('rate_date', '<', $date)->orderByDesc('rate_date')->orderByDesc('id')->value('rate');
        if ($previous && ! $confirm) {
            $move = abs((float) bcdiv(bcsub($rate, (string) $previous, self::SCALE), (string) $previous, self::SCALE)) * 100;
            if ($move > config('fx.max_daily_move_pct')) {
                throw FxException::make('rate_jump', sprintf('%s moved %.1f%% from yesterday (%s). Check the Safaricom rate and confirm.', "$base/$quote", $move, $previous));
            }
        }
        $id = DB::table('fx_rates')->insertGetId(['pair' => "$base/$quote", 'rate' => $rate, 'rate_date' => $date, 'source' => $source ?? config('fx.source'),
            'recorded_by' => $by, 'note' => $note, 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('fx_rates')->find($id);
    }

    /** Today's reference rate for BASE/QUOTE (latest correction wins). Throws if today's is missing. */
    public function reference(string $pair): string
    {
        [$base, $quote] = $this->pair($pair);
        $today = now(config('fx.timezone'))->toDateString();
        $rate = DB::table('fx_rates')->where('pair', "$base/$quote")->where('rate_date', $today)->orderByDesc('id')->value('rate');
        if (! $rate) {
            throw FxException::make('rate_stale', "No Safaricom rate recorded for $base/$quote today. Cross-currency payments are paused until treasury records it.");
        }

        return bcadd((string) $rate, '0', self::SCALE); // same 8-place string on every database
    }

    /**
     * What the customer gets for $amount (minor units of $from), locked 15 minutes. KES→foreign is a buy: the customer
     * pays the reference rate plus the buy markup. Foreign→KES is a sell: they get the reference less the sell markdown.
     */
    public function quote(string $from, string $to, int $amount): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        $this->fiat($from);
        $this->fiat($to);
        if ($amount <= 0) {
            throw FxException::make('invalid_amount', 'The amount must be more than zero.');
        }
        [$pair, $direct] = $this->find($from, $to);
        $ref = $this->reference($pair);
        $side = $direct ? 'buy' : 'sell';   // pairs are KES/X: from KES = buying X
        $bps = $this->markupBps($side);
        $m = bcdiv((string) $bps, '10000', self::SCALE);

        // Units of $to per 1 $from: the reference, then the markup in Sharet's favour
        $refRate = $direct ? $ref : bcdiv('1', $ref, self::SCALE);
        $customerRate = $side === 'buy'
            ? bcdiv($refRate, bcadd('1', $m, self::SCALE), self::SCALE)    // X costs (1 + markup) × the reference in KES
            : bcmul($refRate, bcsub('1', $m, self::SCALE), self::SCALE);   // X fetches (1 − markdown) × the reference in KES

        $toAmount = (int) bcmul((string) $amount, $customerRate, 0);            // bcmul with scale 0 truncates: rounds down
        $atReference = (int) bcmul((string) $amount, $refRate, 0);

        return [
            'from' => $from, 'to' => $to, 'amount' => $amount, 'to_amount' => $toAmount, 'side' => $side,
            'reference_rate' => $refRate, 'customer_rate' => $customerRate, 'markup_bps' => $bps,
            'spread' => $atReference - $toAmount,                                // in $to minor units, Sharet's
            'source' => DB::table('fx_rates')->where('pair', $pair)->where('rate_date', now(config('fx.timezone'))->toDateString())->orderByDesc('id')->value('source') ?? config('fx.source'),
            'pair' => $pair,
            'expires_at' => now()->addMinutes(config('fx.quote_minutes'))->toIso8601String(),
        ];
    }

    /** Sharet's markup in basis points: 'buy' (customer buys foreign currency) or 'sell' (customer sells it). */
    public function markupBps(string $side): int
    {
        return (int) ($side === 'buy' ? config('fx.buy_markup_bps') : config('fx.sell_markdown_bps'));
    }

    /** What a customer pays in KES for one unit of $currency today ('buy') or gets for it ('sell'), 4 decimals. */
    public function kesPer(string $currency, string $side = 'buy', ?string $kesPerReference = null): string
    {
        $kesPerReference ??= bcdiv('1', $this->reference('KES/'.strtoupper($currency)), self::SCALE);
        $m = bcdiv((string) $this->markupBps($side), '10000', self::SCALE);
        $v = $side === 'buy' ? bcmul($kesPerReference, bcadd('1', $m, self::SCALE), self::SCALE) : bcmul($kesPerReference, bcsub('1', $m, self::SCALE), self::SCALE);

        return bcadd($v, '0', 4);
    }

    protected function find(string $from, string $to): array
    {
        foreach (config('fx.pairs') as $pair) {
            [$b, $q] = explode('/', $pair);
            if ($b === $from && $q === $to) {
                return [$pair, true];
            }
            if ($b === $to && $q === $from) {
                return [$pair, false];
            }
        }
        throw FxException::make('pair_not_offered', "Sharet doesn't convert $from to $to.");
    }

    protected function pair(string $pair): array
    {
        $parts = explode('/', strtoupper(trim($pair)));
        if (count($parts) !== 2) {
            throw FxException::make('invalid_pair', 'Write a pair as BASE/QUOTE, e.g. KES/UGX.');
        }
        array_map([$this, 'fiat'], $parts);
        if (! in_array(implode('/', $parts), config('fx.pairs'), true)) {
            throw FxException::make('pair_not_offered', implode('/', $parts).' is not an offered pair.');
        }

        return $parts;
    }

    /** Strictly fiat: ISO 4217 currencies from config only. Anything else (virtual assets included) is refused. */
    public function fiat(string $code): void
    {
        if (! in_array(strtoupper($code), config('fx.currencies'), true)) {
            throw FxException::make('not_fiat', "$code is not a supported fiat currency. Sharet handles fiat money only.");
        }
    }
}
