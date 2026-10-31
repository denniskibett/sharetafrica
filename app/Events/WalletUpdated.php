<?php
// app/Events/WalletUpdated.php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletUpdated
{
    use Dispatchable, SerializesModels;

    public $walletOwner;
    public $newBalance;
    public $transaction;

    public function __construct($walletOwner, $newBalance, $transaction)
    {
        $this->walletOwner = $walletOwner;
        $this->newBalance = $newBalance;
        $this->transaction = $transaction;
    }
}