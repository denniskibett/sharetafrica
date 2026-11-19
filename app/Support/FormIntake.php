<?php

namespace App\Support;

use App\Mail\SharetMail;
use App\Models\Enquiry;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Every website form ends here once its own row is saved (the scharge demo-form pattern): a reference number for the
 * thank-you, a row in Administration › Enquiries (the inbox for everything sent from sharet.africa), a PDF of what was
 * sent on the private disk (downloaded from the inbox), and one email to the team inbox with the PDF attached.
 * The answers live in enquiries.meta: ref, form, rows, pdf and the id of the row the form wrote.
 */
class FormIntake
{
    /** Form key => [reference prefix, label]. */
    public const FORMS = [
        'personal' => ['SP', 'Personal waiting list'],
        'merchant' => ['SM', 'Business waiting list'],
        'trade' => ['ST', 'Trade application'],
        'developer' => ['SD', 'Developer request'],
        'contact' => ['SC', 'Contact form'],
    ];

    /**
     * @param  array<string, mixed>  $rows  label => value shown in the PDF and the email (blanks dropped)
     * @param  array<string, int>  $links  e.g. ['trade_application_id' => 12]
     */
    public static function record(User $user, string $form, string $audience, ?string $business, string $brief, array $rows, array $links = []): Enquiry
    {
        [$prefix, $label] = self::FORMS[$form];
        $ref = $prefix.'-'.strtoupper(Str::random(6));
        $rows = array_filter($rows, fn ($v) => ! blank($v));

        $enquiry = $user->enquiries()->create([
            'audience' => $audience,
            'business' => $business,
            'brief' => $brief,
            'status' => 'new',
            'meta' => ['ref' => $ref, 'form' => $form, 'form_name' => $label, 'rows' => $rows] + $links,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);

        try {
            $path = 'submissions/'.$form.'/'.$ref.'.pdf';
            Storage::disk('local')->put($path, Pdf::loadView('pdf.form-submission', ['e' => $enquiry->load('user')])->setPaper('a4')->output());
            $enquiry->update(['meta' => $enquiry->meta + ['pdf' => $path]]);
        } catch (\Throwable $e) {
            Log::warning('Form PDF not made', ['ref' => $ref, 'error' => $e->getMessage()]);
        }

        $mail = SharetMail::formReceived($enquiry);
        if ($pdf = $enquiry->meta['pdf'] ?? null) {
            $mail->attachFromStorageDisk('local', $pdf, $ref.'.pdf', ['mime' => 'application/pdf']);
        }
        Outbox::later(config('mail.contact_to'), $mail);

        return $enquiry;
    }

    /** Hidden "website" field: people never see it, bots fill it in. */
    public static function isBot(): bool
    {
        return filled(request()->input('website'));
    }
}
