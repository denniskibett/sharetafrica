<?php

namespace App\Http\Controllers\Api\V1;

use App\Api\PaymentInput;
use App\Http\Controllers\Controller;
use App\Ledger\Ledger;
use App\Models\User;
use App\Support\ApiProblem;
use App\Support\Handle;
use Illuminate\Http\Request;

/**
 * Public API v1: who am I, who is this @name / Sharet ID, and what would a payment cost. Nothing here moves money.
 * Contract: docs/v2/INTEGRATION.md (§1 identifiers, §7 pricing, §9 accounts).
 */
class AccountsController extends Controller
{
    /** GET /api/v1/accounts/me: the account the key acts for. */
    public function me(Request $request, Ledger $ledger)
    {
        $key = $request->attributes->get('api_key');
        $user = $request->user();

        return response()->json([
            'sharet_id' => $user->sharet_id,
            'handle' => '@'.Handle::for($user),
            'name' => $user->name,
            'verification' => $user->kyc_level ?: 'none',
            'business' => $key->company ? ['sharet_id' => $key->company->sharet_id, 'name' => $key->company->name] : null,
            'balance' => ['currency' => config('ledger.currency'), 'available' => PaymentInput::amount($ledger->account('2100', $user)->available())],
            'key' => ['name' => $key->name, 'environment' => $key->environment, 'scopes' => $key->scopes ?? ['read']],
        ]);
    }

    /** GET /api/v1/accounts/resolve?handle=@x or ?sharet_id=SH-…: the Sharet ID behind it and a masked verified name. */
    public function resolve(Request $request)
    {
        $user = $this->find($request->query('sharet_id'), $request->query('handle'));
        if (! $user) {
            return ApiProblem::make(404, 'account_not_found', 'No such Sharet account', 'Check the @name or Sharet ID.');
        }

        return response()->json($this->present($user));
    }

    /** POST /api/v1/quotes: the exact fee and total for a payment, without sending it (same checks as payments). */
    public function quote(Request $request)
    {
        $request->validate(['amount' => ['required', 'string', 'max:20'], 'currency' => ['nullable', 'in:'.config('ledger.currency')], 'to' => ['required', 'array']]);
        $in = PaymentInput::read($request->all(), $request->user());

        if ($in['type'] === 'sharet') {
            return response()->json($this->priced($in['cents'], ['fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => $in['cents']], ['type' => 'sharet'] + $this->present($in['recipient'])));
        }

        $price = app(\App\Api\Tariffs::class)->quote($request->user(), $in['type'], $in['cents']);

        return response()->json($this->priced($in['cents'], $price, ['type' => $in['type']] + $in['destination'])
            + ['tariff' => ['version' => $price['tariff_version'], 'volume_discount_percent' => $price['discount_bp'] / 100, 'month_to_date' => PaymentInput::amount($price['volume'])]]);
    }

    private function find(?string $sharetId, ?string $handle): ?User
    {
        return PaymentInput::account($sharetId, $handle);
    }

    /** The verified name masked to "Juma J." so an @name lookup can't be used to harvest full names. */
    private function present(User $user): array
    {
        $parts = preg_split('/\s+/', trim((string) $user->name));
        $masked = trim(($parts[0] ?? '').' '.(count($parts) > 1 ? mb_substr(end($parts), 0, 1).'.' : ''));

        return [
            'sharet_id' => $user->sharet_id,
            'handle' => '@'.Handle::for($user),
            'name' => $masked,
            'verified' => in_array($user->kyc_level, ['basic', 'standard'], true),
            'can_receive' => ! $user->trashed() && $user->is_active !== false,
        ];
    }

    private function priced(int $amount, array $p, array $to): array
    {
        return [
            'amount' => PaymentInput::amount($amount), 'currency' => config('ledger.currency'),
            'fee' => PaymentInput::amount($p['fee']), 'excise' => PaymentInput::amount($p['excise']), 'rounding' => PaymentInput::amount($p['rounding']), 'total' => PaymentInput::amount($p['total']),
            'to' => $to,
            'expires_in_minutes' => (int) config('pricing.quote_minutes'),
        ];
    }
}
