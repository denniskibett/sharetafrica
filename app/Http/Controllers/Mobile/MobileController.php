<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateDevice;
use App\Ledger\Money;
use App\Models\RampOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Security\Lockdown;
use App\Support\Footprint;
use App\Support\Handle;
use App\Support\HomeRates;
use App\Support\Identity;
use App\Support\TransactionPin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Sharet mobile API, version 1 (routes/api.php, prefix /api/mobile/v1). Small, JSON only, same rules as the website:
 * every payment goes through RampOrders and the Sharet PIN; the ledger, limits and kill switches apply unchanged.
 *
 * Sign-in: phone or email + password once per phone → a device token (kept in the phone's secure storage and
 * unlocked with the fingerprint / face). Money calls need "Idempotency-Key" so a retried request never runs twice.
 * Docs: docs/v2/MOBILE-API.md.
 */
class MobileController extends Controller
{
    public function __construct(private RampOrders $orders) {}

    /** Public: what the app needs before sign-in (rates line, whether payments are open). */
    public function config(): JsonResponse
    {
        return response()->json([
            'api' => 'v1', 'min_app_version' => config('mobile.min_version', '1.0.0'), 'currency' => config('ledger.currency'),
            'rates' => HomeRates::line(), 'payments_open' => ! Lockdown::ledgerFrozen(), 'support' => ['email' => 'support@sharet.africa'],
            'test_mode' => config('rails.default') === 'simulator',
            // banks a payment can go to (through the bank's M-PESA paybill): key => name
            'banks' => config('pricing.banks'),
            'android' => ($a = \App\Support\AndroidRelease::latest()) ? ['version' => $a['version'], 'build' => $a['build'] ?? null, 'url' => route('app.android')] : null,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:190', 'password' => 'required|string|max:200',
            'device_id' => 'required|string|min:8|max:100', 'device_name' => 'nullable|string|max:100', 'platform' => 'nullable|in:ios,android',
        ]);
        $id = trim($data['identifier']);
        $limiter = 'mobile-login:'.Str::lower($id);
        if (RateLimiter::tooManyAttempts($limiter, 5)) {
            throw ValidationException::withMessages(['identifier' => 'Too many tries. Wait '.RateLimiter::availableIn($limiter).' seconds.']);
        }
        $user = str_contains($id, '@')
            ? User::where('email', Str::lower($id))->first()
            : User::where('phone', Identity::normalisePhone($id))->first();

        if (! $user || $user->is_business_wallet || ! Hash::check($data['password'], (string) $user->password)) {
            RateLimiter::hit($limiter, 300);
            throw ValidationException::withMessages(['identifier' => 'That phone or email and password don\'t match.']);
        }
        if ($user->status === false) {
            throw ValidationException::withMessages(['identifier' => 'This account is not active. Contact support@sharet.africa.']);
        }
        RateLimiter::clear($limiter);

        // one live token per phone: signing in again on the same phone replaces the old one
        DB::table('device_tokens')->where('user_id', $user->id)->where('device_id', $data['device_id'])->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $token = Str::random(64);
        DB::table('device_tokens')->insert([
            'user_id' => $user->id, 'device_id' => $data['device_id'], 'name' => $data['device_name'] ?? null, 'platform' => $data['platform'] ?? null,
            'token_hash' => hash('sha256', $token), 'last_used_at' => now(), 'last_ip' => $request->ip(),
            'expires_at' => now()->addDays(AuthenticateDevice::DAYS), 'created_at' => now(), 'updated_at' => now(),
        ]);
        Footprint::record($user, $request, 'mobile_sign_in');

        return response()->json(['token' => $token, 'user' => $this->person($user)], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        DB::table('device_tokens')->where('id', $request->attributes->get('device_token_id'))->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Signed out on this phone.']);
    }

    public function devices(Request $request): JsonResponse
    {
        $current = $request->attributes->get('device_token_id');

        return response()->json(['devices' => DB::table('device_tokens')->where('user_id', $request->user()->id)->whereNull('revoked_at')->orderByDesc('last_used_at')
            ->get(['id', 'name', 'platform', 'last_used_at', 'created_at'])->map(fn ($d) => (array) $d + ['this_device' => $d->id === $current])]);
    }

    public function revokeDevice(Request $request, int $id): JsonResponse
    {
        DB::table('device_tokens')->where('user_id', $request->user()->id)->where('id', $id)->update(['revoked_at' => now()]);

        return response()->json(['message' => 'That phone is signed out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['user' => $this->person($user), 'wallet' => ['balance' => Money::format($this->orders->walletBalance($user)), 'currency' => config('ledger.currency')],
            'rates' => HomeRates::line(), 'payments_open' => ! Lockdown::ledgerFrozen(),
            // your own account number on Sharet's paybill (once made), and which ways to add money are open
            'quick_pay_limit' => ($q = \App\Support\QuickPay::limit($request)) ? Money::format($q) : null,
            'frozen' => (bool) $user->frozen_at,
            'account_number' => ($a = \App\Support\AccountNumber::for($user, create: false)) ? $a + ['formatted' => \App\Support\AccountNumber::format($a['account'])] : null,
            'add_ways' => array_values(array_filter(['mpesa', filled(\App\Services\Integrations::credentials('cloud9')['api_key'] ?? null) ? 'account' : null,
                filled(\App\Services\Integrations::credentials('cloud9')['api_key'] ?? null) ? 'card' : null]))]);
    }

    /** Your own account number on Sharet's paybill (made the first time). */
    public function accountNumber(Request $request): JsonResponse
    {
        $a = \App\Support\AccountNumber::for($request->user());

        return response()->json(['account_number' => $a ? $a + ['formatted' => \App\Support\AccountNumber::format($a['account'])] : null]);
    }

    /** Add money by card: the app opens the returned page in the browser; the customer pays the card charge on top. */
    public function addByCard(Request $request): JsonResponse
    {
        $data = $request->validate(['amount' => 'required|string|max:20']);
        [$order, $url] = $this->orders->topUpByCard($request->user(), $data['amount']);

        return response()->json(['order' => $order->present($request->user()), 'url' => $url], 201);
    }

    /** Requests (P05): To pay and Sent, the same lists as the web page. */
    public function requests(Request $request): JsonResponse
    {
        return response()->json(\App\Support\PersonalRequests::lists($request->user()));
    }

    public function askMoney(Request $request): JsonResponse
    {
        $data = $request->validate(['to' => 'required|string|max:40', 'amount' => 'required|string|max:20', 'note' => 'nullable|string|max:140', 'expires' => 'nullable|in:48h,7d']);
        $r = \App\Support\PersonalRequests::ask($request->user(), $data['to'], $data['amount'], $data['note'] ?? null, $data['expires'] ?? '48h');

        return response()->json(['message' => 'Request sent to '.($r->payer?->name ?? $r->payer_phone).'.', 'pay_url' => route('pay-request.show', $r->token)], 201);
    }

    public function splitBill(Request $request): JsonResponse
    {
        $data = $request->validate(['total' => 'required|string|max:20', 'people' => 'required|array|min:1|max:20', 'people.*' => 'string|max:40', 'note' => 'nullable|string|max:140', 'include_me' => 'boolean']);
        $rs = \App\Support\PersonalRequests::split($request->user(), $data['total'], $data['people'], $data['note'] ?? null, $data['include_me'] ?? true);

        return response()->json(['message' => 'Split sent: '.count($rs).' requests.'], 201);
    }

    /** Pay a request from the wallet with the Sharet PIN, inside the app (free between Sharet members). */
    public function payRequest(Request $request, string $id, \App\Api\PaymentRequests $requests): JsonResponse
    {
        $r = \App\Models\ApiPaymentRequest::where('kind', 'personal')->where('public_id', $id)->where('payer_user_id', $request->user()->id)->firstOrFail();
        \App\Support\QuickPay::approve($request, $request->user(), (int) $r->amount);
        $r = $requests->payWithSharet($r, $request->user(), null, approved: true);
        $order = $r->order;

        return response()->json(['message' => 'Paid '.$r->payee->name.'.', 'order' => $order ? $order->present($request->user()) + ['reference' => $order->reference(), 'receipt' => $order->receipt($request->user())] : null]);
    }

    /** decline (asked of me) · remind / cancel (I asked). */
    public function requestAction(Request $request, string $id, string $action): JsonResponse
    {
        $q = \App\Models\ApiPaymentRequest::where('kind', 'personal')->where('public_id', $id);
        $user = $request->user();
        match ($action) {
            'decline' => \App\Support\PersonalRequests::decline($q->firstOrFail(), $user),
            'remind' => \App\Support\PersonalRequests::remind($q->where('user_id', $user->id)->firstOrFail()),
            'cancel' => $q->where('user_id', $user->id)->where('status', 'open')->firstOrFail()->update(['status' => 'cancelled']),
        };

        return response()->json(['message' => ['decline' => 'Declined. Nothing was paid.', 'remind' => 'Reminder sent.', 'cancel' => 'Request cancelled.'][$action]]);
    }

    public function activity(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = RampOrder::where(fn ($q) => $q->where('user_id', $user->id)->orWhere('recipient_user_id', $user->id))
            ->whereNotIn('status', ['quoted', 'expired', 'reversed'])->latest('id')->paginate(20);

        // This month: money in, money out, and every fee and charge paid (nothing hidden)
        $month = RampOrder::where('created_at', '>=', now('Africa/Nairobi')->startOfMonth()->utc())->where('status', 'paid')
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('recipient_user_id', $user->id))->get();
        $in = fn ($o) => $o->recipient_user_id === $user->id || $o->destination_type === 'wallet';

        return response()->json(['data' => $page->getCollection()->map(fn ($o) => $o->present($user))->values(),
            'meta' => ['page' => $page->currentPage(), 'last_page' => $page->lastPage()],
            'month' => ['in' => Money::format((int) $month->filter($in)->sum('amount')), 'out' => Money::format((int) $month->reject($in)->sum('total')),
                'fees' => Money::format((int) $month->where('user_id', $user->id)->sum(fn ($o) => $o->fee + $o->excise))]]);
    }

