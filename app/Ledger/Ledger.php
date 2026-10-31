<?php

namespace App\Ledger;

use App\Models\LedgerAccount;
use App\Models\LedgerHold;
use App\Models\LedgerJournal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * The only way money moves in Sharet (docs/v2/LEDGER.md).
 *
 *  - Every journal balances: debits = credits, in one currency, at least two lines, no zero lines.
 *  - Idempotent: the same key always returns the same journal; the same key with different lines is refused.
 *  - No account that holds real money or owes a customer may go below zero (after its holds).
 *  - Insert-only: journals and postings are never changed (database triggers refuse it). Mistakes are reversed.
 *  - Every journal is chained to the previous one by hash, so a row slipped in or altered outside this class shows up
 *    in `ledger:check`.
 *  - Locks are always taken in the same order (chain → holds → accounts by id) so two postings never deadlock.
 */
class Ledger
{
    private const UUID_NAMESPACE = 'b2d6f1a4-6c2e-4f0e-9d4b-5a3c1e7f8a20'; // fixed forever once live

    // ------------------------------------------------------------------ accounts

    /** A system account (from config ledger.accounts) or, with an owner, that owner's account (ledger.templates). */
    public function account(string $code, ?Model $owner = null, ?string $currency = null): LedgerAccount
    {
        $currency ??= config('ledger.currency');
        $def = $owner ? config("ledger.templates.{$code}") : config("ledger.accounts.{$code}");
        if (! $def) {
            throw LedgerException::make('unknown_account', $owner ? "No account template {$code}." : "No system account {$code}.");
        }
        $key = $owner ? $owner->getMorphClass().':'.$owner->getKey() : 'system';

        $find = fn () => LedgerAccount::where(['code' => $code, 'currency' => $currency, 'owner_key' => $key])->first();
        if ($account = $find()) {
            return $account;
        }

        try {
            return LedgerAccount::create([
                'code' => $code, 'name' => $def['name'], 'type' => $def['type'], 'book' => $def['book'], 'currency' => $currency,
                'owner_key' => $key, 'owner_type' => $owner?->getMorphClass(), 'owner_id' => $owner?->getKey(),
                'allow_negative' => (bool) ($def['allow_negative'] ?? false),
            ]);
        } catch (UniqueConstraintViolationException) {
            return $find(); // created by a parallel request
        }
    }

    public static function debit(LedgerAccount|int $account, int $cents): array
    {
        return ['account' => $account, 'amount' => self::positive($cents)];
    }

    public static function credit(LedgerAccount|int $account, int $cents): array
    {
        return ['account' => $account, 'amount' => -self::positive($cents)];
    }

    // ------------------------------------------------------------------ posting

