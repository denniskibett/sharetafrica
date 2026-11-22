<?php

namespace App\Business;

use App\Api\ApiError;
use App\Api\PaymentInput;
use App\Api\Tariffs;
use App\Aml\Screening;
use App\Ledger\Money;
use App\Models\BusinessBatch;
use App\Models\BusinessBatchLine;
use App\Models\BusinessMember;
use App\Models\Company;
use App\Models\RampOrder;
use App\Ramp\RampOrders;
use App\Support\Handle;
use App\Support\TransactionPin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Bulk payouts from a company wallet, two people per payout (canvas "Phase 2 · Mobile app · Business"):
 *
 *   Prepare  a maker (or the owner) sends a list: every line is checked and priced from the business tariff
 *            (volume discount) before anyone approves. Duplicates and new payees are flagged.
 *   Approve  a different person (owner or checker) approves with their PIN, within their approval limit. Above
 *            config business.two_checkers_above two different checkers must approve. A batch paying someone new
 *            waits business.new_payee_hours unless the owner approves it.
 *   Pay      each line is its own order from the company wallet through the order engine (ledger, limits, AML,
 *            rails unchanged); Sharet accounts are paid wallet to wallet, free. A line that fails comes back to the
 *            wallet in full, fee included.
 */
final class Batches
{
    public function __construct(private RampOrders $orders, private Tariffs $tariffs) {}

    /** @param array $lines [{name, to: {type, phone|paybill+account|till|bank+account|handle|sharet_id}, amount}] */
    public function create(BusinessMember $maker, string $name, array $lines): BusinessBatch
    {
        Team::authorize($maker, 'prepare');
        $company = Company::findOrFail($maker->company_id);
        $this->assertVerified($company);
        $wallet = $company->walletUser();
        if (count($lines) < 1 || count($lines) > config('business.max_lines')) {
            throw ValidationException::withMessages(['lines' => 'Send 1 to '.config('business.max_lines').' people.']);
        }

        $rows = [];
        $errors = [];
        foreach (array_values($lines) as $i => $line) {
            try {
                $in = PaymentInput::read((array) $line, $wallet);
                $price = $this->tariffs->quote($wallet, $in['type'], $in['cents']);
                $rows[] = ['line' => $i + 1, 'name' => filled($line['name'] ?? null) ? Str::limit(strip_tags((string) $line['name']), 120, '') : null,
                    'type' => $in['type'], 'to' => ['type' => $in['type']] + $in['destination'] + ($in['recipient'] ? ['handle' => Handle::for($in['recipient'])] : []),
                    'payee_key' => self::payeeKey($in['type'], $in['destination']), 'amount' => $in['cents'], 'fee' => $price['total'] - $in['cents']];
            } catch (ApiError $e) {
                $errors['lines.'.$i] = ['Line '.($i + 1).': '.$e->detail];
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        // Flags, before anyone approves: the same person twice in this batch, already paid this month, or never paid before
        $keys = array_count_values(array_column($rows, 'payee_key'));
        $past = BusinessBatchLine::query()->whereIn('payee_key', array_keys($keys))->where('status', 'paid')
            ->whereHas('batch', fn ($q) => $q->where('company_id', $company->id))->get(['payee_key', 'created_at']);
        $monthStart = now('Africa/Nairobi')->startOfMonth()->utc();
        foreach ($rows as &$r) {
            $r['flags'] = array_values(array_filter([
                $keys[$r['payee_key']] > 1 ? 'duplicate_in_batch' : null,
                $past->where('payee_key', $r['payee_key'])->where('created_at', '>=', $monthStart)->isNotEmpty() ? 'paid_this_month' : null,
                $past->where('payee_key', $r['payee_key'])->isEmpty() ? 'new_payee' : null,
            ]));
        }
        unset($r);

        $amount = array_sum(array_column($rows, 'amount'));
        $fees = array_sum(array_column($rows, 'fee'));

        return DB::transaction(function () use ($company, $wallet, $maker, $name, $rows, $amount, $fees) {
            $batch = BusinessBatch::create([
                'public_id' => 'bat_'.Str::lower(Str::random(20)), 'company_id' => $company->id, 'wallet_user_id' => $wallet->id,
                'name' => Str::limit(strip_tags(trim($name)) ?: 'Payments '.now('Africa/Nairobi')->format('j M'), 80, ''), 'status' => 'awaiting_approval',
                'item_count' => count($rows), 'amount' => $amount, 'fees' => $fees, 'total' => $amount + $fees,
                'approvals_needed' => $amount + $fees > (int) config('business.two_checkers_above') ? 2 : 1, 'approvals' => [], 'created_by' => $maker->user_id,
            ]);
            foreach ($rows as $r) {
                $batch->lines()->create($r);
            }

            return $batch;
        });
    }

    /** Lines from a pasted or uploaded list: "name, number or account, amount" per line (a header row is skipped). */
    public static function fromCsv(string $csv): array
    {
        $lines = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($csv)) as $n => $raw) {
            if (trim($raw) === '') {
                continue;
            }
            $cols = array_map('trim', str_getcsv($raw));
            if (count($cols) < 3) {
                throw ValidationException::withMessages(['csv' => 'Row '.($n + 1).': write name, number or account, amount.']);
            }
            [$name, $where, $amount] = [$cols[0], $cols[1], str_replace([',', ' ', 'KES', 'kes'], '', $cols[2])];
            if ($n === 0 && ! is_numeric($amount)) {
                continue;   // header row
            }
            $lines[] = ['name' => $name, 'amount' => $amount, 'to' => self::destination($where)];
        }

        return $lines;
    }

