<?php

namespace App\Http\Middleware;

use App\Models\MerchantTill;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cashier requests from a shop phone: "Authorization: Bearer <till token>" plus "X-Device-Id". The token links the
 * phone to one till (App\Merchant\Tills::link). With "open" the till must also be opened by the cashier PIN for this
 * shift. A paused till is refused at once; a removed one is signed out. The request acts for the shop's wallet only
 * so money routes can be idempotent; cashier routes never show its balance.
 */
class AuthenticateTill
{
    public function handle(Request $request, Closure $next, ?string $open = null): Response
    {
        $token = $request->bearerToken();
        $row = $token ? DB::table('merchant_till_devices')->where('token_hash', hash('sha256', $token))->first() : null;
        if (! $row || $row->revoked_at || ! hash_equals($row->device_id, (string) $request->header('X-Device-Id'))) {
            return response()->json(['error' => 'unauthenticated', 'message' => 'This phone is not linked to a till. Ask the shop owner for a cashier code.'], 401);
        }
        $till = MerchantTill::with('company')->find($row->till_id);
        if (! $till || $till->status === 'removed' || ! $till->company || $till->company->status === false) {
            return response()->json(['error' => 'unauthenticated', 'message' => 'This till was closed by the shop owner.'], 401);
        }
        if ($till->status === 'paused') {
            return response()->json(['error' => 'till_paused', 'message' => 'This till is paused by the shop owner.'], 403);
        }
        if ($open === 'open' && (! $row->unlocked_until || now()->gt($row->unlocked_until))) {
            return response()->json(['error' => 'till_locked', 'message' => 'Enter your cashier PIN.'], 401);
        }
        if (! $row->last_used_at || now()->diffInMinutes($row->last_used_at, true) >= 5) {
            DB::table('merchant_till_devices')->where('id', $row->id)->update(['last_used_at' => now()]);
        }
        $wallet = $till->company->walletUser();
        $request->setUserResolver(fn () => $wallet);   // Idempotent keys are kept per shop
        $request->attributes->set('till', $till);
        $request->attributes->set('till_device', $row->id);

        return $next($request);
    }
}
