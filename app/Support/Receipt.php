<?php

namespace App\Support;

use App\Mail\SharetMail;
use App\Models\RampOrder;
use App\Models\User;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Transaction receipts outside the app: a PDF to download, and an email (with the PDF) whenever a payment of at least
 * config('pricing.receipt_email_min') finishes. Lines come from RampOrder::receipt(), the same as the receipt panel.
 */
final class Receipt
{
    public static function pdf(RampOrder $order, ?User $viewer = null): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pay.receipt-pdf', [
            'order' => $order->present($viewer), 'lines' => $order->receipt($viewer),
            'qr' => 'data:image/png;base64,'.base64_encode((new Writer(new GDLibRenderer(220, 2)))->writeString(self::qrText($order, $viewer))),
        ])->setPaper('a4');
    }

    /** What the receipt's QR code holds: a plain-text summary any phone camera can read, even offline. */
    public static function qrText(RampOrder $order, ?User $viewer = null): string
    {
        $lines = $order->receipt($viewer);

        return implode("\n", array_filter([
            'Sharet receipt', $lines['Transaction ID'] ?? null, $lines['Total'] ?? $lines['Amount'] ?? null,
            ($lines['Recipient'] ?? '').(isset($lines['Account ID']) ? ' · '.$lines['Account ID'] : ''),
            $lines['Date'] ?? null, $lines['Status'] ?? null, 'sharet.africa',
        ]));
    }

    /** Email the payer (and, for an @name transfer, the person paid) once the payment has finished. Never throws. */
    public static function notify(RampOrder $order): void
    {
        if (! in_array($order->status, ['paid', 'refunded'], true) || $order->amount < config('pricing.receipt_email_min')) {
            return;
        }
        foreach (array_filter([$order->sender, $order->recipient_user_id ? User::find($order->recipient_user_id) : null]) as $person) {
            try {
                $p = $order->present($person);
                Mail::to($person->email)->send(SharetMail::transactionReceipt($order, $person)
                    ->attachData(self::pdf($order, $person)->output(), 'sharet-receipt-'.$order->sharetReference().'.pdf', ['mime' => 'application/pdf']));
            } catch (Throwable $e) {
                report($e); // a receipt that fails to send must never touch the payment
            }
        }
    }
}
