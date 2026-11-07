<?php

namespace App\Api;

use App\Ledger\LedgerException;
use App\Ledger\Money;
use App\Models\User;
use App\Ramp\Destination;
use App\Support\Handle;
use App\Support\SharetId;
use Illuminate\Validation\ValidationException;

/**
 * Reads "amount" and "to" from a quote or payment request (docs/v2/INTEGRATION.md §4): whole shillings, and a
 * destination that is a Sharet account (by sharet_id or handle, resolved to the Sharet ID) or an outside rail.
 * Throws ApiError with a stable type; never moves money.
 */
final class PaymentInput
{
    public const TYPES = ['sharet', 'mpesa', 'airtel', 'tkash', 'paybill', 'till', 'bank'];

    /** @return array{cents: int, type: string, destination: array, recipient: ?User} */
    public static function read(array $input, User $payer): array
    {
        $to = (array) ($input['to'] ?? []);
        $type = $to['type'] ?? (isset($to['sharet_id']) || isset($to['handle']) ? 'sharet' : null);
        if (! $type || ! in_array($type, self::TYPES, true)) {
            throw new ApiError(422, 'destination_required', 'Where should it go?', 'Send "to" with a sharet_id, a handle, or a type (mpesa, airtel, tkash, paybill, till, bank).');
        }

        try {
            $cents = Money::cents((string) ($input['amount'] ?? ''));
        } catch (LedgerException) {
            throw new ApiError(422, 'invalid_amount', 'That amount isn\'t valid', 'Send the amount as a string, e.g. "1500.00".');
        }
        if ($cents % 100 !== 0 || $cents < config('pricing.min_amount')) {
            throw new ApiError(422, 'invalid_amount', 'That amount isn\'t valid', 'Whole shillings, at least KES '.Money::format(config('pricing.min_amount')).'.');
        }

        if ($type === 'sharet') {
            $recipient = self::account($to['sharet_id'] ?? null, $to['handle'] ?? null)
                ?? throw new ApiError(404, 'account_not_found', 'No such Sharet account', 'Check the @name or Sharet ID.');
            if ($recipient->is($payer)) {
                throw new ApiError(422, 'own_account', 'That\'s your own account', 'Pay a different Sharet ID.');
            }

            return ['cents' => $cents, 'type' => 'sharet', 'destination' => ['sharet_id' => $recipient->sharet_id], 'recipient' => $recipient];
        }

        try {
            [$destination] = Destination::normalise(self::rampType($type), $to);
        } catch (ValidationException $e) {
            throw new ApiError(422, 'invalid_destination', 'Check the destination', (string) collect($e->errors())->flatten()->first(), ['errors' => $e->errors()]);
        }

        return ['cents' => $cents, 'type' => $type, 'destination' => $destination, 'recipient' => null];
    }

    /** M-Pesa, Airtel and T-Kash are all phone payouts to the order engine. */
    public static function rampType(string $type): string
    {
        return in_array($type, ['mpesa', 'airtel', 'tkash'], true) ? 'phone' : $type;
    }

    public static function account(?string $sharetId, ?string $handle): ?User
    {
        if ($sharetId) {
            $id = SharetId::normalise($sharetId);

            return $id ? User::where('sharet_id', $id)->first() : null;
        }

        return $handle ? Handle::find($handle) : null;
    }

    /** API amounts are plain strings with two decimals and no separators: "5000.00" (§9). */
    public static function amount(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
