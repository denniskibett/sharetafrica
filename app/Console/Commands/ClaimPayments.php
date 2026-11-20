<?php

namespace App\Console\Commands;

use App\Api\PaymentRequests;
use App\Models\ApiPaymentRequest;
use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Backfill for "claim on join": M-PESA payments made on pay pages before this existed join the history of the
 * verified account with that M-PESA number. Display only (destination.paid_by); the ledger is never touched.
 *   php artisan sharet:claim-payments          report only
 *   php artisan sharet:claim-payments --apply  link them
 */
class ClaimPayments extends Command
{
    protected $signature = 'sharet:claim-payments {--apply : Link them (otherwise only report)}';

    protected $description = 'Show past pay-page M-PESA payments in the history of the verified account with that number';

    public function handle(): int
    {
        $rows = RampOrder::whereIn('id', ApiPaymentRequest::whereNotNull('ramp_order_id')->select('ramp_order_id'))
            ->where('destination_type', 'wallet')->orderBy('id')->get()->map(function (RampOrder $o) {
                $m = $o->mpesaPayload();
                $claimer = $o->destination['paid_by'] ?? null;

                return [$o->id, $o->sharetReference(), $o->rail, $o->status, $o->payer_phone, $m['receipt'] ?? '-', $m['transaction_date'] ?? '-',
                    $m ? ($m['result_code'] ?? 'no callback') : 'none (simulator)', $claimer ? User::find($claimer)?->email.' (linked)' : '-'];
            });
        $this->table(['Order', 'Sharet ID', 'Rail', 'Status', 'Paid from', 'M-PESA receipt', 'M-PESA date', 'ResultCode', 'In history of'], $rows->all());

        $verified = User::where('is_business_wallet', false)->whereNotIn('kyc_level', ['none'])->whereNotNull('phone')->get();
        if (! $this->option('apply')) {
            $this->info('Report only. With --apply, these verified accounts claim matching payments: '.$verified->count().' checked.');

            return self::SUCCESS;
        }
        $n = $verified->sum(fn (User $u) => PaymentRequests::claimFor($u));
        $this->info("Linked {$n} payment(s) to their payers' history.");

        return self::SUCCESS;
    }
}
