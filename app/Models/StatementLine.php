<?php

namespace App\Models;

use App\Ledger\Money;
use Illuminate\Database\Eloquent\Model;

/** One line of a provider's statement and how it matched the ledger. Changed only by App\Recon\Reconciler. */
class StatementLine extends Model
{
    public const OPEN = ['missed', 'amount', 'unknown'];

    protected $guarded = ['id'];

    protected $casts = ['raw' => 'array', 'amount' => 'integer', 'day' => 'date', 'occurred_at' => 'datetime', 'resolved_at' => 'datetime'];

    public function statusLabel(): array
    {
        return match ($this->status) {
            'matched' => ['Matched', 'success'],
            'fixed' => ['Fixed from statement', 'success'],
            'parked' => ['Parked in suspense', 'info'],
            'missed' => ['On statement, not booked', 'error'],
            'amount' => ['Amounts differ', 'error'],
            'unknown' => ['Not ours: unidentified', 'warning'],
            default => [ucfirst($this->status), 'neutral'],
        };
    }

    public function present(): array
    {
        [$label, $tone] = $this->statusLabel();

        return [
            'id' => $this->id,
            'time' => $this->occurred_at?->timezone('Africa/Nairobi')->format('H:i'),
            'ref' => $this->line_ref,
            'receipt' => $this->receipt,
            'direction' => $this->direction === 'in' ? 'In' : 'Out',
            'amount' => Money::format($this->amount),
            'party' => $this->party,
            'status' => $this->status,
            'pill' => ['label' => $label, 'tone' => $tone],
            'note' => $this->note,
            'can_fix' => $this->status === 'missed',
            'can_park' => $this->status === 'unknown',
        ];
    }
}
