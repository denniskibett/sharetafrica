<?php

namespace App\Trade;

use App\Api\PaymentInput;
use App\Business\Team;
use App\Ledger\Money;
use App\Models\BusinessMember;
use App\Models\Company;
use App\Models\TradeOrder;
use App\Models\User;
use App\Ramp\RampOrders;
use App\Support\Handle;
use App\Support\SharetId;
use App\Support\TransactionPin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Trade in Kenya (canvas "Phase 2 · Mobile app · Trade"): one order from LPO to paid.
 *
 *   LPO      the importer orders from a supplier that is on Sharet and verified (by its Sharet ID or @name)
 *   Invoice  the supplier invoices against the LPO (its own invoice number, amount, due date)
 *   Pay      the importer's owner or a checker pays it from the company wallet with their PIN: wallet to wallet,
 *            free and instant, through the order engine (ledger, limits, AML). The LPO and invoice numbers are on
 *            the payment, so both sides' statements show what it paid.
 *   Finance  lenders financing an invoice is designed but off (config business.trade.finance) until lender agreements
 *            and a credit licence check exist. Repayments are therefore not built either.
 */
final class TradeOrders
{
    public function __construct(private RampOrders $orders) {}

    public function create(BusinessMember $m, string $supplier, string $items, string $amount, string $terms): TradeOrder
    {
        Team::authorize($m, 'trade');
        $importer = Company::findOrFail($m->company_id);
        if (! $importer->verified_at) {
            Team::deny('business_not_verified', 'Your business isn\'t verified yet. Orders open once Sharet has checked its documents.');
        }
        $seller = self::supplier($supplier);
        if ($seller->id === $importer->id) {
            throw ValidationException::withMessages(['supplier' => 'That is your own business.']);
        }
        if (! $seller->verified_at) {
            throw ValidationException::withMessages(['supplier' => $seller->name.' isn\'t verified on Sharet yet. Suppliers must be verified before they can take orders.']);
        }
        if (! array_key_exists($terms, config('business.trade.terms'))) {
            throw ValidationException::withMessages(['terms' => 'Choose pay on delivery, 30 days or 60 days.']);
        }
        $cents = self::amount($amount);

        return DB::transaction(function () use ($m, $importer, $seller, $items, $cents, $terms) {
            $n = (int) TradeOrder::where('importer_company_id', $importer->id)->lockForUpdate()->count() + 1;

            return TradeOrder::create(['number' => str_pad((string) $n, 4, '0', STR_PAD_LEFT), 'importer_company_id' => $importer->id, 'supplier_company_id' => $seller->id,
                'items' => Str::limit(strip_tags(trim($items)), 400, ''), 'amount' => $cents, 'currency' => 'KES', 'terms' => $terms, 'status' => 'sent', 'created_by' => $m->user_id]);
        });
    }

    /** The supplier invoices an LPO it received. */
    public function invoice(BusinessMember $m, TradeOrder $o, string $number, string $amount, string $due): TradeOrder
    {
        Team::authorize($m, 'trade');
        $this->side($m, $o, 'supplier');
        if ($o->status !== 'sent') {
            Team::deny('not_open', 'This order has been '.$o->status.' already.', 409);
        }
        $cents = self::amount($amount);
        if ($cents > $o->amount) {
            throw ValidationException::withMessages(['amount' => 'The invoice can\'t be more than the LPO (KES '.Money::format($o->amount).').']);
        }
        try {
            $dueOn = Carbon::parse($due, 'Africa/Nairobi')->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages(['due' => 'Give the due date as YYYY-MM-DD.']);
        }
        if ($dueOn->lt(now('Africa/Nairobi')->startOfDay())) {
            throw ValidationException::withMessages(['due' => 'The due date can\'t be in the past.']);
        }
        $o->update(['status' => 'invoiced', 'invoice_number' => Str::limit(strip_tags(trim($number)), 40, ''), 'invoice_amount' => $cents,
            'invoice_due' => $dueOn->toDateString(), 'invoiced_at' => now()]);

        return $o->fresh();
    }

    public function decline(BusinessMember $m, TradeOrder $o, string $reason): TradeOrder
    {
        Team::authorize($m, 'trade');
        $this->side($m, $o, 'supplier');
        if ($o->status !== 'sent') {
            Team::deny('not_open', 'This order has been '.$o->status.' already.', 409);
        }
        $o->update(['status' => 'declined', 'decline_reason' => Str::limit(strip_tags($reason), 200, '')]);

        return $o->fresh();
    }

    public function cancel(BusinessMember $m, TradeOrder $o): TradeOrder
    {
        Team::authorize($m, 'trade');
        $this->side($m, $o, 'importer');
        if (! in_array($o->status, ['sent', 'invoiced'], true)) {
            Team::deny('not_open', 'This order has been '.$o->status.' already.', 409);
        }
        $o->update(['status' => 'cancelled']);

        return $o->fresh();
    }

