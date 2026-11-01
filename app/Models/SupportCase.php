<?php

namespace App\Models;

use App\Ledger\Money;
use App\Support\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A support desk case (design: DashSupport, JourneySupport). The system opens some (a late M-PESA confirmation);
 * staff take, work and close them. Every case has a reason code and an SLA.
 */
class SupportCase extends Model
{
    public const REASONS = [
        'LATE_CONFIRMATION' => 'M-PESA confirmed after the payment showed as failed',
        'PAYOUT_UNKNOWN' => 'Payout status unknown',
        'STK_NOT_RECEIVED' => 'M-PESA prompt not received',
        'NOT_RECEIVED' => 'Recipient says not received',
        'REVERSAL_REQUEST' => 'Reversal requested',
    ];

    public const STATUSES = ['new' => ['New', 'info'], 'open' => ['Open', 'warning'], 'with_ops' => ['With ops', 'info'], 'waiting' => ['Waiting on customer', 'neutral'], 'closed' => ['Closed', 'success']];

    public const OPEN = ['new', 'open', 'with_ops', 'waiting'];

    protected $guarded = ['id'];

    protected $casts = ['sla_due_at' => 'datetime', 'closed_at' => 'datetime'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /** Open a case for a payment, once per reason (the system calls this). */
    public static function raise(RampOrder $order, string $reason, string $summary, int $slaMinutes = 30): self
    {
        $case = self::firstOrCreate(['ramp_order_id' => $order->id, 'reason' => $reason],
            ['user_id' => $order->user_id, 'summary' => $summary, 'sla_due_at' => now()->addMinutes($slaMinutes)]);
        if (! $case->number) {
            $case->forceFill(['number' => 'CS-'.(10000 + $case->id)])->save();
        }

        return $case;
    }

    /** "00:22" left, or "+00:12 OVER", for open cases. */
    public function slaLeft(): ?string
    {
        if (! $this->sla_due_at || $this->status === 'closed') {
            return null;
        }
        $mins = (int) floor(now()->diffInMinutes($this->sla_due_at, false));

        return ($mins < 0 ? '+' : '').sprintf('%02d:%02d', intdiv(abs($mins), 60), abs($mins) % 60).($mins < 0 ? ' OVER' : '');
    }

    public function present(): array
    {
        $o = $this->order;
        [$label, $tone] = self::STATUSES[$this->status] ?? [ucfirst($this->status), 'neutral'];
        $over = $this->sla_due_at && $this->status !== 'closed' && $this->sla_due_at->isPast();

        return [
            'id' => $this->id,
            'number' => $this->number,
            'opened' => $this->created_at?->timezone('Africa/Nairobi')->format('j M, H:i'),
            'customer' => $this->customer ? Table::person($this->customer) : null,
            'phone' => $o?->payer_phone,
            'reason' => $this->reason,
            'reason_text' => self::REASONS[$this->reason] ?? $this->reason,
            'summary' => $this->summary,
            'channel' => ucfirst($this->channel),
            'sla' => $this->slaLeft() ?? '—',
            'sla_pill' => $this->status === 'closed' ? null : Table::pill($this->slaLeft() ?? '—', $over ? 'error' : 'neutral'),
            'owner' => $this->owner?->name ?? 'Unassigned',
            'pill' => Table::pill($label, $tone),
            'order' => $o ? ['ref' => $o->reference(), 'amount' => Money::format($o->total), 'state' => $o->statusLabel()[0], 'title' => $o->present()['title']] : null,
            'close_note' => $this->close_note,
            'closed' => $this->closed_at ? $this->closer?->name.' · '.$this->closed_at->timezone('Africa/Nairobi')->format('j M, H:i') : null,
            'is_open' => $this->status !== 'closed',
            'mine' => $this->owner_id === auth()->id(),
        ];
    }
}
