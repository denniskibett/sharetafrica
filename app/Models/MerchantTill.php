<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One till of a shop and its cashier (cashier PIN, shop phones linked to it). See App\Merchant\Tills. */
class MerchantTill extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['pin_hash', 'invite_hash'];

    protected $casts = ['pin_locked_until' => 'datetime', 'invite_expires_at' => 'datetime', 'last_seen_at' => 'datetime'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function label(): string
    {
        return 'Till '.$this->number;
    }

    public function firstName(): string
    {
        return \Illuminate\Support\Str::before(trim($this->cashier_name), ' ');
    }
}
