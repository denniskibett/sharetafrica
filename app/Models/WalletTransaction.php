<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_id', 'type', 'amount', 'description', 'invoice_id'];

    protected $casts = ['amount' => 'decimal:2'];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }
}