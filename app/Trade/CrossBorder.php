<?php

namespace App\Trade;

use App\Business\Team;
use App\Fx\FxException;
use App\Fx\Rates;
use App\Ledger\Money;
use App\Models\BusinessMember;
use App\Models\Company;
use App\Models\TradeFxQuote;
use App\Models\TradeOrder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Paying a supplier abroad (strictly fiat). The quote is real: today's reference (CBK for USD, Safaricom's for UGX,
 * TZS, RWF) with Sharet's markup (dollars + KES 1.30, others + 1.5%), locked for config fx.quote_minutes and tied to
 * the supplier's invoice reference. The payout is not: no partner abroad is signed (Cloud9 is KES only), so paying a
 * quote is refused with "not available yet" and nothing is ever taken from the wallet.
 */
final class CrossBorder
{
    public function __construct(private Rates $rates) {}

    public function quote(BusinessMember $m, string $currency, string $amount, string $supplierName, string $country, string $invoiceRef, ?int $tradeOrderId = null): TradeFxQuote
    {
        Team::authorize($m, 'trade');
        $currency = strtoupper(trim($currency));
        $country = strtoupper(trim($country));
        try {
            $this->rates->fiat($currency);
        } catch (FxException $e) {
            throw ValidationException::withMessages(['currency' => $e->getMessage()]);
        }
        $abroad = config('business.trade.abroad.'.$currency) ?? throw ValidationException::withMessages(['currency' => 'Sharet doesn\'t quote payments abroad in '.$currency.'.']);
        if (! in_array($country, $abroad['countries'], true)) {
            throw ValidationException::withMessages(['country' => $currency.' payments can\'t be quoted to that country.']);
        }
        try {
            $minor = Money::cents($amount);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['amount' => 'Write the amount the supplier asks for, e.g. 5000.00.']);
        }
        if ($minor <= 0) {
            throw ValidationException::withMessages(['amount' => 'The amount must be more than zero.']);
        }
        if ($tradeOrderId !== null) {
            abort_unless(TradeOrder::whereKey($tradeOrderId)->where('importer_company_id', $m->company_id)->exists(), 404);
        }

        try {
            $kesPerUnit = $this->rates->kesPer($currency, 'buy', null, Rates::SCALE);            // reference + Sharet's markup
            $reference = bcdiv('1', $this->rates->reference('KES/'.$currency), Rates::SCALE);   // KES per unit at the reference
        } catch (FxException $e) {
            throw ValidationException::withMessages(['currency' => $e->getMessage()]);
        }
        $kes = (int) bcmul((string) $minor, $kesPerUnit, 0);
        if (bccomp(bcmul((string) $minor, $kesPerUnit, Rates::SCALE), (string) $kes, Rates::SCALE) > 0) {
            $kes++;   // the shilling side rounds up: never short of what the supplier is owed
        }

        return TradeFxQuote::create([
            'public_id' => 'fxq_'.Str::lower(Str::random(20)), 'company_id' => $m->company_id, 'created_by' => $m->user_id,
            'currency' => $currency, 'amount' => $minor, 'kes_amount' => $kes, 'kes_per_unit' => bcadd($kesPerUnit, '0', 4), 'reference_rate' => bcadd($reference, '0', 4),
            'rate_source' => \Illuminate\Support\Facades\DB::table('fx_rates')->where('pair', 'KES/'.$currency)->orderByDesc('rate_date')->orderByDesc('id')->value('source'),
            'supplier_name' => Str::limit(strip_tags(trim($supplierName)), 120, ''), 'supplier_country' => $country,
            'invoice_reference' => Str::limit(strip_tags(trim($invoiceRef)), 60, ''), 'trade_order_id' => $tradeOrderId,
            'expires_at' => now()->addMinutes((int) config('fx.quote_minutes')),
        ]);
    }

    /** Never moves money until a partner abroad is live and built: says what is missing. */
    public function pay(BusinessMember $m, TradeFxQuote $q): never
    {
        Team::authorize($m, 'approve');
        abort_unless($q->company_id === $m->company_id, 404);
        if ($q->expires_at->isPast()) {
            Team::deny('quote_expired', 'This rate has expired. Get a new quote.', 409);
        }
        Team::deny('not_available', self::unavailable($q->currency), 409);
    }

    public static function present(TradeFxQuote $q): array
    {
        return [
            'id' => $q->public_id, 'currency' => $q->currency, 'amount' => Money::format($q->amount), 'kes_amount' => Money::format($q->kes_amount),
            'rate' => ['kes_per_unit' => $q->kes_per_unit, 'reference' => $q->reference_rate, 'source' => $q->rate_source === 'cbk' ? 'Central Bank of Kenya' : ($q->rate_source ? ucfirst($q->rate_source) : null)],
            'supplier' => ['name' => $q->supplier_name, 'country' => $q->supplier_country, 'country_name' => Company::countryName($q->supplier_country)],
            'invoice_reference' => $q->invoice_reference, 'trade_order_id' => $q->trade_order_id,
            'locked_until' => $q->expires_at->toIso8601String(), 'expired' => $q->expires_at->isPast(),
            'available' => false, 'message' => self::unavailable($q->currency),
        ];
    }

    public static function unavailable(string $currency): string
    {
        $needs = config('business.trade.abroad.'.strtoupper($currency).'.needs', 'a payout partner abroad');

        return 'Paying suppliers abroad isn\'t available yet: Sharet needs '.$needs.'. Nothing was taken from your wallet.';
    }
}
