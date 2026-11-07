<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A business asking someone to pay (docs/v2/INTEGRATION.md §3). The hosted page is /r/{token}. */
class ApiPaymentRequest extends Model
{
    protected $fillable = ['public_id', 'token', 'api_key_id', 'user_id', 'environment', 'amount', 'reference', 'payer_user_id', 'payer_phone',
        'channels', 'metadata', 'splits', 'split_status', 'split_failure', 'status', 'paid_via', 'ramp_order_id', 'paid_at', 'expires_at'];

    protected $casts = ['channels' => 'array', 'metadata' => 'array', 'splits' => 'array', 'paid_at' => 'datetime', 'expires_at' => 'datetime'];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class, 'api_key_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && $this->expires_at->isFuture();
    }
}
