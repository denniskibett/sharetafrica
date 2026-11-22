<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Ledger\Money;
use App\Merchant\Refunds;
use App\Merchant\Sales;
use App\Merchant\Shop;
use App\Merchant\Tills;
use App\Models\Company;
use App\Models\MerchantRefund;
use App\Models\MerchantSale;
use App\Models\MerchantTill;
use App\Ramp\RampOrders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The shop owner in the app (Phase 2 · Merchant, canvas "Owner"): today's sales, get paid (QR, account number, charge a
 * phone, payment link), refunds, settlement, team (tills and cashiers). Signed in like any personal account (device
 * token); only the owner of the shop gets here. Money routes need an Idempotency-Key. Docs: docs/v2/MOBILE-API.md.
 */
class MerchantController extends Controller
{
    public function __construct(private Sales $sales, private Refunds $refunds) {}

    /** Owner home: today's sales, fees, recent sales, refunds waiting, tills. */
    public function home(Request $request): JsonResponse
    {
        $shop = Shop::of($request->user());
        if (! $shop) {
            return response()->json(['shop' => null]);
        }
        $today = Sales::totals($shop);
        [, $from] = Shop::day();
        $lastWeek = Sales::totals($shop, null, now(config('merchant.timezone'))->subWeek()->toDateString());
        $recent = MerchantSale::with(['till', 'request.order', 'order'])->where('company_id', $shop->id)->where('status', 'paid')->latest('paid_at')->limit(10)->get();

        return response()->json([
            'shop' => $this->shop($shop),
            'today' => $this->money($today) + ['vs_last_week' => $today['sales'] - $lastWeek['sales'], 'fee_rate' => rtrim(rtrim(number_format(config('merchant.fee_bp.mpesa') / 100, 2), '0'), '.').'%'],
            'balance' => Money::format(app(RampOrders::class)->walletBalance(Shop::wallet($shop))),
            'recent' => $recent->map(fn ($s) => Sales::present($s, true))->values(),
            'refunds_waiting' => MerchantRefund::where('company_id', $shop->id)->where('status', 'asked')->count(),
            'tills' => $this->tillRows($shop),
        ]);
    }

    /** Open a shop from the app (verified personal account holders). Sharet checks the business before it takes money. */
    public function setup(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|min:2|max:120', 'kra_pin' => ['nullable', 'regex:/^[APap]\d{9}[A-Za-z]$/']],
            ['kra_pin.regex' => 'A KRA PIN looks like P051234567X.']);
        $shop = Shop::setup($request->user(), $data['name'], $data['kra_pin'] ?? null);