    /**
     * Post one balanced journal. $legs: [Ledger::debit($a, 1000), Ledger::credit($b, 1000), ...].
     * $options: memo, currency, source (a model), source_type, source_id, created_by, approved_by, meta.
     */
    public function post(string $key, string $type, array $legs, array $options = []): LedgerJournal
    {
        $currency = $options['currency'] ?? config('ledger.currency');
        $lines = $this->lines($legs);
        $payload = $this->payloadHash($type, $currency, $lines);

        if ($existing = $this->existing($key, $payload)) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($key, $type, $currency, $lines, $payload, $options) {
                $chain = DB::table('ledger_chain')->where('id', 1)->lockForUpdate()->first();
                if ($existing = $this->existing($key, $payload)) {
                    return $existing;
                }

                $ids = array_values(array_unique(array_column($lines, 'account_id')));
                sort($ids);
                $accounts = LedgerAccount::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                $delta = [];
                foreach ($lines as $line) {
                    $delta[$line['account_id']] = ($delta[$line['account_id']] ?? 0) + $line['amount'];
                }
                foreach ($ids as $id) {
                    $account = $accounts[$id] ?? throw LedgerException::make('unknown_account', "Ledger account {$id} does not exist.");
                    if (! $account->is_active) {
                        throw LedgerException::make('inactive_account', "Account {$account->label()} is closed.");
                    }
                    if ($account->currency !== $currency) {
                        throw LedgerException::make('currency_mismatch', "Account {$account->label()} is in {$account->currency}, the journal in {$currency}.");
                    }
                    $after = $account->natural($account->balance + $delta[$id]);
                    if (! $account->allow_negative && $after < $account->natural() && $after - $account->held < 0) {
                        throw LedgerException::make('insufficient_funds', "Account {$account->label()} would go below zero: available "
                            .Money::format($account->available()).', change '.Money::format($after - $account->natural()).'.');
                    }
                }

                $postedAt = now()->format('Y-m-d H:i:s.u');
                $uuid = Uuid::uuid5(self::UUID_NAMESPACE, $key)->toString();
                $hash = self::hash($chain->last_hash, $uuid, $key, $type, $currency, $postedAt, $lines);
                $source = $options['source'] ?? null;

                $journal = LedgerJournal::create([
                    'uuid' => $uuid,
                    'idempotency_key' => $key,
                    'payload_hash' => $payload,
                    'type' => $type,
                    'currency' => $currency,
                    'memo' => $options['memo'] ?? null,
                    'source_type' => $source ? $source->getMorphClass() : ($options['source_type'] ?? null),
                    'source_id' => $source ? $source->getKey() : ($options['source_id'] ?? null),
                    'reverses_id' => $options['reverses_id'] ?? null,
                    'created_by' => $options['created_by'] ?? auth()->id(),
                    'approved_by' => $options['approved_by'] ?? null,
                    'meta' => $options['meta'] ?? null,
                    'prev_hash' => $chain->last_hash,
                    'hash' => $hash,
                    'posted_at' => $postedAt,
                ]);

                DB::table('ledger_postings')->insert(array_map(fn ($l) => [
                    'journal_id' => $journal->id, 'account_id' => $l['account_id'], 'amount' => $l['amount'],
                    'currency' => $currency, 'created_at' => $postedAt,
                ], $lines));

                foreach ($delta as $id => $change) {
                    DB::table('ledger_accounts')->where('id', $id)->update([
                        'balance' => $accounts[$id]->balance + $change,
                        'version' => $accounts[$id]->version + 1,
                        'updated_at' => now(),
                    ]);
                }
                DB::table('ledger_chain')->where('id', 1)->update(['last_hash' => $hash, 'last_journal_id' => $journal->id, 'updated_at' => now()]);

                return $journal;
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            // the same key posted by a parallel worker after our first look: same lines → same journal
            return $this->existing($key, $payload) ?? throw $e;
        }
    }

    /** Undo a journal with its exact opposite. Calling it again returns the same reversal. */
    public function reverse(LedgerJournal $journal, string $reason, ?int $by = null): LedgerJournal
    {
        if ($journal->reverses_id) {
            throw LedgerException::make('already_reversed', 'A reversal cannot itself be reversed: post a new journal instead.');
        }
        $legs = $journal->postings()->get()->map(fn ($p) => ['account' => (int) $p->account_id, 'amount' => -$p->amount])->all();

        return $this->post("reversal:{$journal->uuid}", 'reversal', $legs, [
            'currency' => $journal->currency, 'memo' => $reason, 'reverses_id' => $journal->id,
            'source_type' => $journal->source_type, 'source_id' => $journal->source_id, 'created_by' => $by,
        ]);
    }

    // ------------------------------------------------------------------ holds

    /** Set money aside in an account. The same reference returns the same open hold. */
    public function hold(LedgerAccount $account, int $cents, string $reference, ?string $reason = null, ?\DateTimeInterface $expiresAt = null): LedgerHold
    {
        $cents = self::positive($cents);

        return DB::transaction(function () use ($account, $cents, $reference, $reason, $expiresAt) {
            $locked = LedgerAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            if ($hold = LedgerHold::where('reference', $reference)->first()) {
                if ($hold->account_id === $locked->id && $hold->amount === $cents) {
                    return $hold;
                }
                throw LedgerException::make('hold_exists', "Hold {$reference} already exists with different details.");
            }
            if ($locked->available() < $cents) {
                throw LedgerException::make('insufficient_funds', "Account {$locked->label()} has ".Money::format($locked->available()).' available, '.Money::format($cents).' asked.');
            }
            DB::table('ledger_accounts')->where('id', $locked->id)->update(['held' => $locked->held + $cents, 'version' => $locked->version + 1, 'updated_at' => now()]);

            return LedgerHold::create([
                'uuid' => (string) Uuid::uuid4(), 'account_id' => $locked->id, 'amount' => $cents, 'reference' => $reference,
                'reason' => $reason, 'status' => 'open', 'expires_at' => $expiresAt,
            ]);
        }, 3);
    }

