<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A payment made through the public API (docs/v2/INTEGRATION.md §4). Live ones point at the ramp order that moves the money. */
class ApiPayment extends Model
{
    protected $fillable = ['public_id', 'api_key_id', 'user_id', 'environment', 'ramp_order_id', 'api_settlement_id', 'status', 'amount', 'fee', 'total', 'to', 'reference', 'failure'];

    protected $casts = ['to' => 'array'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(ApiSettlement::class, 'api_settlement_id');
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class, 'api_key_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
