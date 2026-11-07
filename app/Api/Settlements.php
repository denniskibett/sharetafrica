<?php

namespace App\Api;

use App\Models\ApiKey;
use App\Models\ApiPayment;
use App\Models\ApiSettlement;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Batch settlements: many payments in one call (docs/v2/INTEGRATION.md §4). Each item is paid like POST /payments
 * (Sharet IDs free and instant, outside payouts charged). Every item is checked before anything moves. Live batches above
 * the threshold (KES 500,000 by default) wait for a second person from the same business to approve them in the
 * dashboard (Settlements), never the key's owner.
 */
final class Settlements
{
    public const MAX_ITEMS = 500;

    public function __construct(private Payments $payments, private Webhooks $webhooks) {}

    public static function threshold(): int
    {
        return (int) config('api.second_approval_cents', 50_000_000);
    }

    public function create(ApiKey $key, User $user, array $input): ApiSettlement
    {
        $items = array_values((array) ($input['items'] ?? []));
        if (count($items) < 1 || count($items) > self::MAX_ITEMS) {
            throw new ApiError(422, 'invalid_items', 'Check the items', 'Send 1 to '.self::MAX_ITEMS.' items.');
        }
        if ($key->isLive() && ! $key->company?->verified_at) {
            throw new ApiError(403, 'business_not_verified', 'Your business isn\'t verified yet', 'Live settlements need a verified business. Use a sk_test_ key meanwhile.');
        }

        $total = 0;
        $errors = [];
        foreach ($items as $i => $item) {
            try {
                $total += PaymentInput::read((array) $item, $user)['cents'];
            } catch (ApiError $e) {
                $errors["items.$i"] = [$e->detail];
            }
        }
        if ($errors) {
            throw new ApiError(422, 'invalid_items', 'Some items need fixing', 'Nothing was paid. Fix the items listed in "errors" and send the batch again.', ['errors' => $errors]);
        }

        $needsApproval = $key->isLive() && $total > self::threshold();
        $s = ApiSettlement::create([
            'public_id' => ($key->isLive() ? 'set_' : 'set_test_').Str::lower(Str::random(20)),
            'api_key_id' => $key->id, 'user_id' => $user->id, 'environment' => $key->environment,
            'status' => $needsApproval ? 'awaiting_approval' : 'processing',
            'items' => array_map(fn ($it) => array_intersect_key((array) $it, array_flip(['amount', 'to', 'reference'])), $items),
            'item_count' => count($items), 'total' => $total,
            'reference' => filled($input['reference'] ?? null) ? Str::limit(strip_tags((string) $input['reference']), 140, '') : null,
        ]);

        if ($needsApproval) {
            $this->webhooks->fire($user, $s->environment, 'settlement.awaiting_approval', self::present($s));

            return $s;
        }

        return $this->run($s);
    }

    /** A second person from the same business approves (never the key's owner); then it runs. */
    public function approve(ApiSettlement $s, User $approver): ApiSettlement
    {
        $this->assertApprover($s, $approver);
        $s->update(['approved_by' => $approver->id, 'approved_at' => now(), 'status' => 'processing']);

        return $this->run($s->fresh());
    }

    public function reject(ApiSettlement $s, User $approver): ApiSettlement
    {
        $this->assertApprover($s, $approver);
        $s->update(['approved_by' => $approver->id, 'approved_at' => now(), 'status' => 'rejected']);
        $this->webhooks->fire($s->user, $s->environment, 'settlement.failed', self::present($s->fresh()) + ['failure' => 'Rejected by '.$approver->name.'.']);

        return $s->fresh();
    }

    public static function canApprove(ApiSettlement $s, User $u): bool
    {
        $company = $s->key?->company_id;

        return $s->status === 'awaiting_approval' && $company && $u->company_id === $company
            && $u->id !== $s->user_id && $u->id !== $s->key->user_id && $u->can('developer.api_keys');
    }

    /** Work out the batch's status from its payments, and tell the business once it's finished. */
    public function refresh(ApiSettlement $s): ApiSettlement
    {
        if (! in_array($s->status, ['processing'], true)) {
            return $s;
        }
        $statuses = $s->payments()->pluck('status');
        if ($statuses->count() < $s->item_count || $statuses->contains('paying')) {
            return $s;
        }
        $paid = $statuses->filter(fn ($st) => $st === 'paid')->count();
        $status = $paid === $s->item_count ? 'completed' : ($paid === 0 ? 'failed' : 'partially_failed');
        $s->update(['status' => $status]);
        $this->webhooks->fire($s->user, $s->environment, $status === 'failed' ? 'settlement.failed' : 'settlement.completed', self::present($s->fresh()));

        return $s->fresh();
    }

    public static function present(ApiSettlement $s): array
    {
        $payments = $s->payments()->get();

        return [
            'id' => $s->public_id,
            'object' => 'settlement',
            'environment' => $s->environment,
            'status' => $s->status,
            'item_count' => $s->item_count,
            'total' => PaymentInput::amount($s->total),
            'currency' => config('ledger.currency'),
            'reference' => $s->reference,
            'paid' => $payments->where('status', 'paid')->count(),
            'failed' => $payments->where('status', 'failed')->count(),
            'payments' => $payments->map(fn (ApiPayment $p) => Payments::present($p))->values(),
            'approved_by' => $s->approver?->name,
            'approved_at' => $s->approved_at?->toIso8601String(),
            'created_at' => $s->created_at?->toIso8601String(),
        ];
    }

    private function run(ApiSettlement $s): ApiSettlement
    {
        foreach ($s->items as $item) {
            $this->payments->createInSettlement($s->key, $s->user, $item, $s->id);
        }

        return $this->refresh($s->fresh());
    }

    private function assertApprover(ApiSettlement $s, User $u): void
    {
        if (! self::canApprove($s, $u)) {
            throw new ApiError(403, 'not_an_approver', 'You can\'t approve this', 'A different person from the same business, with access to API keys, must approve it.');
        }
    }
}
