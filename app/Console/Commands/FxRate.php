<?php

namespace App\Console\Commands;

use App\Fx\FxException;
use App\Fx\Rates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
                $cur = explode('/', $pair)[1];
                $rows[] = [$pair, $ref, $rates->kesPer($cur, 'buy'), $rates->kesPer($cur, 'sell'), DB::table('fx_rates')->where('pair', $pair)->orderByDesc('rate_date')->orderByDesc('id')->value('source')];
            } catch (FxException $e) {
                $rows[] = [$pair, '—', '—', '—', $e->reason];
            }
        }
        $this->info('Customers buy at the reference + '.($rates->markupBps('buy') / 100).'% and sell at the reference − '.($rates->markupBps('sell') / 100).'%.');
        $this->table(['Pair', 'Reference today', 'Customer buys 1 at (KES)', 'Customer sells 1 for (KES)', 'Source'], $rows);

        return self::SUCCESS;
    }
}