    /** "0712 345 678" · "@jane.w" · "SH-4K7Q2M" · "247247#0123456" (paybill#account) · "till 123456" · "kcb:0123456789" (bank:account) */
    public static function destination(string $where): array
    {
        $w = trim($where);

        return match (true) {
            str_starts_with($w, '@') => ['type' => 'sharet', 'handle' => $w],
            (bool) preg_match('/^SH-/i', $w) => ['type' => 'sharet', 'sharet_id' => $w],
            str_contains($w, '#') => ['type' => 'paybill', 'paybill' => trim(Str::before($w, '#')), 'account' => trim(Str::after($w, '#'))],
            (bool) preg_match('/^till\s*(\d+)$/i', $w, $m) => ['type' => 'till', 'till' => $m[1]],
            (bool) preg_match('/^([a-z_]+):\s*(\d+)$/i', $w, $m) => ['type' => 'bank', 'bank' => strtolower($m[1]), 'account' => $m[2]],
            default => ['type' => 'mpesa', 'phone' => $w],
        };
    }

    /** A second person approves with their PIN. When enough have approved, it pays at once. */
    public function approve(BusinessMember $checker, BusinessBatch $batch, ?string $pin): BusinessBatch
    {
        Team::authorize($checker, 'approve');
        $this->own($checker, $batch);
        if ($batch->status !== 'awaiting_approval') {
            Team::deny('not_awaiting_approval', 'This batch is not waiting for approval.', 409);
        }
        if ($batch->created_by === $checker->user_id) {
            Team::deny('maker_cannot_approve', 'You prepared this batch: someone else must approve it.');
        }
        if (collect($batch->approvals)->contains('user_id', $checker->user_id)) {
            Team::deny('already_approved', 'You have approved this batch already. It needs a second checker.', 409);
        }
        $limit = Team::approvalLimit($checker);
        if ($limit !== null && $batch->total > $limit) {
            Team::deny('over_your_limit', 'This batch is over your approval limit of KES '.Money::format($limit).'. Ask the owner or a checker with a higher limit.');
        }
        $new = $batch->lines()->get()->filter(fn ($l) => in_array('new_payee', (array) $l->flags, true))->count();
        $wait = $batch->created_at->copy()->addHours((int) config('business.new_payee_hours'));
        if ($new && $checker->role !== 'owner' && $wait->isFuture()) {
            Team::deny('new_payee_cool_off', $new.' new '.Str::plural('payee', $new).' in this batch: the owner can approve it now, or a checker after '
                .$wait->timezone('Africa/Nairobi')->format('j M H:i').'.');
        }
        TransactionPin::check($checker->user, $pin);
        $balance = $this->orders->walletBalance($batch->wallet);
        $last = count((array) $batch->approvals) + 1 >= $batch->approvals_needed;
        if ($last && $balance < $batch->total) {
            throw ValidationException::withMessages(['amount' => 'The company wallet has KES '.Money::format($balance).'; this batch needs KES '.Money::format($batch->total).'.']);
        }

        $ready = DB::transaction(function () use ($batch, $checker) {
            $b = BusinessBatch::whereKey($batch->id)->lockForUpdate()->first();
            if ($b->status !== 'awaiting_approval') {
                return false;
            }
            $approvals = (array) $b->approvals;
            $approvals[] = ['user_id' => $checker->user_id, 'name' => $checker->user->name, 'at' => now()->toIso8601String()];
            $done = count($approvals) >= $b->approvals_needed;
            $b->update(['approvals' => $approvals, 'status' => $done ? 'processing' : 'awaiting_approval', 'started_at' => $done ? now() : null]);

            return $done;
        });

        return $ready ? $this->run($batch->fresh()) : $batch->fresh();
    }