    /** The importer pays the invoice from the company wallet: the owner, or a checker who didn't raise the LPO. */
    public function pay(BusinessMember $m, TradeOrder $o, ?string $pin): TradeOrder
    {
        Team::authorize($m, 'approve');
        $this->side($m, $o, 'importer');
        if ($o->status !== 'invoiced') {
            Team::deny($o->status === 'paid' ? 'already_paid' : 'not_invoiced', $o->status === 'paid' ? 'This invoice is paid.' : 'The supplier hasn\'t invoiced this order yet.', 409);
        }
        if ($m->role !== 'owner' && $o->created_by === $m->user_id) {
            Team::deny('maker_cannot_approve', 'You raised this LPO: the owner or another checker pays its invoice.');
        }
        $limit = Team::approvalLimit($m);
        if ($limit !== null && $o->invoice_amount > $limit) {
            Team::deny('over_your_limit', 'This invoice is over your approval limit of KES '.Money::format($limit).'.');
        }
        TransactionPin::check($m->user, $pin);

        $from = Company::findOrFail($o->importer_company_id)->walletUser();
        $to = Company::findOrFail($o->supplier_company_id)->walletUser();
        $paid = DB::transaction(function () use ($o) {   // one payment per invoice, however often the phone retries
            $fresh = TradeOrder::whereKey($o->id)->lockForUpdate()->first();
            if ($fresh->status !== 'invoiced') {
                return false;
            }
            $fresh->update(['status' => 'paying']);

            return true;
        });
        if (! $paid) {
            Team::deny('already_paid', 'This invoice is being paid already.', 409);
        }
        try {
            $order = $this->orders->transfer($from, Handle::for($to), PaymentInput::amount($o->invoice_amount), self::memo($o));
        } catch (\Throwable $e) {
            $o->update(['status' => 'invoiced']);
            throw $e;
        }
        $o->update(['ramp_order_id' => $order->id, 'status' => $order->status === 'paid' ? 'paid' : 'held',
            'paid_at' => $order->status === 'paid' ? now() : null, 'paid_by' => $m->user_id]);

        return $o->fresh();
    }

    /** What needs a decision today, then the rest: orders this business placed and orders it received. */
    public function list(BusinessMember $m): array
    {
        $all = TradeOrder::where('importer_company_id', $m->company_id)->orWhere('supplier_company_id', $m->company_id)
            ->with(['importer', 'supplier', 'order'])->latest('id')->limit(100)->get();

        return [
            'to_pay' => $all->where('importer_company_id', $m->company_id)->where('status', 'invoiced')->map(fn ($o) => $this->present($o, $m))->values(),
            'to_invoice' => $all->where('supplier_company_id', $m->company_id)->where('status', 'sent')->map(fn ($o) => $this->present($o, $m))->values(),
            'orders' => $all->map(fn ($o) => $this->present($o, $m))->values(),
        ];
    }

    public function present(TradeOrder $o, BusinessMember $m): array
    {
        $side = $o->importer_company_id === $m->company_id ? 'importer' : 'supplier';

        return [
            'id' => $o->id, 'lpo' => 'LPO '.$o->number, 'side' => $side, 'status' => $o->status,
            'importer' => ['name' => $o->importer?->name, 'sharet_id' => $o->importer?->sharet_id],
            'supplier' => ['name' => $o->supplier?->name, 'sharet_id' => $o->supplier?->sharet_id],
            'items' => $o->items, 'amount' => Money::format($o->amount), 'currency' => $o->currency,
            'terms' => $o->terms, 'terms_label' => config('business.trade.terms')[$o->terms] ?? $o->terms,
            'invoice' => $o->invoice_number ? ['number' => $o->invoice_number, 'amount' => Money::format($o->invoice_amount), 'due' => $o->invoice_due?->toDateString()] : null,
            'paid' => $o->order ? ['at' => $o->paid_at?->toIso8601String(), 'reference' => $o->order->sharetReference(), 'order' => $o->order->uuid, 'note' => $o->order->note] : null,
            'decline_reason' => $o->decline_reason,
            'can_pay' => $side === 'importer' && $o->status === 'invoiced' && Team::can($m, 'approve') && ($m->role === 'owner' || $o->created_by !== $m->user_id),
            'can_invoice' => $side === 'supplier' && $o->status === 'sent' && Team::can($m, 'trade'),
            'finance' => self::finance(),
        ];
    }

    /** Trade finance: designed, not switched on. */
    public static function finance(): array
    {
        return config('business.trade.finance')
            ? ['available' => true]
            : ['available' => false, 'message' => 'Financing an invoice isn\'t available yet: it needs lender agreements and a credit licence check first.'];
    }

    /** "Invoice 2291 · LPO 0047": on the payment, so it shows on both statements and receipts. */
    public static function memo(TradeOrder $o): string
    {
        return 'Invoice '.$o->invoice_number.' · LPO '.$o->number;
    }

    private static function supplier(string $ref): Company
    {
        $ref = trim($ref);
        $wallet = preg_match('/^SH-/i', $ref)
            ? User::where('sharet_id', SharetId::normalise($ref))->first()
            : Handle::find($ref);
        $company = $wallet?->is_business_wallet && $wallet->company_id ? Company::find($wallet->company_id) : Company::where('sharet_id', SharetId::normalise($ref) ?? '-')->first();

        return $company ?? throw ValidationException::withMessages(['supplier' => 'No business on Sharet has that Sharet ID or @name.']);
    }

    private static function amount(string $amount): int
    {
        try {
            $cents = Money::cents($amount);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['amount' => 'Write the amount in shillings, e.g. 860000.']);
        }
        if ($cents < 100 || $cents > (int) config('business.trade.max_order')) {
            throw ValidationException::withMessages(['amount' => 'Between KES 1 and KES '.Money::format((int) config('business.trade.max_order')).'.']);
        }

        return $cents;
    }

    private function side(BusinessMember $m, TradeOrder $o, string $side): void
    {
        abort_unless(($side === 'importer' ? $o->importer_company_id : $o->supplier_company_id) === $m->company_id, 404);
    }
}
