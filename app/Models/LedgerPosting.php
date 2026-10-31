<?php

namespace App\Models;

use App\Ledger\LedgerException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One line of a journal: amount in cents, debit +, credit -. Insert-only. */
class LedgerPosting extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['amount' => 'integer', 'created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw LedgerException::make('immutable', 'Postings cannot be changed.'));
        static::deleting(fn () => throw LedgerException::make('immutable', 'Postings cannot be deleted.'));
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(LedgerJournal::class, 'journal_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id');
    }
}
