<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One sale of a shop: a payment request of kind "merchant", or money paid to the shop's account number. See App\Merchant\Sales. */
class MerchantSale extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['paid_at' => 'datetime'];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function till(): BelongsTo
    {
        return $this->belongsTo(MerchantTill::class, 'till_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApiPaymentRequest::class, 'payment_request_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(MerchantRefund::class, 'sale_id');
    }
}
