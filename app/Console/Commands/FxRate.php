<?php

namespace App\Console\Commands;

use App\Fx\FxException;
use App\Fx\Rates;
use Illuminate\Console\Command;

/**
 * Treasury, each morning: record Safaricom's M-PESA rate for the day.
 *   php artisan fx:rate KES/UGX 28.6500
 *   php artisan fx:rate KES/USD 0.00775 --confirm   (when it moved more than the daily limit)
 *   php artisan fx:rate                              (show today's rates and quotes)
 */
class FxRate extends Command
{
    protected $signature = 'fx:rate {pair?} {rate?} {--date=} {--confirm} {--note=}';

    protected $description = "Record today's Safaricom reference rate for a currency pair, or show today's rates";

    public function handle(Rates $rates): int
    {
        try {
            if ($this->argument('pair')) {
                $r = $rates->record($this->argument('pair'), (string) $this->argument('rate'), $this->option('date'), null, (bool) $this->option('confirm'), $this->option('note') ?: 'console');
                $this->info("Recorded {$r->pair} = {$r->rate} for {$r->rate_date} (source: {$r->source}).");
            }
        } catch (FxException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $rows = [];
        foreach (config('fx.pairs') as $pair) {
            try {
                $ref = $rates->reference($pair);
                [$b] = explode('/', $pair);
                $q = $rates->quote($b, explode('/', $pair)[1], 1_000_000);
                $rows[] = [$pair, $ref, $q['customer_rate'], $rates->marginBps($pair).' bps', number_format($q['to_amount'] / 100, 2)];
            } catch (FxException $e) {
                $rows[] = [$pair, '—', '—', $rates->marginBps($pair).' bps', $e->reason];
            }
        }
        $this->table(['Pair', 'Safaricom today', 'Customer rate', 'Margin', '10,000 '.'base → quote'], $rows);

        return self::SUCCESS;
    }
}