    public function reject(BusinessMember $checker, BusinessBatch $batch, string $reason): BusinessBatch
    {
        Team::authorize($checker, 'approve');
        $this->own($checker, $batch);
        if ($batch->status !== 'awaiting_approval') {
            Team::deny('not_awaiting_approval', 'This batch is not waiting for approval.', 409);
        }
        if ($batch->created_by === $checker->user_id) {
            Team::deny('maker_cannot_approve', 'You prepared this batch: cancel it instead.');
        }
        $batch->update(['status' => 'rejected', 'rejected_by' => $checker->user_id, 'reject_reason' => Str::limit(strip_tags($reason), 200, '')]);

        return $batch->fresh();
    }

    /** The maker (or the owner) withdraws a batch nobody has finished approving. */
    public function cancel(BusinessMember $m, BusinessBatch $batch): BusinessBatch
    {
        $this->own($m, $batch);
        if ($batch->created_by !== $m->user_id && $m->role !== 'owner') {
            Team::deny('not_allowed', 'Only whoever prepared it, or the owner, can cancel a batch.');
        }
        if ($batch->status !== 'awaiting_approval') {
            Team::deny('not_awaiting_approval', 'Only a batch waiting for approval can be cancelled.', 409);
        }
        $batch->update(['status' => 'cancelled']);

        return $batch->fresh();
    }

    /** Pay every line as its own order. A refused line is recorded as failed; nothing was taken for it. */
    private function run(BusinessBatch $batch): BusinessBatch
    {
        $wallet = $batch->wallet;
        $note = Str::limit($batch->name, 140, '');
        foreach ($batch->lines()->where('status', 'pending')->get() as $line) {
            try {
                if ($line->type === 'sharet') {
                    $order = $this->orders->transfer($wallet, $line->to['handle'], PaymentInput::amount($line->amount), $note);
                } else {
                    $price = $this->tariffs->quote($wallet, $line->type, $line->amount);
                    $to = array_diff_key($line->to, ['type' => 1]);
                    $order = $this->orders->confirm($this->orders->quote($wallet, PaymentInput::rampType($line->type), $to, PaymentInput::amount($line->amount), $note, 'wallet', $price));
                }
                $line->update(['ramp_order_id' => $order->id, 'fee' => $order->fee + $order->excise + $order->rounding, 'status' => self::lineStatus($order), 'failure' => self::failure($order)]);
            } catch (ValidationException $e) {
                $line->update(['status' => 'failed', 'failure' => Str::limit((string) collect($e->errors())->flatten()->first(), 200, '')]);
            }
        }

        return $this->refresh($batch->fresh());
    }

    /** Ask each unfinished line's order where it is, then settle the batch's status. Safe to call any time. */
    public function refresh(BusinessBatch $batch): BusinessBatch
    {
        if ($batch->status !== 'processing') {
            return $batch;
        }
        foreach ($batch->lines()->where('status', 'paying')->with('order')->get() as $line) {
            $order = $line->order;
            if ($order && ! $order->final()) {
                $order = $this->orders->refresh($order);
            }
            $order && $line->update(['status' => self::lineStatus($order), 'failure' => self::failure($order)]);
        }
        $statuses = $batch->lines()->pluck('status');
        if ($statuses->contains(fn ($s) => in_array($s, ['pending', 'paying'], true))) {
            return $batch->fresh();
        }
        $paid = $statuses->filter(fn ($s) => $s === 'paid')->count();
        $batch->update(['status' => $paid === $statuses->count() ? 'completed' : ($paid === 0 ? 'failed' : 'partially_failed')]);

        return $batch->fresh();
    }

