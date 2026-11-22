<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A refund of a sale: asked by a cashier (or the owner), approved by the owner with the PIN. See App\Merchant\Refunds. */
class MerchantRefund extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['decided_at' => 'datetime'];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(MerchantSale::class, 'sale_id');
    }

    public function till(): BelongsTo
    {
        return $this->belongsTo(MerchantTill::class, 'till_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }
}