    /** Pay again: the five people and billers this person paid most in the last 90 days, ready to pay. */
    public function favourites(Request $request): JsonResponse
    {
        $user = $request->user();
        $rows = RampOrder::where('user_id', $user->id)->where('status', 'paid')->whereIn('destination_type', ['sharet', 'phone', 'paybill', 'till', 'bank'])
            ->where('created_at', '>=', now()->subDays(90))->latest('id')->limit(300)->get()
            ->groupBy(fn ($o) => $o->destination_type.':'.json_encode(array_intersect_key((array) $o->destination, array_flip(['handle', 'phone', 'paybill', 'account', 'till', 'bank']))))
            ->sortByDesc(fn ($g) => $g->count())->take(5)
            ->map(function ($g) {
                $o = $g->first();
                $d = (array) $o->destination;

                return ['type' => $o->destination_type, 'label' => $o->recipient(), 'times' => $g->count(), 'last_amount' => Money::format($o->amount),
                    'fields' => array_filter(['handle' => isset($d['handle']) ? '@'.$d['handle'] : null, 'phone' => $d['phone'] ?? null, 'paybill' => $d['paybill'] ?? null,
                        'account' => $d['account'] ?? null, 'till' => $d['till'] ?? null, 'bank' => $d['bank'] ?? null])];
            })->values();

        return response()->json(['favourites' => $rows]);
    }

