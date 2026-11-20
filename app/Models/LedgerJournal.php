<?php

namespace App\Models;

use App\Ledger\LedgerException;
use App\Ledger\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One balanced entry. Insert-only: refused here and by database triggers. Fix a mistake with a reversal. */
class LedgerJournal extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    // microseconds are part of the hash: keep them when saving
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $casts = ['meta' => 'array', 'posted_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw LedgerException::make('immutable', 'Journals cannot be changed. Post a reversal.'));
        static::deleting(fn () => throw LedgerException::make('immutable', 'Journals cannot be deleted. Post a reversal.'));
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function postings(): HasMany
    {
        return $this->hasMany(LedgerPosting::class, 'journal_id');
    }

    public function reverses(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_id');
    }

    public function reversal()
    {
        return $this->hasOne(self::class, 'reverses_id');
    }

    public function present(): array
    {
        $this->loadMissing('postings.account');

        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'memo' => $this->memo,
            'key' => $this->idempotency_key,
            'posted_at' => $this->posted_at?->format('d M Y, H:i:s'),
            'source' => $this->source_type ? "{$this->source_type} #{$this->source_id}" : null,
            'reverses' => $this->reverses_id ? self::whereKey($this->reverses_id)->value('uuid') : null,
            'reversed_by' => self::where('reverses_id', $this->id)->value('uuid'),
            'hash' => $this->hash,
            'lines' => $this->postings->map(fn (LedgerPosting $p) => [
                'account' => $p->account->label(),
                'account_url' => route('ledger.show', $p->account_id),
                'debit' => $p->amount > 0 ? Money::format($p->amount) : '',
                'credit' => $p->amount < 0 ? Money::format(-$p->amount) : '',
            ])->values(),
            'total' => Money::format($this->postings->where('amount', '>', 0)->sum('amount')),
        ];
    }
}
