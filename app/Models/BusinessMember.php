<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A person who can act for a company's wallet in the app: owner, maker, checker or viewer (App\Business\Team). */
class BusinessMember extends Model
{
    protected $fillable = ['company_id', 'user_id', 'role', 'approval_limit', 'added_by'];

    protected $casts = ['approval_limit' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