    /** Quick pay for this phone: a limit in KES (0 = off), with the PIN. */
    public function quickPay(Request $request): JsonResponse
    {
        $data = $request->validate(['limit' => 'required|integer|min:0|max:'.intdiv(\App\Support\QuickPay::MAX, 100), 'pin' => 'required|string']);
        $cents = \App\Support\QuickPay::set($request, $request->user(), (int) $data['limit']);

        return response()->json(['quick_pay_limit' => $cents ? Money::format($cents) : null,
            'message' => $cents ? 'Quick pay on: up to KES '.Money::format($cents).' with your fingerprint on this phone.' : 'Quick pay off: every payment asks for your PIN.']);
    }

    /** Freeze: stops every payment out at once (no PIN needed, for a lost phone or a scam call). */
    public function freeze(Request $request): JsonResponse
    {
        $request->user()->forceFill(['frozen_at' => now()])->save();
        \App\Security\Lockdown::log('customer froze their account', ['user' => $request->user()->id]);

        return response()->json(['frozen' => true, 'message' => 'Account frozen. Nothing can leave your wallet; money can still come in.']);
    }

    /** Unfreeze with the PIN. */
    public function unfreeze(Request $request): JsonResponse
    {
        TransactionPin::check($request->user(), $request->input('pin'));
        $request->user()->forceFill(['frozen_at' => null])->save();
        \App\Security\Lockdown::log('customer unfroze their account', ['user' => $request->user()->id]);

        return response()->json(['frozen' => false, 'message' => 'Account unfrozen.']);
    }

    /** Check an @name before sending: shows whose wallet it is. */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate(['handle' => 'required|string|max:40']);
        $to = Handle::find((string) $request->query('handle'));
        if (! $to || $to->is($request->user())) {
            throw ValidationException::withMessages(['handle' => $to ? 'That is your own wallet.' : 'No Sharet wallet has that @name.']);
        }

