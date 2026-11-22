<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One person paid by a batch: its own order once approved; a failed line comes back to the wallet in full. */
class BusinessBatchLine extends Model
{
    protected $fillable = ['business_batch_id', 'line', 'name', 'type', 'to', 'payee_key', 'amount', 'fee', 'flags', 'status', 'ramp_order_id', 'failure'];

    protected $casts = ['to' => 'array', 'flags' => 'array'];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BusinessBatch::class, 'business_batch_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }
}
