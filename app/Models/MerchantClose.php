<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** End of day for one till: the sales it closed and their totals. See App\Merchant\Sales::close. */
class MerchantClose extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['day' => 'date'];

    public function till(): BelongsTo
    {
        return $this->belongsTo(MerchantTill::class, 'till_id');
    }
}
