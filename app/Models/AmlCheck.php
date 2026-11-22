<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One AML screening that hit a rule: held for review or blocked (App\Aml\Screening). */
class AmlCheck extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['hits' => 'array', 'reviewed_at' => 'datetime', 'amount' => 'integer'];

    public function order()
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
