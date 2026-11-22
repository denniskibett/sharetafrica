<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A locked FX quote for paying a supplier abroad (App\Trade\CrossBorder). It never moves money by itself. */
class TradeFxQuote extends Model
{
    protected $fillable = ['public_id', 'company_id', 'created_by', 'currency', 'amount', 'kes_amount', 'kes_per_unit', 'reference_rate', 'rate_source',
        'supplier_name', 'supplier_country', 'invoice_reference', 'trade_order_id', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];
}
