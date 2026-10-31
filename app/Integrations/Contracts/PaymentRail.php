<?php

namespace App\Integrations\Contracts;

use App\Integrations\RailResult;
use App\Models\RailRequest;

/**
 * One payment provider (Cashia, Gladys, Daraja, the simulator). Adapters read credentials from the integrations side
 * and never post to the ledger: they only report what the provider says. App\Ramp\RampOrders decides what it means.
 *
 * Every call carries $request->reference as the idempotency key, so a retry can never pay twice.
 */
interface PaymentRail
{
    public function name(): string;

    /** Ask the payer to pay (STK push). Usually answers pending; the result comes by callback or status(). */
    public function collect(RailRequest $request): RailResult;

    /** Pay a phone, paybill, till or bank account from Sharet's float. */
    public function payout(RailRequest $request): RailResult;

    /** What the provider says now about a request, by our reference. Used instead of ever resending. */
    public function status(RailRequest $request): RailResult;
}