    /** Give held money back. Releasing an already-released hold is a no-op. */
    public function release(LedgerHold|string $hold): LedgerHold
    {
        return DB::transaction(function () use ($hold) {
            $locked = $this->lockHold($hold);
            if ($locked->status === 'released') {
                return $locked;
            }
            if ($locked->status !== 'open') {
                throw LedgerException::make('hold_not_open', "Hold {$locked->reference} is {$locked->status}.");
            }
            $this->unhold($locked);
            $locked->update(['status' => 'released']);

            return $locked;
        }, 3);
    }

    /** Take held money: the hold is released and the journal posted in one transaction (both happen, or neither). */
    public function capture(LedgerHold|string $hold, string $key, string $type, array $legs, array $options = []): LedgerJournal
    {
        return DB::transaction(function () use ($hold, $key, $type, $legs, $options) {
            DB::table('ledger_chain')->where('id', 1)->lockForUpdate()->first();
            $locked = $this->lockHold($hold);
            if ($locked->status === 'captured' && $locked->journal_id) {
                return LedgerJournal::findOrFail($locked->journal_id);
            }
            if ($locked->status !== 'open') {
                throw LedgerException::make('hold_not_open', "Hold {$locked->reference} is {$locked->status}.");
            }
            $lines = $this->lines($legs);
            if (! in_array($locked->account_id, array_column($lines, 'account_id'), true)) {
                throw LedgerException::make('invalid_capture', 'A capture must post to the held account.');
            }
            $this->unhold($locked);
            $journal = $this->post($key, $type, $legs, $options);
            $locked->update(['status' => 'captured', 'journal_id' => $journal->id]);

            return $journal;
        }, 3);
    }

    // ------------------------------------------------------------------ helpers

    /** The canonical hash of one journal; ledger:check recomputes it. */
    public static function hash(?string $prev, string $uuid, string $key, string $type, string $currency, string $postedAt, array $lines): string
    {
        usort($lines, fn ($a, $b) => [$a['account_id'], $a['amount']] <=> [$b['account_id'], $b['amount']]);
        $body = implode(',', array_map(fn ($l) => $l['account_id'].':'.$l['amount'], $lines));

        return hash('sha256', implode('|', [$prev ?? '', $uuid, $key, $type, $currency, $postedAt, $body]));
    }

    private function lines(array $legs): array
    {
        if (count($legs) < 2) {
            throw LedgerException::make('too_few_legs', 'A journal needs at least two lines.');
        }
        $lines = [];
        $sum = 0;
        foreach ($legs as $leg) {
            $amount = $leg['amount'] ?? null;
            if (! is_int($amount) || $amount === 0) {
                throw LedgerException::make('invalid_amount', 'Every line needs a whole, non-zero number of cents.');
            }
            $account = $leg['account'] ?? null;
            $id = $account instanceof LedgerAccount ? $account->id : $account;
            if (! is_int($id)) {
                throw LedgerException::make('unknown_account', 'Every line needs an account.');
            }
            $lines[] = ['account_id' => $id, 'amount' => $amount];
            $sum += $amount;
        }
        if ($sum !== 0) {
            throw LedgerException::make('unbalanced', 'Debits and credits differ by '.Money::format($sum).'.');
        }

        return $lines;
    }

    private function payloadHash(string $type, string $currency, array $lines): string
    {
        usort($lines, fn ($a, $b) => [$a['account_id'], $a['amount']] <=> [$b['account_id'], $b['amount']]);

        return hash('sha256', json_encode([$type, $currency, $lines]));
    }

    private function existing(string $key, string $payload): ?LedgerJournal
    {
        $journal = LedgerJournal::where('idempotency_key', $key)->first();
        if ($journal && ! hash_equals($journal->payload_hash, $payload)) {
            throw LedgerException::make('idempotency_conflict', "Key {$key} was already used for a different journal.");
        }

        return $journal;
    }

    private function lockHold(LedgerHold|string $hold): LedgerHold
    {
        $query = LedgerHold::query()->lockForUpdate();

        return $hold instanceof LedgerHold ? $query->findOrFail($hold->id) : $query->where('reference', $hold)->firstOrFail();
    }

    private function unhold(LedgerHold $hold): void
    {
        $account = LedgerAccount::whereKey($hold->account_id)->lockForUpdate()->firstOrFail();
        DB::table('ledger_accounts')->where('id', $account->id)->update([
            'held' => $account->held - $hold->amount, 'version' => $account->version + 1, 'updated_at' => now(),
        ]);
    }

    private static function positive(int $cents): int
    {
        if ($cents <= 0) {
            throw LedgerException::make('invalid_amount', 'Use a positive number of cents with debit() and credit().');
        }

        return $cents;
    }
}
