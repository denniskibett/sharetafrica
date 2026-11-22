<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Ledger\Ledger;
use App\Models\RailRequest;
use App\Models\RampOrder;
use App\Ramp\Markup;
use Illuminate\Http\Request;

/**
 * Revenue share: for every finished payment, what moved, what the partner kept (Cloud9's charge, or Safaricom's on our
 * paybill) and what Sharet kept (its markup, excise set aside), by rail and by kind; plus the ledger's running totals.
 * Partner charges come from the partner's own answer (Cloud9 reports it); on the M-PESA paybill Safaricom's band is the
 * estimate until reconciliation, marked "est.".
 */
class RevenueController extends Controller
{
    public const PERIODS = ['today' => 'Today', '7d' => 'Last 7 days', 'month' => 'This month', '30d' => 'Last 30 days'];

    public function index(Request $request, Ledger $ledger)
    {
        $period = array_key_exists($request->query('period'), self::PERIODS) ? $request->query('period') : 'month';
        $from = match ($period) {
            'today' => now('Africa/Nairobi')->startOfDay(),
            '7d' => now('Africa/Nairobi')->subDays(7),
            '30d' => now('Africa/Nairobi')->subDays(30),
            default => now('Africa/Nairobi')->startOfMonth(),
        };
        $orders = RampOrder::with('user:id,name,is_business_wallet')->where('status', 'paid')->where('created_at', '>=', $from->copy()->utc())->latest('id')->limit(2000)->get();
        $requests = RailRequest::whereIn('id', $orders->pluck('collect_request_id')->merge($orders->pluck('payout_request_id'))->filter())->get()->keyBy('id');

        $rows = $orders->map(fn (RampOrder $o) => $this->row($o, $requests));
        $sum = fn ($c) => ['count' => $c->count(), 'moved' => $c->sum('moved'), 'partner' => $c->sum('partner'), 'sharet' => $c->sum('sharet'), 'excise' => $c->sum('excise')];

        return view('admin.revenue.index', [
            'period' => $period, 'periods' => self::PERIODS, 'rows' => $rows->take(200),
            'total' => $sum($rows),
            'byRail' => $rows->groupBy('rail')->map($sum)->sortByDesc('moved'),
            'byKind' => $rows->groupBy('kind')->map($sum)->sortByDesc('moved'),
            'ledger' => [
                ['4100 Sharet fees and markup', $ledger->account('4100')->fresh()->natural()],
                ['5130 Partner charges Sharet paid', $ledger->account('5130')->fresh()->natural()],
                ['2300 Excise duty owed (KRA)', $ledger->account('2300')->fresh()->natural()],
                ['4900 Rounding', $ledger->account('4900')->fresh()->natural()],
                ['4800 Test payments kept', $ledger->account('4800')->fresh()->natural()],
            ],
            'markup' => config('pricing.sharet_markup'),
        ]);
    }

    /** One payment: kind, rail, money moved, partner's charge, Sharet's share, excise. Cents. */
    private function row(RampOrder $o, $requests): array
    {
        $collect = $requests[$o->collect_request_id] ?? null;
        $payout = $requests[$o->payout_request_id] ?? null;
        $req = $payout ?? $collect;
        $rail = $o->rail === 'internal' ? 'internal' : ($req?->rail ?? $o->rail);
        $kind = match (true) {
            $o->destination_type === 'sharet' => 'Sharet to Sharet',
            $o->destination_type === 'wallet' => match ($collect?->meta['method'] ?? null) {
                'card' => 'Money in · card', 'account_number' => 'Money in · account number', default => 'Money in · M-PESA prompt' },
            (bool) $o->user?->is_business_wallet => 'Business payout',
            $o->destination_type === 'bank' => 'Out · bank',
            in_array($o->destination_type, ['paybill', 'till'], true) => 'Out · paybill or till',
            default => 'Out · M-PESA number',
        };
        // What the partner kept: reported by Cloud9; on the paybill, Safaricom's band for a payout (an estimate)
        $reported = (int) ($collect?->meta['charge'] ?? 0) + (int) ($payout?->meta['charge'] ?? 0);
        $estimated = false;
        if (! $reported && $payout && $rail === 'daraja') {
            $band = collect(config('pricing.safaricom_bands'))->first(fn ($b) => $o->amount <= $b[0]) ?? end(config('pricing.safaricom_bands'));
            $reported = (int) $band[1];
            $estimated = true;
        }
        if ($o->destination_type === 'wallet') {
            $sharet = max(0, (int) $o->fee - (int) ($collect?->meta['charge'] ?? 0));   // the markup taken from money in (excise inside it)
            $excise = $sharet ? Markup::excise($sharet) : 0;
            $sharet -= $excise;
            $moved = (int) $o->total;
        } else {
            $sharet = (int) $o->fee + (int) $o->rounding - $reported;   // fee less what the partner kept; excise is on top
            $excise = (int) $o->excise;
            $moved = (int) $o->amount;
        }

        return ['ref' => $o->sharetReference(), 'when' => $o->created_at->timezone('Africa/Nairobi')->format('j M, H:i'), 'who' => $o->user?->name,
            'kind' => $kind, 'rail' => $rail, 'moved' => $moved, 'partner' => $reported, 'estimated' => $estimated, 'sharet' => $sharet, 'excise' => $excise];
    }
}
