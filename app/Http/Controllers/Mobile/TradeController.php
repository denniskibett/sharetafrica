<?php

namespace App\Http\Controllers\Mobile;

use App\Business\Team;
use App\Http\Controllers\Controller;
use App\Models\TradeFxQuote;
use App\Models\TradeOrder;
use App\Trade\CrossBorder;
use App\Trade\TradeOrders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Trade in the app (routes/api.php, /api/mobile/v1/trade/…): LPOs to verified suppliers on Sharet, their invoices,
 * paying an invoice from the company wallet, and locked FX quotes for suppliers abroad (quote only: no partner abroad
 * yet). Finance offers are designed but off. Docs: docs/v2/MOBILE-API.md § Trade.
 */
class TradeController extends Controller
{
    public function __construct(private TradeOrders $trade, private CrossBorder $abroad) {}

    public function home(Request $request): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');

        return response()->json($this->trade->list($m) + [
            'finance' => TradeOrders::finance(),
            'abroad' => collect(config('business.trade.abroad'))->map(fn ($c, $cur) => ['currency' => $cur, 'countries' => $c['countries'], 'available' => false,
                'message' => CrossBorder::unavailable($cur)])->values(),
            'terms' => config('business.trade.terms'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['supplier' => 'required|string|max:40', 'items' => 'required|string|max:400', 'amount' => 'required|string|max:20', 'terms' => 'required|string']);
        $m = Team::membership($request->user());
        $o = $this->trade->create(Team::authorize($m, 'trade'), $data['supplier'], $data['items'], $data['amount'], $data['terms']);

        return response()->json(['order' => $this->trade->present($o->load(['importer', 'supplier']), $m), 'message' => 'LPO '.$o->number.' sent to '.$o->supplier->name.'.'], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');

        return response()->json(['order' => $this->trade->present($this->find($m, $id), $m)]);
    }

    public function invoice(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['number' => 'required|string|max:40', 'amount' => 'required|string|max:20', 'due' => 'required|string|max:20']);
        $m = Team::authorize(Team::membership($request->user()), 'trade');
        $o = $this->trade->invoice($m, $this->find($m, $id), $data['number'], $data['amount'], $data['due']);

        return response()->json(['order' => $this->trade->present($o, $m), 'message' => 'Invoice '.$o->invoice_number.' sent.']);
    }

    public function decline(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['reason' => 'required|string|max:200']);
        $m = Team::authorize(Team::membership($request->user()), 'trade');

        return response()->json(['order' => $this->trade->present($this->trade->decline($m, $this->find($m, $id), $data['reason']), $m), 'message' => 'Order declined.']);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'trade');

        return response()->json(['order' => $this->trade->present($this->trade->cancel($m, $this->find($m, $id)), $m), 'message' => 'Order cancelled.']);
    }

    /** Pay the invoice from the company wallet (PIN, Idempotency-Key). */
    public function pay(Request $request, int $id): JsonResponse
    {
        $request->validate(['pin' => 'required|string']);
        $m = Team::authorize(Team::membership($request->user()), 'approve');
        $o = $this->trade->pay($m, $this->find($m, $id), $request->input('pin'));

        return response()->json(['order' => $this->trade->present($o, $m), 'message' => $o->status === 'paid'
            ? 'Paid '.$o->supplier->name.'.' : 'Being checked by Sharet. Usually within an hour; nothing has moved yet.']);
    }

    /** Asking lenders to finance an invoice: not available yet. */
    public function finance(Request $request, int $id): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'trade');
        $this->find($m, $id);
        $f = TradeOrders::finance();

        return response()->json(['error' => 'not_available', 'message' => $f['message'] ?? 'Not available.'], 409);
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate(['currency' => 'required|string|size:3', 'amount' => 'required|string|max:20', 'supplier_name' => 'required|string|max:120',
            'country' => 'required|string|size:2', 'invoice_reference' => 'required|string|max:60', 'trade_order_id' => 'nullable|integer']);
        $m = Team::authorize(Team::membership($request->user()), 'trade');
        $q = $this->abroad->quote($m, $data['currency'], $data['amount'], $data['supplier_name'], $data['country'], $data['invoice_reference'], $data['trade_order_id'] ?? null);

        return response()->json(['quote' => CrossBorder::present($q)], 201);
    }

    public function showQuote(Request $request, string $id): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'view');

        return response()->json(['quote' => CrossBorder::present(TradeFxQuote::where('company_id', $m->company_id)->where('public_id', $id)->firstOrFail())]);
    }

    /** Refused with what is missing; nothing is taken (CrossBorder::pay). */
    public function payQuote(Request $request, string $id): JsonResponse
    {
        $m = Team::authorize(Team::membership($request->user()), 'approve');
        $this->abroad->pay($m, TradeFxQuote::where('company_id', $m->company_id)->where('public_id', $id)->firstOrFail());
    }

    private function find($m, int $id): TradeOrder
    {
        return TradeOrder::whereKey($id)->where(fn ($q) => $q->where('importer_company_id', $m->company_id)->orWhere('supplier_company_id', $m->company_id))
            ->with(['importer', 'supplier', 'order'])->firstOrFail();
    }
}
