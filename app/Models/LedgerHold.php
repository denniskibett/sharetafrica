<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Money set aside in an account (open), then taken (captured) or given back (released). Changed only by App\Ledger\Ledger. */
class LedgerHold extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['amount' => 'integer', 'expires_at' => 'datetime'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id');
    }
}
