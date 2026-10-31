<?php

namespace App\Models;

use App\Ledger\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One ledger account. `balance` is the raw sum of its postings (debits +, credits -), kept in step by App\Ledger\Ledger
 * only. natural() turns it into the everyday sign: an asset with money in it, or a wallet that owes a customer, is positive.
 */
class LedgerAccount extends Model
{
    protected $guarded = ['id', 'balance', 'held', 'version'];

    protected $casts = [
        'allow_negative' => 'boolean',
        'is_active' => 'boolean',
        'balance' => 'integer',
        'held' => 'integer',
        'version' => 'integer',
    ];

    public const DEBIT_NORMAL = ['asset', 'expense'];

    public function postings(): HasMany
    {
        return $this->hasMany(LedgerPosting::class, 'account_id');
    }

    public function holds(): HasMany
    {
        return $this->hasMany(LedgerHold::class, 'account_id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function debitNormal(): bool
    {
        return in_array($this->type, self::DEBIT_NORMAL, true);
    }

    /** The balance in the account's own direction (cents). */
    public function natural(?int $raw = null): int
    {
        $raw ??= $this->balance;

        return $this->debitNormal() ? $raw : -$raw;
    }

    /** What can still be spent: natural balance less open holds (cents). */
    public function available(): int
    {
        return $this->natural() - $this->held;
    }

    public function label(): string
    {
        return $this->owner_key === 'system' ? "{$this->code} {$this->name}" : "{$this->code} {$this->name} · {$this->owner_key}";
    }

    public function present(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'owner' => $this->owner_key === 'system' ? null : $this->owner_key,
            'type' => $this->type,
            'book' => $this->book,
            'currency' => $this->currency,
            'balance' => Money::format($this->natural()),
            'held' => Money::format($this->held),
            'available' => Money::format($this->available()),
            'active' => $this->is_active,
            'url' => route('ledger.show', $this->id),
        ];
    }
}
