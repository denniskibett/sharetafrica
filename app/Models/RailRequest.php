<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One request sent to a payment rail (collect or payout), with what the rail last said about it. */
class RailRequest extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'integer',
        'checks' => 'integer',
        'last_checked_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function final(): bool
    {
        return in_array($this->status, ['succeeded', 'failed'], true);
    }
}
