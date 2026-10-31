<?php

namespace App\Http\Controllers;

use App\Models\RampOrder;
use App\Ramp\Destination;
use App\Ramp\RampOrders;
use App\Support\Table;
use Illuminate\Http\Request;

/**
 * Personal "Pay anyone": quote, confirm, follow the payment. Everything after the first page load is JSON
 * (no reloads); the order page polls show() while the payment moves, which also asks the rail for news.
 */
class PayController extends Controller
{
    public function __construct(private RampOrders $orders) {}

    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('pay.index', ['banks' => config('pricing.banks'), 'payer' => $request->user()->phone]);
        }

        $query = RampOrder::where('user_id', $request->user()->id)
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s));
        $mine = RampOrder::where('user_id', $request->user()->id);

        return Table::respond($request, $query, fn (RampOrder $o) => $o->present() + [
            'pill' => Table::pill($o->statusLabel()[0], $o->statusLabel()[1]),
        ], [], ['date' => 'created_at'], [
            'paid' => (clone $mine)->where('status', 'paid')->count(),
            'waiting' => (clone $mine)->whereIn('status', ['collecting', 'paying', 'refunding'])->count(),
            'refunded' => (clone $mine)->where('status', 'refunded')->count(),
            'sent' => \App\Ledger\Money::format((int) (clone $mine)->where('status', 'paid')->sum('amount')),
        ], '-date');
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
        ]);
        $order = $this->orders->quote($request->user(), $data['type'], $data, $data['amount'], $data['note'] ?? null);

        return response()->json(['order' => $order->present()]);
    }

    public function confirm(Request $request, RampOrder $order)
    {
        $this->own($request, $order);
        $order = $this->orders->confirm($order);

        return response()->json(['order' => $order->present(), 'message' => 'Check your phone and enter your M-PESA PIN.']);
    }

    public function show(Request $request, RampOrder $order)
    {
        $this->own($request, $order);
        if (! $request->expectsJson()) {
            return view('pay.show', ['order' => $order->present()]);
        }
        if (! $order->final()) {
            $order = $this->orders->refresh($order);
        }

        return response()->json(['order' => $order->present()]);
    }

    private function own(Request $request, RampOrder $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404);
    }
}
