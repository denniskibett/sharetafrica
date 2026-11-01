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
 * Wallet-funded orders skip collecting (quoted → paying); top-ups (destination_type "wallet") end at paid once collected.
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

    /**
     * When a payment changes state, tell the customer after the transaction commits: an SMS for the prompt and the
     * result (CustomerMessages), and the emailed receipt when it finishes big enough (Receipt).
     */
    protected static function booted(): void
    {
        static::saved(function (RampOrder $o) {
            if (! $o->wasRecentlyCreated && ! $o->wasChanged('status')) {
                return;
            }
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($o) {
                $fresh = $o->fresh();
                \App\Support\CustomerMessages::order($fresh);
                if (in_array($fresh->status, ['paid', 'refunded'], true)) {
                    \App\Support\Receipt::notify($fresh);
                }
            });
        });
    }

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
            'wallet' => 'Your Sharet wallet',
            'sharet' => '@'.($d['handle'] ?? '').' · '.($d['name'] ?? ''),
            default => '—',
        };
    }

    public function statusLabel(): array
    {
        return match ($this->status) {
            'quoted' => ['Quote', 'neutral'],
            'collecting' => ['Waiting for M-PESA PIN', 'warning'],
            'paying' => ['Sending', 'info'],
            'paid' => [$this->destination_type === 'wallet' ? 'Added to wallet' : 'Paid', 'success'],
            'failed' => ['Not paid', 'error'],
            'refunding' => ['Refunding', 'warning'],
            'refunded' => ['Refunded', 'neutral'],
            'refund_failed' => ['Refund needs attention', 'error'],
            'expired' => ['Quote expired', 'neutral'],
            default => [ucfirst($this->status), 'neutral'],
        };
    }

    /** A standard icon for a progress step, from what it says (works for steps saved before icons existed). */
    public static function stepIcon(string $text): string
    {
        return match (true) {
            (bool) preg_match('/^(Could not|Not paid|Refund could not)/', $text) => 'x-circle',
            (bool) preg_match('/expired/i', $text) => 'clock',
            (bool) preg_match('/(Refund|back in your wallet)/', $text) => 'arrow-uturn-left',
            (bool) preg_match('/^(Received by|Added to your wallet|Paid by)/', $text) => 'check-circle',
            (bool) preg_match('/prompt sent/', $text) => 'device-phone-mobile',
            (bool) preg_match('/^(Taken from your wallet|Sent from your wallet|Sending)/', $text) => 'paper-airplane',
            default => 'document-text', // quote ready, adding money
        };
    }

    /** The code to quote: the M-PESA receipt of the money that arrived, else Sharet's own reference. */
    public function reference(): string
    {
        $id = $this->destination_type === 'wallet' ? $this->collect_request_id : $this->payout_request_id;

        return ($id ? RailRequest::find($id)?->receipt : null) ?: $this->sharetReference();
    }

    public function sharetReference(): string
    {
        return 'SH'.strtoupper(substr(str_replace('-', '', $this->uuid), 0, 8));
    }

    /**
     * The receipt, one definition for the panel, the PDF and the email: recipient, account, bank or network,
     * transaction ID, date and the money. As $viewer sees it (a recipient never sees the sender's number).
     *
     * @return array<string, string>
     */
    public function receipt(?User $viewer = null): array
    {
        $d = $this->destination;
        $p = $this->present($viewer);
        $bank = config('pricing.banks.'.($d['bank'] ?? ''));

        return array_filter([
            'Recipient' => match ($this->destination_type) {
                'sharet' => ($d['name'] ?? '').' (@'.($d['handle'] ?? '').')',
                'wallet' => 'Your Sharet wallet',
                'phone' => 'M-PESA '.($d['phone'] ?? ''),
                'paybill' => 'Paybill '.($d['paybill'] ?? ''),
                'till' => 'Till '.($d['till'] ?? ''),
                'bank' => $bank ?? 'Bank',
                default => $this->recipient(),
            },
            'Account ID' => match ($this->destination_type) {
                'sharet' => '@'.($d['handle'] ?? ''),
                'wallet' => '@'.($this->sender?->handle ?? ''),
                'phone' => $d['phone'] ?? null,
                'paybill' => $d['account'] ?? null,
                'till' => $d['till'] ?? null,
                'bank' => $d['account'] ?? null,
                default => null,
            },
            'Bank' => match ($this->destination_type) {
                'bank' => $bank ?? 'Bank',
                'sharet', 'wallet' => 'Sharet wallet',
                default => 'M-PESA (Safaricom)',
            },
            'From' => $p['from'],
            'Transaction ID' => $this->reference(),
            'Sharet reference' => $this->sharetReference(),
            'Date' => $this->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i'),
            'Amount' => 'KES '.Money::format($this->amount),
            'Fee and duty' => $p['incoming'] || $this->destination_type === 'wallet' ? null : 'KES '.Money::format($this->fee + $this->excise + $this->rounding),
            'Total' => $p['incoming'] ? null : 'KES '.Money::format($this->total),
            'Status' => $p['state']['label'],
            'Note' => $this->note,
        ], fn ($v) => filled($v));
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** As $viewer sees it: the recipient of a transfer sees where it came from, not the sender's number. */
    public function present(?User $viewer = null): array
    {
        [$label, $tone] = $this->statusLabel();
        $incoming = $viewer && $this->recipient_user_id === $viewer->id;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'recipient' => $incoming ? 'From @'.($this->sender?->handle ?? 'sharet').' · '.$this->sender?->name : $this->recipient(),
            // where the money came from and went, for one-line lists and receipts
            'from' => match (true) {
                $incoming => '@'.($this->sender?->handle ?? 'sharet'),
                $this->funding === 'wallet' => 'Your wallet',
                default => 'M-PESA '.$this->payer_phone,
            },
            'to' => $incoming || $this->destination_type === 'wallet' ? 'Your wallet' : $this->recipient(),
            'incoming' => $incoming,
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
            'payer' => $incoming ? null : $this->payer_phone,
            'funding' => $this->funding,
            'top_up' => $this->destination_type === 'wallet',
            'transfer' => $this->destination_type === 'sharet',
            'withdrawal' => $this->funding === 'wallet' && $this->destination_type === 'phone' && $this->destination['phone'] === $this->payer_phone,
            'in' => $in = $incoming || $this->destination_type === 'wallet',
            'signed' => ($in ? '+' : '−').Money::format($in ? $this->amount : $this->total),
            // colour of the amount: in (green), out (red), pending (amber, still moving), none (failed, refunded, expired)
            'flow' => match (true) {
                in_array($this->status, ['failed', 'refunded', 'expired', 'quoted'], true) => 'none',
                ! $this->final() => 'pending',
                default => $in ? 'in' : 'out',
            },
            'title' => match (true) {
                $incoming => 'From @'.($this->sender?->handle ?? 'sharet'),
                $this->destination_type === 'wallet' => 'Added from M-PESA',
                $this->funding === 'wallet' && $this->destination_type === 'phone' && $this->destination['phone'] === $this->payer_phone => 'Withdrawn to M-PESA',
                default => $this->recipient(),
            },
            // heroicon (outline) for the list and receipt, one per kind of payment
            'icon' => $incoming ? 'arrow-down-left' : match ($this->destination_type) {
                'wallet' => 'arrow-down-tray', 'sharet' => 'at-symbol', 'paybill' => 'building-office', 'till' => 'shopping-bag',
                'bank' => 'building-library', default => $this->funding === 'wallet' && ($this->destination['phone'] ?? null) === $this->payer_phone ? 'banknotes' : 'device-phone-mobile',
            },
            'quote_expires_at' => $this->quote_expires_at?->toIso8601String(),
            'timeline' => array_map(fn ($e) => $e + ['icon' => self::stepIcon($e['text'])], $this->timeline ?? []),
            'date' => $this->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i'),
            'url' => route('pay.show', $this->uuid),
            'sandbox' => $this->rail === 'simulator',
        ];
    }
}
