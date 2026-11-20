<?php

namespace App\Api;

use App\Models\ApiConnection;
use App\Models\ApiKey;
use App\Models\User;
use App\Support\Handle;
use App\Support\SharetId;
use App\Support\TransactionPin;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Connected accounts (docs/v2/INTEGRATION.md §6).
 *  1. The platform asks for a link: POST /api/v1/connect/links ("connect" scope) with the scopes it wants.
 *  2. The person opens /connect/{token}, signs in, reads what the platform may do, and approves with their PIN.
 *  3. The platform acts for them with "Sharet-Account: SH-…", only within the approved scopes; their own limits apply.
 *  4. Either side can disconnect at any time (the person in Account › Connected apps).
 * Owner's rule (7 Nov): connected accounts receive and send; free inside Sharet, charged outside.
 */
final class Connections
{
    public const LINK_DAYS = 7;

    public function __construct(private Webhooks $webhooks) {}

    public function createLink(ApiKey $key, User $platform, array $input): ApiConnection
    {
        $scopes = array_values(array_intersect(ApiConnection::SCOPES, (array) ($input['scopes'] ?? [])));
        if (! $scopes) {
            throw new ApiError(422, 'invalid_scopes', 'What should you be able to do?', 'Ask for "collect", "payout" or both.');
        }
        $return = $input['return_url'] ?? null;
        if ($return !== null && ! Webhooks::urlAllowed((string) $return)) {
            throw new ApiError(422, 'invalid_return_url', 'Use a public HTTPS address', 'return_url must start with https:// and can\'t point at a private network.');
        }

        return ApiConnection::create([
            'public_id' => ($key->isLive() ? 'con_' : 'con_test_').Str::lower(Str::random(20)),
            'token' => Str::random(40),
            'platform_user_id' => $platform->id, 'api_key_id' => $key->id, 'environment' => $key->environment,
            'scopes' => $scopes, 'status' => 'pending',
            'reference' => filled($input['reference'] ?? null) ? Str::limit(strip_tags((string) $input['reference']), 140, '') : null,
            'return_url' => $return,
            'expires_at' => now()->addDays(self::LINK_DAYS),
        ]);
    }

    public function approve(ApiConnection $c, User $user, ?string $pin): ApiConnection
    {
        $this->assertPending($c);
        if ($user->is($c->platform) || $user->is_business_wallet) {
            throw ValidationException::withMessages(['connect' => 'You can\'t connect this account to itself.']);
        }
        TransactionPin::check($user, $pin);

        // One live connection per platform and person: a new approval replaces the old one
        ApiConnection::where('platform_user_id', $c->platform_user_id)->where('account_user_id', $user->id)
            ->where('environment', $c->environment)->where('status', 'active')->update(['status' => 'revoked', 'revoked_at' => now()]);
        $c->update(['account_user_id' => $user->id, 'status' => 'active', 'approved_at' => now()]);
        $this->webhooks->fire($c->platform, $c->environment, 'account.connected', self::present($c->fresh()));

        return $c->fresh();
    }

    public function decline(ApiConnection $c, User $user): ApiConnection
    {
        $this->assertPending($c);
        $c->update(['account_user_id' => $user->id, 'status' => 'declined']);

        return $c->fresh();
    }

    /** Either side disconnects. The platform is told (account.disconnected). */
    public function revoke(ApiConnection $c): ApiConnection
    {
        if ($c->status === 'active') {
            $c->update(['status' => 'revoked', 'revoked_at' => now()]);
            $this->webhooks->fire($c->platform, $c->environment, 'account.disconnected', self::present($c->fresh()));
        }

        return $c->fresh();
    }

    /** The account a request acts for when it sends "Sharet-Account": an active connection of this platform, or a 403. */
    public function forRequest(ApiKey $key, User $platform, string $header): ApiConnection
    {
        $id = SharetId::normalise($header);
        $c = $id ? ApiConnection::where('platform_user_id', $platform->id)->where('environment', $key->environment)->where('status', 'active')
            ->whereHas('account', fn ($q) => $q->where('sharet_id', $id))->with('account')->first() : null;
        if (! $c) {
            throw new ApiError(403, 'not_connected', 'That account isn\'t connected to you', 'Send a Sharet ID that approved a connect link from your business (and is still connected).');
        }

        return $c;
    }

    public static function present(ApiConnection $c): array
    {
        return [
            'id' => $c->public_id,
            'object' => 'connection',
            'environment' => $c->environment,
            'status' => $c->status,
            'scopes' => $c->scopes,
            'account' => $c->account ? ['sharet_id' => $c->account->sharet_id, 'handle' => '@'.Handle::for($c->account)] : null,
            'reference' => $c->reference,
            'url' => $c->status === 'pending' ? route('connect.show', $c->token) : null,
            'expires_at' => $c->status === 'pending' ? $c->expires_at->toIso8601String() : null,
            'approved_at' => $c->approved_at?->toIso8601String(),
            'revoked_at' => $c->revoked_at?->toIso8601String(),
        ];
    }

    private function assertPending(ApiConnection $c): void
    {
        if ($c->status !== 'pending' || $c->expires_at->isPast()) {
            throw ValidationException::withMessages(['connect' => 'This link has already been used or has expired. Ask for a new one.']);
        }
    }
}