    public function present(BusinessBatch $b, ?BusinessMember $viewer = null, bool $lines = true): array
    {
        $all = $b->lines()->get();
        $flagged = $all->filter(fn ($l) => ! empty($l->flags) && array_diff((array) $l->flags, ['new_payee']))->count();

        return [
            'id' => $b->public_id, 'name' => $b->name, 'status' => $b->status,
            'people' => $b->item_count, 'amount' => Money::format($b->amount), 'fees' => Money::format($b->fees), 'total' => Money::format($b->total),
            'currency' => config('ledger.currency'),
            'prepared_by' => $b->maker?->name, 'prepared_at' => $b->created_at?->toIso8601String(),
            'approvals_needed' => $b->approvals_needed, 'approvals' => collect($b->approvals)->map(fn ($a) => ['name' => $a['name'], 'at' => $a['at']])->values(),
            'rejected_by' => $b->rejecter?->name, 'reject_reason' => $b->reject_reason,
            'flagged' => $flagged, 'new_payees' => $all->filter(fn ($l) => in_array('new_payee', (array) $l->flags, true))->count(),
            'paid' => ['count' => $all->where('status', 'paid')->count(), 'amount' => Money::format((int) $all->where('status', 'paid')->sum('amount'))],
            'returned' => ['count' => $all->whereIn('status', ['failed', 'returned'])->count(), 'amount' => Money::format((int) $all->whereIn('status', ['failed', 'returned'])->sum('amount'))],
            'can_approve' => $viewer ? $this->canApprove($viewer, $b) : false,
            'can_cancel' => $viewer && $b->status === 'awaiting_approval' && ($b->created_by === $viewer->user_id || $viewer->role === 'owner'),
        ] + ($lines ? ['lines' => $all->map(fn (BusinessBatchLine $l) => [
            'line' => $l->line, 'name' => $l->name, 'to' => self::describe($l), 'type' => $l->type,
            'amount' => Money::format($l->amount), 'fee' => Money::format($l->fee), 'flags' => (array) $l->flags,
            'status' => $l->status, 'failure' => $l->failure, 'reference' => $l->order?->sharetReference(), 'order' => $l->order?->uuid,
        ])->values()] : []);
    }

    /** The batch's report as CSV: one row per line, with both references. */
    public function csv(BusinessBatch $b): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['line', 'name', 'to', 'amount', 'fee', 'status', 'sharet_reference', 'network_reference', 'failure']);
        foreach ($b->lines()->with('order')->get() as $l) {
            fputcsv($out, [$l->line, $l->name, self::describe($l), PaymentInput::amount($l->amount), PaymentInput::amount($l->fee), $l->status,
                $l->order?->sharetReference(), $l->order?->reference(), $l->failure]);
        }
        rewind($out);

        return stream_get_contents($out);
    }

    public function canApprove(BusinessMember $m, BusinessBatch $b): bool
    {
        $limit = Team::approvalLimit($m);

        return Team::can($m, 'approve') && $b->status === 'awaiting_approval' && $b->company_id === $m->company_id && $b->created_by !== $m->user_id
            && ! collect($b->approvals)->contains('user_id', $m->user_id) && ($limit === null || $b->total <= $limit);
    }

    /** People this business has paid before (the most recent line for each), to pick for a new batch. */
    public static function savedPeople(int $companyId, int $limit = 50): array
    {
        return BusinessBatchLine::query()->where('status', 'paid')->whereHas('batch', fn ($q) => $q->where('company_id', $companyId))
            ->latest('id')->limit(500)->get()->unique('payee_key')->take($limit)
            ->map(fn ($l) => ['name' => $l->name, 'to' => $l->to, 'label' => self::describe($l), 'last_amount' => PaymentInput::amount($l->amount)])->values()->all();
    }

    public static function describe(BusinessBatchLine $l): string
    {
        $t = $l->to;

        return match ($l->type) {
            'sharet' => '@'.($t['handle'] ?? $t['sharet_id'] ?? ''),
            'paybill' => 'Paybill '.($t['paybill'] ?? '').' · '.($t['account'] ?? ''),
            'till' => 'Till '.($t['till'] ?? ''),
            'bank' => (config('pricing.banks')[$t['bank'] ?? ''] ?? 'Bank').' · '.($t['account'] ?? ''),
            default => '+'.($t['phone'] ?? ''),
        };
    }

    public static function payeeKey(string $type, array $d): string
    {
        $v = $d['sharet_id'] ?? $d['phone'] ?? $d['paybill'] ?? $d['till'] ?? $d['bank'] ?? '';

        return Str::limit($type.':'.Screening::norm((string) $v).':'.Screening::norm((string) ($d['account'] ?? '')), 120, '');
    }

    private static function lineStatus(RampOrder $o): string
    {
        return match ($o->status) {
            'paid' => 'paid',
            'refunded' => 'returned',
            'failed', 'rejected', 'expired', 'cancelled', 'reversed', 'refund_failed' => 'failed',
            default => 'paying',   // quoted, paying, held (being checked), refunding
        };
    }

    private static function failure(RampOrder $o): ?string
    {
        return in_array($o->status, ['refunded', 'failed', 'rejected', 'refund_failed'], true) ? Str::limit((string) ($o->failure ?? 'Not paid'), 200, '') : null;
    }

    private function own(BusinessMember $m, BusinessBatch $b): void
    {
        abort_unless($b->company_id === $m->company_id, 404);
    }

    private function assertVerified(Company $company): void
    {
        if (! $company->verified_at) {
            Team::deny('business_not_verified', 'Your business isn\'t verified yet. Payouts open once Sharet has checked its documents (Business settings on the web).');
        }
    }
}
