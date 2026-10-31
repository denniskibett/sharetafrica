<?php

namespace App\Models;

use App\Ledger\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A personal payment: collect from the payer's M-PESA, pay the recipient. States and every transition live in
 * App\Ramp\RampOrders; this model only describes the order.
 *
 * quoted → collecting → paying → paid
 *                    ↘ failed            ↘ refunding → refunded  (or refund_failed: ops)
 * quoted → expired
 */
class RampOrder extends Model
{
    public const FINAL = ['paid', 'failed', 'refunded', 'expired', 'refund_failed'];

    protected $guarded = ['id'];

    protected $casts = [
        'destination' => 'array',
        'timeline' => 'array',
        'amount' => 'integer',
        'fee' => 'integer',
        'excise' => 'integer',
        'rounding' => 'integer',
        'total' => 'integer',
        'quote_expires_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function final(): bool
    {
        return in_array($this->status, self::FINAL, true);
    }

    public function recipient(): string
    {
        $d = $this->destination;

        return match ($this->destination_type) {
            'phone' => 'M-PESA '.($d['phone'] ?? ''),
            'paybill' => 'Paybill '.($d['paybill'] ?? '').' · account '.($d['account'] ?? ''),
            'till' => 'Till '.($d['till'] ?? ''),
            'bank' => (config('pricing.banks.'.($d['bank'] ?? '')) ?? 'Bank').' · '.($d['account'] ?? ''),
            default => '—',
        };
    }

    public function statusLabel(): array
    {
        return match ($this->status) {
            'quoted' => ['Quote', 'neutral'],
            'collecting' => ['Waiting for M-PESA PIN', 'warning'],
            'paying' => ['Sending', 'info'],
            'paid' => ['Paid', 'success'],
            'failed' => ['Not paid', 'error'],
            'refunding' => ['Refunding', 'warning'],
            'refunded' => ['Refunded', 'neutral'],
            'refund_failed' => ['Refund needs attention', 'error'],
            'expired' => ['Quote expired', 'neutral'],
            default => [ucfirst($this->status), 'neutral'],
        };
    }

    public function present(): array
    {
        [$label, $tone] = $this->statusLabel();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'recipient' => $this->recipient(),
            'note' => $this->note,
            'amount' => Money::format($this->amount),
            'fee' => Money::format($this->fee),
            'excise' => Money::format($this->excise),
            'rounding' => Money::format($this->rounding),
            'total' => Money::format($this->total),
            'status' => $this->status,
            'state' => ['label' => $label, 'tone' => $tone],
            'final' => $this->final(),
            'failure' => $this->failure,
            'payer' => $this->payer_phone,
            'quote_expires_at' => $this->quote_expires_at?->toIso8601String(),
            'timeline' => $this->timeline ?? [],
            'date' => $this->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i'),
            'url' => route('pay.show', $this->uuid),
            'sandbox' => $this->rail === 'simulator',
        ];
    }
}
