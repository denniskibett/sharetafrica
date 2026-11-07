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
            'rates' => HomeRates::line(), 'payments_open' => ! Lockdown::ledgerFrozen()]);
    }

    public function activity(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = RampOrder::where(fn ($q) => $q->where('user_id', $user->id)->orWhere('recipient_user_id', $user->id))
            ->whereNotIn('status', ['quoted', 'expired'])->latest('id')->paginate(20);

        return response()->json(['data' => $page->getCollection()->map(fn ($o) => $o->present($user))->values(),
            'meta' => ['page' => $page->currentPage(), 'last_page' => $page->lastPage()]]);
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
        $data = $request->validate(['handle' => 'required|string|max:40', 'amount' => 'required|string|max:20', 'note' => 'nullable|string|max:140', 'pin' => 'required|string']);
        TransactionPin::check($request->user(), $data['pin']);
        $order = $this->orders->transfer($request->user(), $data['handle'], $data['amount'], $data['note'] ?? null);

        return response()->json(['order' => $order->present($request->user()), 'message' => 'Sent to '.$order->recipient().'.'], 201);
    }

    /** Sharet to M-PESA, step 1: the full cost (fee, excise) before the PIN. Valid ~10 minutes. */
    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate(['phone' => 'required|string|max:20', 'amount' => 'required|string|max:20', 'note' => 'nullable|string|max:140']);
        $order = $this->orders->quote($request->user(), 'phone', ['phone' => $data['phone']], $data['amount'], $data['note'] ?? null, 'wallet');

        return response()->json(['order' => $order->present($request->user())], 201);
    }

    /** Step 2: confirm with the PIN. Confirming twice is harmless; the app polls GET orders/{uuid} for the result. */
    public function confirm(Request $request, string $uuid): JsonResponse
    {
        $order = $this->owned($request, $uuid);
        TransactionPin::check($request->user(), $request->input('pin'));
        $order = $this->orders->confirm($order);

        return response()->json(['order' => $order->present($request->user()), 'message' => 'Taken from your wallet. Sending to '.$order->recipient().'.']);
    }

    public function order(Request $request, string $uuid): JsonResponse
    {
        $order = $this->owned($request, $uuid, true);
        if (! $order->final() && $order->user_id === $request->user()->id) {
            $order = $this->orders->refresh($order);
        }

        return response()->json(['order' => $order->present($request->user()) + ['reference' => $order->reference()]]);
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
            'kyc_level' => $user->kyc_level ?? 'none', 'verified' => in_array($user->kyc_level, ['basic', 'standard'], true), 'pin_set' => TransactionPin::isSet($user)];
    }
}
