<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradeApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'trade_type', 'corridor', 'monthly_volume',
        'expected_order_size', 'need', 'brief',
        'status', 'reviewed_by', 'reviewed_at',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}