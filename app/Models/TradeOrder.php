<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An LPO from an importer to a supplier on Sharet, the supplier's invoice on it, and its payment (App\Trade\TradeOrders). */
class TradeOrder extends Model
{
    protected $fillable = ['number', 'importer_company_id', 'supplier_company_id', 'items', 'amount', 'currency', 'terms', 'status',
        'invoice_number', 'invoice_amount', 'invoice_due', 'invoiced_at', 'ramp_order_id', 'paid_at', 'paid_by', 'created_by', 'decline_reason'];

    protected $casts = ['invoice_due' => 'date', 'invoiced_at' => 'datetime', 'paid_at' => 'datetime'];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'importer_company_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'supplier_company_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(RampOrder::class, 'ramp_order_id');
    }
}
