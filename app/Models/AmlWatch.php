<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The AML watch list: a phone, paybill, till, bank account or Sharet ID that must never pay or be paid. */
class AmlWatch extends Model
{
    protected $table = 'aml_watchlist';

    protected $guarded = ['id'];

    public const TYPES = ['phone' => 'M-PESA number', 'paybill' => 'Paybill', 'till' => 'Till', 'account' => 'Bank or paybill account', 'sharet_id' => 'Sharet ID'];
}
