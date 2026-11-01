<?php

namespace App\Http\Controllers;

use App\Models\RampOrder;
use App\Ramp\Destination;
use App\Ramp\RampOrders;
use App\Support\Table;
use App\Support\TransactionPin;
use Illuminate\Http\Request;

/**
 * Personal transactions: the history page, and the JSON behind the dashboard's money panel (send to a phone, paybill,
 * till, bank or another wallet; withdraw; add money). Everything after the first page load is JSON
 * (no reloads); the order page polls show() while the payment moves, which also asks the rail for news.
 */
class PayController extends Controller
{
    public function __construct(private RampOrders $orders) {}

    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('pay.index');
        }

        $me = $request->user();
        $query = RampOrder::with('sender')->where(fn ($q) => $q->where('user_id', $me->id)->orWhere('recipient_user_id', $me->id))
            ->whereNotIn('status', ['quoted', 'expired']) // quotes nobody confirmed are not transactions
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s));
        $mine = RampOrder::where('user_id', $me->id);

        return Table::respond($request, $query, fn (RampOrder $o) => $o->present($me) + [
            'pill' => Table::pill($o->statusLabel()[0], $o->statusLabel()[1]),
        ], [], ['date' => 'created_at'], [
            'paid' => (clone $mine)->where('status', 'paid')->count(),
            'waiting' => (clone $mine)->whereIn('status', ['collecting', 'paying', 'refunding'])->count(),
            'refunded' => (clone $mine)->where('status', 'refunded')->count(),
            'sent' => \App\Ledger\Money::format((int) (clone $mine)->where('status', 'paid')->where('destination_type', '!=', 'wallet')->sum('amount')),
            'wallet' => \App\Ledger\Money::format($this->orders->walletBalance($request->user())),
        ] + $this->summary($me), '-date');
    }

    /** Money in and out per day (?days=7|30|90), for the chart above the transactions. ApexCharts options as JSON. */
    public function chart(Request $request)
    {
        $me = $request->user();
        $days = in_array((int) $request->query('days'), [7, 30, 90], true) ? (int) $request->query('days') : 30;
        $from = now('Africa/Nairobi')->subDays($days - 1)->startOfDay();
        $orders = RampOrder::where('status', 'paid')->where('created_at', '>=', $from->copy()->utc())
            ->where(fn ($q) => $q->where('user_id', $me->id)->orWhere('recipient_user_id', $me->id))
            ->get(['user_id', 'recipient_user_id', 'destination_type', 'amount', 'total', 'created_at']);
        $dates = collect(range(0, $days - 1))->map(fn ($i) => $from->copy()->addDays($i));
        $sum = fn ($in) => $dates->map(fn ($d) => round($orders->filter(function ($o) use ($d, $me, $in) {
            $isIn = $o->recipient_user_id === $me->id || $o->destination_type === 'wallet';

            return $isIn === $in && $o->created_at->timezone('Africa/Nairobi')->isSameDay($d);
        })->sum(fn ($o) => $in ? $o->amount : $o->total) / 100, 2))->values();

        return response()->json([
            'type' => 'area',
            'series' => [['name' => 'Money in', 'data' => $sum(true)], ['name' => 'Money out', 'data' => $sum(false)]],
            'colors' => ['#22C55E', '#EF4444'],
            'xaxis' => ['categories' => $dates->map->format('j M')->values(), 'tickAmount' => min($days, 10), 'labels' => ['rotate' => 0, 'hideOverlappingLabels' => true]],
        ]);
    }

    /** This month in and out, fees paid, today's use against the limits, and people paid recently (dashboard column). */
    private function summary(\App\Models\User $me): array
    {
        $month = now('Africa/Nairobi')->startOfMonth()->utc(); // days and months start at midnight in Kenya
        $paid = RampOrder::where('status', 'paid')->where('created_at', '>=', $month);
        $out = (clone $paid)->where('user_id', $me->id)->where('destination_type', '!=', 'wallet');
        $inQuery = fn ($q) => $q->where(fn ($q) => $q->where('recipient_user_id', $me->id)->orWhere(fn ($q) => $q->where('user_id', $me->id)->where('destination_type', 'wallet')));
        $lastMonth = RampOrder::where('status', 'paid')->whereBetween('created_at', [now('Africa/Nairobi')->subMonthNoOverflow()->startOfMonth()->utc(), $month]);
        $limits = config('pricing.limits.'.($me->kyc_level ?: 'none')) ?? config('pricing.limits.none');
        $today = (int) RampOrder::where('user_id', $me->id)->where('destination_type', '!=', 'wallet')->where('created_at', '>=', now('Africa/Nairobi')->startOfDay()->utc())
            ->whereIn('status', ['collecting', 'paying', 'paid', 'refunding'])->sum('total');
        $recent = RampOrder::where('user_id', $me->id)->where('status', 'paid')->where('destination_type', '!=', 'wallet')->latest('id')->limit(20)->get()
            ->unique(fn ($o) => json_encode($o->destination))->take(4)
            ->map(fn ($o) => ['label' => $o->destination_type === 'sharet' ? '@'.$o->destination['handle'] : $o->recipient(), 'icon' => $o->present()['icon'],
                'payload' => ['mode' => 'send'] + ($o->destination_type === 'sharet' ? ['to' => '@'.$o->destination['handle']] : ['type' => $o->destination_type, 'dest' => $o->destination])])->values();

        return [
            'month_in' => \App\Ledger\Money::format((int) $inQuery(clone $paid)->sum('amount')),
            'month_in_count' => $inQuery(clone $paid)->count(),
            'month_out_count' => (clone $out)->count(),
            'last_in' => \App\Ledger\Money::format((int) $inQuery(clone $lastMonth)->sum('amount')),
            'last_out' => \App\Ledger\Money::format((int) (clone $lastMonth)->where('user_id', $me->id)->where('destination_type', '!=', 'wallet')->sum('total')),
            'waiting' => RampOrder::where('user_id', $me->id)->whereIn('status', ['collecting', 'paying', 'refunding'])->count(),
            'month_out' => \App\Ledger\Money::format((int) (clone $out)->sum('total')),
            'month_fees' => \App\Ledger\Money::format((int) (clone $out)->sum(\DB::raw('fee + excise + rounding'))),
            'today_used' => \App\Ledger\Money::format($today),
            'per_day' => \App\Ledger\Money::format($limits['per_day']),
            'per_order' => \App\Ledger\Money::format($limits['per_order']),
            'today_percent' => $limits['per_day'] ? min(100, (int) round($today * 100 / $limits['per_day'])) : 0,
            'recent' => $recent,
        ];
    }

    public function quote(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', Destination::TYPES)],
            'amount' => ['required', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:140'],
            'phone' => ['nullable', 'string', 'max:20'],
            'paybill' => ['nullable', 'string', 'max:10'],
            'till' => ['nullable', 'string', 'max:10'],
            'account' => ['nullable', 'string', 'max:30'],
            'bank' => ['nullable', 'string', 'max:20'],
            'funding' => ['nullable', 'in:mpesa,wallet'],
        ]);
        $order = $this->orders->quote($request->user(), $data['type'], $data, $data['amount'], $data['note'] ?? null, $data['funding'] ?? 'mpesa');

        return response()->json(['order' => $order->present()]);
    }

    /** Set the Sharet PIN (first time), or change it with the current one. */
    public function pin(Request $request)
    {
        $data = $request->validate(['pin' => ['required', 'string'], 'pin_confirmation' => ['required', 'same:pin'], 'current' => ['nullable', 'string']],
            ['pin_confirmation.same' => 'The two PINs do not match.']);
        TransactionPin::set($request->user(), $data['pin'], $data['current'] ?? null);

        return response()->json(['message' => 'Your Sharet PIN is set.', 'pin_set' => true]);
    }

    public function transfer(Request $request)
    {
        $data = $request->validate(['handle' => ['required', 'string', 'max:40'], 'amount' => ['required', 'string', 'max:20'], 'note' => ['nullable', 'string', 'max:140'], 'pin' => ['required', 'string']]);
        TransactionPin::check($request->user(), $data['pin']);
        $order = $this->orders->transfer($request->user(), $data['handle'], $data['amount'], $data['note'] ?? null);

        return response()->json(['order' => $order->present($request->user()), 'message' => 'Sent to '.$order->recipient().'.']);
    }

    public function topUp(Request $request)
    {
        $data = $request->validate(['amount' => ['required', 'string', 'max:20']]);

        return response()->json(['order' => $this->orders->topUp($request->user(), $data['amount'])->present()]);
    }

    /** Money from M-PESA is approved by the M-PESA PIN on the phone; money from the wallet needs the Sharet PIN. */
    public function confirm(Request $request, RampOrder $order)
    {
        $this->own($request, $order);
        if ($order->funding === 'wallet') {
            TransactionPin::check($request->user(), $request->input('pin'));
        }
        $order = $this->orders->confirm($order);

        return response()->json(['order' => $order->present(), 'message' => $order->funding === 'wallet' ? 'Paid from your wallet.' : 'Check your phone and enter your M-PESA PIN.']);
    }

    public function show(Request $request, RampOrder $order)
    {
        $this->own($request, $order);
        if (! $request->expectsJson()) {
            return view('pay.show', ['order' => $order->present($request->user())]);
        }
        if (! $order->final() && $order->user_id === $request->user()->id) {
            $order = $this->orders->refresh($order);
        }

        return response()->json(['order' => $order->present($request->user()) + ['reference' => $order->reference(), 'receipt' => $order->receipt($request->user())]]);
    }

    /** The receipt as a PDF to keep or forward (same lines as the panel and the email). */
    public function receipt(Request $request, RampOrder $order)
    {
        $this->own($request, $order);

        return \App\Support\Receipt::pdf($order, $request->user())->download('sharet-receipt-'.$order->sharetReference().'.pdf');
    }

    /** The payer, or (to view only) the Sharet user a transfer went to. */
    private function own(Request $request, RampOrder $order): void
    {
        abort_unless($order->user_id === $request->user()->id
            || ($request->isMethod('GET') && $order->recipient_user_id === $request->user()->id), 404);
    }
}
