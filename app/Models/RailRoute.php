<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Which partner moves one kind of payment (System › Payment rails). Changed only through an approved RailRouteChange. */
class RailRoute extends Model
{
    /** movement => [label, what it covers] */
    public const MOVEMENTS = [
        'collect' => ['Add money · M-PESA prompt', 'Wallet top-ups and payments collected by STK push'],
        'payout_phone' => ['Send to an M-PESA number', 'Sends, withdrawals and refunds to a phone (B2C)'],
        'payout_b2b' => ['Pay a paybill or till', 'Bills and merchants (B2B)'],
        'payout_bank' => ['Pay a bank account', 'Through the bank’s M-PESA paybill'],
        'settlement' => ['Business settlements', 'Payouts from business and API wallets'],
    ];

    /** Rails staff can choose. The simulator never moves real money. */
    public const RAILS = ['daraja' => 'M-PESA paybill', 'cloud9' => 'Cloud9', 'simulator' => 'Simulator (test only)'];

    public const AUDIENCES = ['named' => 'Test list only', 'everyone' => 'Everyone'];

    protected $guarded = ['id'];

    protected $casts = ['approved_at' => 'datetime'];

    /** The route for one kind of payment, or null (then config/rails.php decides). */
    public static function for(string $movement): ?self
    {
        try {
            return self::where('movement', $movement)->first();
        } catch (\Illuminate\Database\QueryException) {
            return null; // before migrations run
        }
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
