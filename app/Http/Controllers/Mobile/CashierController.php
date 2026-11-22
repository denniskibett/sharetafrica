<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Ledger\Money;
use App\Merchant\Refunds;
use App\Merchant\Sales;
use App\Merchant\Tills;
use App\Models\MerchantSale;
use App\Models\MerchantTill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * The cashier on a shop phone (Phase 2 · canvas "Cashier"): charge (M-PESA prompt or QR), watch it arrive, see this
 * till's sales today, ask for a refund, close the day. No balance, no payouts, no refunds of their own. The phone is
 * linked to a till once (cashier phone + invite code + new cashier PIN); each shift opens with the cashier PIN.
 */
class CashierController extends Controller
{
    public function __construct(private Sales $sales, private Refunds $refunds) {}

    /** Link this phone to a till: the cashier's phone, the code from the owner, and a new 4-digit cashier PIN. */
    public function link(Request $request): JsonResponse
    {
        $data = $request->validate(['phone' => 'required|string|max:20', 'code' => 'required|string|max:10', 'pin' => 'required|string', 'pin_confirmation' => 'required|same:pin',
            'device_id' => 'required|string|min:8|max:100'], ['pin_confirmation.same' => 'The two PINs do not match.']);
        $limiter = 'cashier-link:'.preg_replace('/\D/', '', $data['phone']);
        if (RateLimiter::tooManyAttempts($limiter, 5)) {
            throw ValidationException::withMessages(['code' => 'Too many tries. Wait '.RateLimiter::availableIn($limiter).' seconds.']);
        }
        try {
            [$till, $token] = Tills::link($data['phone'], trim($data['code']), $data['pin'], $data['device_id']);
        } catch (ValidationException $e) {
            RateLimiter::hit($limiter, 900);
            throw $e;
        }
        RateLimiter::clear($limiter);

        return response()->json(['token' => $token, 'till' => $this->till($till)], 201);
    }

    /** The shop phone's lock screen: which shop and till, and whether the cashier PIN has opened it. */
    public function me(Request $request): JsonResponse
    {
        $till = $this->current($request);
        $open = ($u = \Illuminate\Support\Facades\DB::table('merchant_till_devices')->where('id', $request->attributes->get('till_device'))->value('unlocked_until')) && now()->lt($u);

        return response()->json(['till' => $this->till($till), 'open' => $open]);
    }

    public function signIn(Request $request): JsonResponse
    {
        $till = $this->current($request);
        Tills::signIn($till, (int) $request->attributes->get('till_device'), $request->input('pin'));

        return response()->json(['till' => $this->till($till->fresh()), 'open' => true]);
    }

    public function signOut(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\DB::table('merchant_till_devices')->where('id', $request->attributes->get('till_device'))->update(['unlocked_until' => null]);

        return response()->json(['open' => false, 'message' => 'Signed out of the till.']);
    }

    /** Unlink this phone from the till (the cashier leaves, or the phone is given away). */
    public function unlink(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\DB::table('merchant_till_devices')->where('id', $request->attributes->get('till_device'))->update(['revoked_at' => now(), 'unlocked_until' => null]);

        return response()->json(['message' => 'This phone is no longer linked to the till.']);
    }

    /** Charge: method mpesa (prompt the customer's phone) or qr (show the pay page as a QR). Money route. */
    public function charge(Request $request): JsonResponse
    {
        $data = $request->validate(['amount' => 'required|string|max:20', 'method' => 'required|in:mpesa,qr', 'phone' => 'required_if:method,mpesa|nullable|string|max:20']);
        $till = $this->current($request);
        $sale = $this->sales->charge($till->company, $till, $data['amount'], $data['method'], $data['phone'] ?? null);
        $till->forceFill(['last_seen_at' => now()])->save();

        return response()->json(['sale' => Sales::present($sale), 'message' => $data['method'] === 'mpesa' ? 'Prompt sent. The customer enters their M-PESA PIN.' : 'Show the QR to the customer.'], 201);
    }

    /** The till waits here (poll every 2–3 seconds): pending → paid (sound) or failed. */
    public function sale(Request $request, string $uuid): JsonResponse
    {
        return response()->json(['sale' => Sales::present($this->sales->follow($this->mine($request, $uuid)))]);
    }

    public function cancel(Request $request, string $uuid): JsonResponse
    {
        return response()->json(['sale' => Sales::present($this->sales->cancel($this->mine($request, $uuid)))]);
    }

    public function receipt(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate(['phone' => 'nullable|string|max:20']);
        $sent = $this->sales->receipt($this->mine($request, $uuid), $data['phone'] ?? null);

        return response()->json(['sent' => $sent, 'message' => $sent ? 'Receipt sent by SMS.' : 'The SMS could not be sent. The customer can see the payment in M-PESA.']);
    }

    /** This till's sales since its last close (today), with the totals and refunds asked. */
    public function today(Request $request): JsonResponse
    {
        $till = $this->current($request);
        [, $from] = \App\Merchant\Shop::day();
        $sales = MerchantSale::with(['till', 'request.order', 'order'])->where('till_id', $till->id)->where('status', 'paid')->whereNull('close_id')->latest('paid_at')->limit(300)->get();
        $refunds = \App\Models\MerchantRefund::with(['sale.till', 'till'])->where('till_id', $till->id)->where('created_at', '>=', $from)->latest('id')->get();
        $t = Sales::totals($till->company, $till);
        $last = \App\Models\MerchantClose::where('till_id', $till->id)->latest('id')->first();

        return response()->json(['till' => $this->till($till), 'sales' => $sales->count(), 'total' => Money::format((int) $sales->sum('amount')), 'totals_today' => [
            'sales' => $t['sales'], 'total' => Money::format($t['gross'])],
            'data' => $sales->map(fn ($s) => Sales::present($s))->values(), 'refunds' => $refunds->map(fn ($r) => Refunds::present($r))->values(),
            'last_close' => Sales::presentClose($last)]);
    }

    /** Ask the owner to refund a sale of this till (all of it or part). Nothing moves until the owner approves. */
    public function askRefund(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate(['amount' => 'nullable|string|max:20', 'reason' => 'required|string|max:140']);
        $sale = $this->mine($request, $uuid);
        $refund = $this->refunds->ask($sale, $data['amount'] ?? Money::plain($sale->amount - $sale->refunded), $data['reason'], $this->current($request));

        return response()->json(['refund' => Refunds::present($refund), 'message' => 'Refund asked. The owner approves it.'], 201);
    }

    /** End of day for this till: closes its sales and locks the phone (the cashier PIN opens the next day). */
    public function close(Request $request): JsonResponse
    {
        $till = $this->current($request);
        $close = Sales::close($till->company, $till, 'cashier');

        return response()->json(['close' => Sales::presentClose($close), 'message' => $till->label().' closed. Thank you.']);
    }

    private function current(Request $request): MerchantTill
    {
        return $request->attributes->get('till');
    }

    private function mine(Request $request, string $uuid): MerchantSale
    {
        return MerchantSale::where('uuid', $uuid)->where('till_id', $this->current($request)->id)->firstOrFail();
    }

    private function till(MerchantTill $t): array
    {
        return ['number' => $t->number, 'label' => $t->label(), 'cashier' => $t->firstName(), 'cashier_name' => $t->cashier_name, 'shop' => $t->company->name,
            'max_sale' => number_format(config('merchant.cashier_max_sale')), 'verified' => (bool) $t->company->verified_at];
    }
}
