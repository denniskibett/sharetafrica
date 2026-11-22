<?php

namespace App\Fx;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * The Central Bank of Kenya's daily indicative rates (the mean of the market's buying and selling at the open), read
 * from the "Daily KES Exchange Rates" panel on centralbank.go.ke: "US DOLLAR 130.00 … Posted On: 20-11-2026".
 * Owner, 22 Nov 2026: Sharet's KES/USD reference comes from CBK every day; treasury can still set it by hand.
 */
class Cbk
{
    public const URL = 'https://www.centralbank.go.ke/';

    /** Currency names on the panel → ISO codes. */
    public const NAMES = ['US DOLLAR' => 'USD', 'STG POUND' => 'GBP', 'EURO' => 'EUR'];

    /**
     * @return array{posted: string, kes_per: array<string, string>} e.g. ['posted' => '2026-11-20', 'kes_per' => ['USD' => '130.00']]
     *
     * @throws FxException when CBK can't be reached or the panel has changed
     */
    public function latest(): array
    {
        try {
            $res = Http::withHeaders(['User-Agent' => 'Sharet rates (sharet.africa)', 'Accept' => 'text/html,*/*'])   // CBK refuses requests with no Accept header
                ->timeout(20)->retry(2, 2000, throw: false)->get(self::URL);
        } catch (\Throwable $e) {
            throw FxException::make('source_down', 'Could not reach the Central Bank of Kenya: '.$e->getMessage());
        }
        if (! $res->successful()) {
            throw FxException::make('source_down', 'The Central Bank of Kenya answered '.$res->status().'.');
        }

        return self::parse($res->body());
    }

    public static function parse(string $html): array
    {
        $at = stripos($html, 'Daily KES Exchange Rates');
        if ($at === false) {
            throw FxException::make('source_changed', 'The Central Bank of Kenya page no longer shows its Daily KES Exchange Rates panel.');
        }
        $panel = substr($html, $at, 4000);
        $rates = [];
        if (preg_match_all('~<small>\s*([A-Z .]+?)\s*</small>\s*</td>\s*<td[^>]*>\s*([\d,]+\.\d+)\s*</td>~i', $panel, $m, PREG_SET_ORDER)) {
            foreach ($m as [, $name, $value]) {
                if ($code = self::NAMES[strtoupper(trim($name))] ?? null) {
                    $rates[$code] = str_replace(',', '', $value);
                }
            }
        }
        if (! preg_match('~Posted On:\s*(\d{2})-(\d{2})-(\d{4})~i', $panel, $d) || ! isset($rates['USD'])) {
            throw FxException::make('source_changed', 'Could not read the US dollar rate and its date from the Central Bank of Kenya page.');
        }
        $usd = (float) $rates['USD'];
        if ($usd < 50 || $usd > 500) {   // sanity: KES per USD has been ~100–160 for a decade
            throw FxException::make('source_changed', "The Central Bank of Kenya US dollar rate ({$rates['USD']}) looks wrong.");
        }

        return ['posted' => Carbon::createFromDate((int) $d[3], (int) $d[2], (int) $d[1])->toDateString(), 'kes_per' => $rates];
    }
}
