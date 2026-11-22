<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A bulk payout from a company wallet: prepared by a maker, approved by a checker (App\Business\Batches). */
class BusinessBatch extends Model
{
    protected $fillable = ['public_id', 'company_id', 'wallet_user_id', 'name', 'status', 'item_count', 'amount', 'fees', 'total',
        'approvals_needed', 'approvals', 'created_by', 'rejected_by', 'reject_reason', 'started_at'];

    protected $casts = ['approvals' => 'array', 'started_at' => 'datetime'];

    public function lines(): HasMany
    {
        return $this->hasMany(BusinessBatchLine::class)->orderBy('line');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wallet_user_id');
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