        return response()->json(['shop' => $this->shop($shop), 'message' => 'Shop saved. Sharet checks it (usually a day); then you can take payments.'], 201);
    }

    /** Get paid: the shop's account number on Sharet's paybill and its QR (the account number, for the counter). */
    public function getPaid(Request $request): JsonResponse
    {
        $shop = Shop::owned($request->user());
        $a = Shop::accountNumber($shop);

        return response()->json(['account_number' => $a, 'shop' => $shop->name,
            'qr' => $a ? 'sharet:pay?paybill='.$a['paybill'].'&account='.$a['account'].'&name='.rawurlencode($shop->name) : null]);
    }

    /** Sales of a day (default today), all tills or one. */
    public function sales(Request $request): JsonResponse
    {
        $data = $request->validate(['day' => 'nullable|date_format:Y-m-d', 'till' => 'nullable|integer']);
        $shop = Shop::owned($request->user());
        $till = isset($data['till']) ? MerchantTill::where('company_id', $shop->id)->where('number', $data['till'])->firstOrFail() : null;
        [$day, $from, $to] = Shop::day($data['day'] ?? null);
        $rows = MerchantSale::with(['till', 'request.order', 'order'])->where('company_id', $shop->id)
            ->where(fn ($q) => $q->whereBetween('paid_at', [$from, $to])->orWhere(fn ($w) => $w->whereNull('paid_at')->whereBetween('created_at', [$from, $to])))
            ->when($till, fn ($q) => $q->where('till_id', $till->id))->latest('id')->limit(500)->get();

        return response()->json(['day' => $day, 'totals' => $this->money(Sales::totals($shop, $till, $day)),
            'data' => $rows->map(fn ($s) => Sales::present($s, true))->values()]);
    }

    public function sale(Request $request, string $uuid): JsonResponse
    {
        $sale = $this->ownedSale($request, $uuid);

        return response()->json(['sale' => Sales::present($this->sales->follow($sale), true),
            'refunds' => $sale->refunds()->latest('id')->get()->map(fn ($r) => Refunds::present($r))->values()]);
    }

    /** The owner charges: an M-PESA prompt to a phone, or a payment link to share (fixed amount, paid once). */
    public function charge(Request $request): JsonResponse
    {
        $data = $request->validate(['amount' => 'required|string|max:20', 'method' => 'required|in:mpesa,link,qr', 'phone' => 'required_if:method,mpesa|nullable|string|max:20',
            'description' => 'nullable|string|max:140']);
        $shop = Shop::owned($request->user());
        $sale = $this->sales->charge($shop, null, $data['amount'], $data['method'], $data['phone'] ?? null, $data['description'] ?? null);

        return response()->json(['sale' => Sales::present($sale, true), 'message' => $data['method'] === 'mpesa' ? 'Prompt sent. The customer enters their M-PESA PIN.' : 'Link ready to share.'], 201);
    }

    public function cancelSale(Request $request, string $uuid): JsonResponse
    {
        return response()->json(['sale' => Sales::present($this->sales->cancel($this->ownedSale($request, $uuid)), true)]);
    }

    // ------------------------------------------------------------ refunds

    public function refunds(Request $request): JsonResponse
    {
        $shop = Shop::owned($request->user());
        $rows = MerchantRefund::with(['sale.till', 'till'])->where('company_id', $shop->id)->latest('id')->limit(100)->get();

        return response()->json(['waiting' => $rows->where('status', 'asked')->map(fn ($r) => Refunds::present($r))->values(),
            'done' => $rows->where('status', '!=', 'asked')->map(fn ($r) => Refunds::present($r))->values()]);
    }

    /** Approve with the PIN: the money leaves the shop's wallet now. Money route (Idempotency-Key). */
    public function approveRefund(Request $request, string $uuid): JsonResponse
    {
        $refund = $this->refunds->approve($this->ownedRefund($request, $uuid), $request->user(), $request->input('pin'));

        return response()->json(['refund' => Refunds::present($refund), 'message' => 'Refund approved. '.Refunds::present($refund)['goes_back_to'].'.']);
    }

    public function declineRefund(Request $request, string $uuid): JsonResponse
    {
        return response()->json(['refund' => Refunds::present($this->refunds->decline($this->ownedRefund($request, $uuid), $request->user())), 'message' => 'Refund declined. Nothing was paid back.']);
    }

    /** The owner refunds a sale directly. Money route (Idempotency-Key). */
    public function refundSale(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate(['amount' => 'required|string|max:20', 'reason' => 'nullable|string|max:140', 'pin' => 'nullable|string']);
        $refund = $this->refunds->refund($this->ownedSale($request, $uuid), $request->user(), $data['amount'], $data['reason'] ?? null, $data['pin'] ?? null);

        return response()->json(['refund' => Refunds::present($refund), 'message' => 'Refunded. '.Refunds::present($refund)['goes_back_to'].'.'], 201);
    }

    // ------------------------------------------------------------ team

    public function team(Request $request): JsonResponse
    {
        return response()->json(['tills' => $this->tillRows(Shop::owned($request->user()))]);
    }

    /** Add a cashier by phone: a new till and an invite code by SMS (also returned, to read out to them). */
    public function addCashier(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|min:2|max:120', 'phone' => 'required|string|max:20']);
        $shop = Shop::owned($request->user());
        [$till, $code] = Tills::invite($shop, $request->user(), $data['name'], $data['phone']);

        return response()->json(['till' => $this->tillRow($till), 'code' => $code,
            'message' => $till->firstName().' is cashier on '.$till->label().'. We sent them the code by SMS.'], 201);
    }

    public function tillAction(Request $request, int $number, string $action): JsonResponse
    {
        $shop = Shop::owned($request->user());
        $till = MerchantTill::where('company_id', $shop->id)->where('number', $number)->firstOrFail();
        if ($action === 'code') {
            if ($till->status === 'removed') {
                abort(404);
            }

            return response()->json(['till' => $this->tillRow($till), 'code' => Tills::sendCode($till), 'message' => 'New code sent to '.$till->firstName().'.']);
        }
        if ($action === 'close') {
            $close = Sales::close($shop, $till, 'owner');

            return response()->json(['till' => $this->tillRow($till->fresh()), 'close' => Sales::presentClose($close), 'message' => $till->label().' closed for the day.']);
        }
        $till = Tills::set($till, $action);

        return response()->json(['till' => $this->tillRow($till), 'message' => ['pause' => $till->label().' paused: the cashier PIN stops working now.',
            'resume' => $till->label().' is on again.', 'remove' => $till->firstName().' was removed. Their phones are unlinked.'][$action]]);
    }

    // ------------------------------------------------------------ settlement

    public function settlement(Request $request): JsonResponse
    {
        return response()->json($this->settlementView(Shop::owned($request->user())));
    }

    public function saveSettlement(Request $request): JsonResponse
    {
        $data = $request->validate(['mode' => 'required|in:keep,bank,mpesa', 'bank' => 'nullable|string|max:20', 'account' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20', 'settle_at' => 'nullable|string|max:5', 'pin' => 'nullable|string']);
        $shop = Shop::owned($request->user());
        \App\Support\TransactionPin::check($request->user(), $data['pin'] ?? null);   // where the shop's money goes: PIN
        Shop::saveSettlement($shop, $data);

        return response()->json($this->settlementView($shop->fresh()) + ['message' => 'Settlement saved.']);
    }

    // ------------------------------------------------------------ helpers

    private function settlementView(Company $shop): array
    {
        $s = Shop::settings($shop);
        $last = $s['last_settlement'];
        if ($last && ($last['order'] ?? null) && ($o = \App\Models\RampOrder::where('uuid', $last['order'])->first())) {
            $last['reference'] = $o->reference();
            $last['status'] = $o->status;
        }

        return ['settle_at' => $s['settle_at'], 'settlement' => $s['settlement'], 'auto' => (bool) config('merchant.auto_settle'),
            'last_settlement' => $last ? ['amount' => Money::format((int) $last['amount'])] + $last : null,
            'banks' => config('pricing.banks')];
    }

    private function shop(Company $shop): array
    {
        $a = Shop::accountNumber($shop);
        $s = Shop::settings($shop);

        return ['name' => $shop->name, 'initials' => collect(preg_split('/\s+/', trim($shop->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode(''),
            'sharet_id' => $shop->sharet_id, 'verified' => (bool) $shop->verified_at, 'account_number' => $a, 'settle_at' => $s['settle_at'], 'settlement' => $s['settlement']['mode']];
    }

    private function tillRows(Company $shop): array
    {
        return MerchantTill::where('company_id', $shop->id)->where('status', '!=', 'removed')->orderBy('number')->get()->map(fn ($t) => $this->tillRow($t))->all();
    }

    private function tillRow(MerchantTill $t): array
    {
        $today = Sales::totals($t->company, $t);
        $open = \Illuminate\Support\Facades\DB::table('merchant_till_devices')->where('till_id', $t->id)->whereNull('revoked_at')->where('unlocked_until', '>', now())->exists();

        return ['number' => $t->number, 'label' => $t->label(), 'cashier' => $t->cashier_name, 'phone' => Sales::maskPhone($t->cashier_phone), 'status' => $t->status,
            'signed_in' => $open && $t->status === 'active', 'today' => Money::format($today['gross']), 'sales_today' => $today['sales'],
            'last_seen' => $t->last_seen_at?->timezone(config('merchant.timezone'))->format('j M H:i')];
    }

    private function money(array $t): array
    {
        return ['day' => $t['day'], 'sales' => $t['sales'], 'gross' => Money::format($t['gross']), 'fees' => Money::format($t['fees']),
            'refunds' => Money::format($t['refunds']), 'net' => Money::format($t['net'])];
    }

    private function ownedSale(Request $request, string $uuid): MerchantSale
    {
        return MerchantSale::where('uuid', $uuid)->where('company_id', Shop::owned($request->user())->id)->firstOrFail();
    }

    private function ownedRefund(Request $request, string $uuid): MerchantRefund
    {
        return MerchantRefund::where('uuid', $uuid)->where('company_id', Shop::owned($request->user())->id)->firstOrFail();
    }
}