        return response()->json(['handle' => Handle::for($to), 'name' => $to->name]);
    }

    public function pin(Request $request): JsonResponse
    {
        $data = $request->validate(['pin' => 'required|string', 'pin_confirmation' => 'required|same:pin', 'current' => 'nullable|string'],
            ['pin_confirmation.same' => 'The two PINs do not match.']);
        TransactionPin::set($request->user(), $data['pin'], $data['current'] ?? null);

        return response()->json(['message' => 'Your Sharet PIN is set.', 'pin_set' => true]);
    }

    /** Sharet to Sharet: free and instant. Needs the PIN and an Idempotency-Key. */
    public function sendSharet(Request $request): JsonResponse
    {
        $data = $request->validate(['handle' => 'required|string|max:40', 'amount' => 'required|string|max:20', 'note' => 'nullable|string|max:140', 'pin' => 'nullable|string', 'biometric' => 'boolean']);
        \App\Support\QuickPay::approve($request, $request->user(), Money::cents($data['amount']));
        $order = $this->orders->transfer($request->user(), $data['handle'], $data['amount'], $data['note'] ?? null);

        return response()->json(['order' => $order->present($request->user()), 'message' => 'Sent to '.$order->recipient().'.'], 201);
    }

    /** Sharet to M-PESA, step 1: the full cost (fee, excise) before the PIN. Valid ~10 minutes. */
    public function quote(Request $request): JsonResponse
    {
        // From the wallet to an M-PESA number, a paybill, a till or a bank account (through its paybill), as on the web
        $data = $request->validate([
            'type' => ['nullable', 'in:'.implode(',', \App\Ramp\Destination::TYPES)],
            'amount' => 'required|string|max:20', 'note' => 'nullable|string|max:140',
            'phone' => 'nullable|string|max:20', 'paybill' => 'nullable|string|max:10', 'till' => 'nullable|string|max:10',
            'account' => 'nullable|string|max:30', 'bank' => 'nullable|string|max:20',
        ]);
        $order = $this->orders->quote($request->user(), $data['type'] ?? 'phone', $data, $data['amount'], $data['note'] ?? null, 'wallet');

        return response()->json(['order' => $order->present($request->user())], 201);
    }

    /** Step 2: confirm with the PIN. Confirming twice is harmless; the app polls GET orders/{uuid} for the result. */
    public function confirm(Request $request, string $uuid): JsonResponse
    {
        $order = $this->owned($request, $uuid);
        \App\Support\QuickPay::approve($request, $request->user(), (int) $order->total);
        $order = $this->orders->confirm($order);

        return response()->json(['order' => $order->present($request->user()), 'message' => 'Taken from your wallet. Sending to '.$order->recipient().'.']);
    }

    public function order(Request $request, string $uuid): JsonResponse
    {
        $order = $this->owned($request, $uuid, true);
        if (! $order->final() && $order->user_id === $request->user()->id) {
            $order = $this->orders->refresh($order);
        }

        // The receipt lines are the same as the web receipt, PDF and email: the Sharet reference and the network's own
        return response()->json(['order' => $order->present($request->user()) + ['reference' => $order->reference(), 'receipt' => $order->receipt($request->user())]]);
    }

    /** Add money from the user's own M-PESA (STK prompt on their phone). */
    public function addMoney(Request $request): JsonResponse
    {
        $data = $request->validate(['amount' => 'required|string|max:20']);
        $order = $this->orders->confirm($this->orders->topUp($request->user(), $data['amount']));

        return response()->json(['order' => $order->present($request->user()), 'message' => 'Check your phone and enter your M-PESA PIN.'], 201);
    }

    protected function owned(Request $request, string $uuid, bool $orRecipient = false): RampOrder
    {
        $order = RampOrder::where('uuid', $uuid)->first();
        $me = $request->user()->id;
        abort_unless($order && ($order->user_id === $me || ($orRecipient && $order->recipient_user_id === $me)), 404);

        return $order;
    }

    protected function person(User $user): array
    {
        return ['name' => $user->name, 'first_name' => Str::before(trim($user->name), ' '), 'handle' => Handle::for($user), 'phone' => $user->phone,
            'kyc_level' => $user->kyc_level ?? 'none', 'verified' => in_array($user->kyc_level, ['basic', 'standard'], true), 'pin_set' => TransactionPin::isSet($user),
            'sharet_id' => $user->sharet_id, 'email' => $user->email];
    }
}
