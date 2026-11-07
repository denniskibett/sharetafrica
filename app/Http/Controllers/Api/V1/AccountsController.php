<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Ledger\Ledger;
use App\Ledger\LedgerException;
use App\Ledger\Money;
use App\Models\User;
use App\Ramp\Destination;
use App\Ramp\Pricing;
use App\Support\ApiProblem;
use App\Support\Handle;
use App\Support\SharetId;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            'balance' => ['currency' => config('ledger.currency'), 'available' => self::amount($ledger->account('2100', $user)->available())],
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

    /** POST /api/v1/quotes: the exact fee and total for a payment, without sending it. */
    public function quote(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'string', 'max:20'],
            'currency' => ['nullable', 'in:'.config('ledger.currency')],
            'to' => ['required', 'array'],
            'to.type' => ['nullable', 'in:sharet,mpesa,airtel,tkash,paybill,till,bank'],
        ]);
        $to = (array) $request->input('to');
        $type = $to['type'] ?? (isset($to['sharet_id']) || isset($to['handle']) ? 'sharet' : null);
        if (! $type) {
            return ApiProblem::make(422, 'destination_required', 'Where should it go?', 'Send "to" with a sharet_id, a handle, or a type (mpesa, airtel, tkash, paybill, till, bank).');
        }

        try {
            $cents = Money::cents($data['amount']);
        } catch (LedgerException) {
            return ApiProblem::make(422, 'invalid_amount', 'That amount isn\'t valid', 'Send the amount as a string, e.g. "1500.00".');
        }
        if ($cents % 100 !== 0 || $cents < config('pricing.min_amount')) {
            return ApiProblem::make(422, 'invalid_amount', 'That amount isn\'t valid', 'Whole shillings, at least KES '.Money::format(config('pricing.min_amount')).'.');
        }

        if ($type === 'sharet') {
            $user = $this->find($to['sharet_id'] ?? null, $to['handle'] ?? null);
            if (! $user) {
                return ApiProblem::make(404, 'account_not_found', 'No such Sharet account', 'Check the @name or Sharet ID.');
            }
            if ($user->is($request->user())) {
                return ApiProblem::make(422, 'own_account', 'That\'s your own account', 'Pay a different Sharet ID.');
            }

            return response()->json($this->priced($cents, ['fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => $cents], ['type' => 'sharet'] + $this->present($user)));
        }

        try {
            [$destination] = Destination::normalise(in_array($type, ['mpesa', 'airtel', 'tkash'], true) ? 'phone' : $type, $to);
        } catch (ValidationException $e) {
            return ApiProblem::make(422, 'invalid_destination', 'Check the destination', collect($e->errors())->flatten()->first(), ['errors' => $e->errors()]);
        }

        return response()->json($this->priced($cents, Pricing::quote($cents), ['type' => $type] + $destination));
    }

    private function find(?string $sharetId, ?string $handle): ?User
    {
        if ($sharetId) {
            $id = SharetId::normalise($sharetId);

            return $id ? User::where('sharet_id', $id)->first() : null;
        }

        return $handle ? Handle::find($handle) : null;
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

    /** API amounts are plain strings with two decimals and no separators: "5000.00" (INTEGRATION.md §9). */
    public static function amount(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function priced(int $amount, array $p, array $to): array
    {
        return [
            'amount' => self::amount($amount), 'currency' => config('ledger.currency'),
            'fee' => self::amount($p['fee']), 'excise' => self::amount($p['excise']), 'rounding' => self::amount($p['rounding']), 'total' => self::amount($p['total']),
            'tariff' => $to['type'] === 'sharet' ? 'sharet_free' : 'standard',
            'to' => $to,
            'expires_in_minutes' => (int) config('pricing.quote_minutes'),
        ];
    }
}
