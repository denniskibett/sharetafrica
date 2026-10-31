<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends email after the response has gone to the browser, so pages never wait on the mail server
 * and no queue worker is needed. Failures are logged with a reference instead of breaking the page.
 */
class Outbox
{
    public static function later(string $to, Mailable $mail, ?callable $onSent = null): void
    {
        dispatch(function () use ($to, $mail, $onSent) {
            self::now($to, $mail) && $onSent && $onSent();
        })->afterResponse();
    }

    public static function now(string $to, Mailable $mail): bool
    {
        try {
            Mail::to($to)->send($mail);

            return true;
        } catch (Throwable $e) {
            Log::error('Email not sent', ['to' => $to, 'subject' => $mail->subjectLine ?? class_basename($mail), 'error' => $e->getMessage(), 'ref' => ErrorRef::get()]);

            return false;
        }
    }
}
