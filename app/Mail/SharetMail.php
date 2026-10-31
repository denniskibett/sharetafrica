<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Every Sharet email, all on the emails.message template. Add new ones as named constructors
 * and list them in previews() so they show up on Administration › Email designs.
 */
class SharetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $subjectLine, public array $data, public ?string $replyToAddress = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            replyTo: $this->replyToAddress ? [new Address($this->replyToAddress)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.message', with: $this->data + ['subjectLine' => $this->subjectLine]);
    }

    /** To the person who used the contact form. */
    public static function contactThanks(ContactMessage $m): self
    {
        return new self('Thank you — we got your note', [
            'preheader' => 'A real person on the Sharet team will reply within 48 working hours.',
            'eyebrow' => 'Message received',
            'heading' => 'Thank you, '.str($m->name)->before(' ').'.',
            'lines' => [
                'Your note reached the Sharet team. A real person reads every message and will reply to this email address within 48 working hours.',
                'Here is a copy of what you sent, for your records:',
            ],
            'details' => array_filter([
                'You are' => $m->audience,
                'Business' => $m->business,
                'Country' => $m->country,
                'Message' => $m->brief,
            ]),
            'after' => ['If something is urgent, reply to this email and it goes straight to us.'],
        ]);
    }

    /** To the Sharet inbox, with reply-to set to the sender. */
    public static function contactReceived(ContactMessage $m): self
    {
        return new self("New contact: {$m->audience} — {$m->name}", [
            'preheader' => str($m->brief)->limit(90),
            'eyebrow' => 'Website contact form',
            'heading' => "{$m->name} wrote in",
            'lines' => ['Reply to this email to answer them directly.'],
            'details' => array_filter([
                'Audience' => $m->audience,
                'Name' => $m->name,
                'Email' => $m->email,
                'Phone' => $m->phone,
                'Business' => $m->business,
                'Country' => $m->country,
                'Message' => $m->brief,
                'Received' => $m->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i').' EAT',
            ]),
            'signoff' => 'Sharet website',
        ], $m->email);
    }

    /** After signing up: they are on the waiting list until the team assigns a lane. */
    public static function waitingList(User $user): self
    {
        return new self('Welcome to Sharet — you are on the list', [
            'preheader' => 'Thank you for signing up. Here is what happens next.',
            'eyebrow' => 'Welcome',
            'heading' => 'Thank you for joining, '.str($user->name)->before(' ').'.',
            'lines' => [
                'Your Sharet account is created. We are opening access in stages so every new wallet, merchant and business gets a proper start.',
                'Our team will review your sign-up and email you as soon as your account is ready. You can sign in at any time to check your status.',
            ],
            'button' => ['label' => 'Sign in to Sharet', 'url' => route('login')],
            'after' => ['The person you send money to never needs a Sharet account: that part works from day one.'],
        ]);
    }

    /** Admin moved them off the waiting list. */
    public static function accountReady(User $user, string $lane): self
    {
        return new self('Your Sharet account is ready', [
            'preheader' => "You now have a {$lane} account.",
            'eyebrow' => 'You are in',
            'heading' => 'Your account is ready, '.str($user->name)->before(' ').'.',
            'lines' => ["Good news: your Sharet {$lane} account is open. Sign in to see your dashboard and get started."],
            'button' => ['label' => 'Open my dashboard', 'url' => route('dashboard')],
            'after' => ['Questions? Reply to this email and a person on the team will help.'],
        ]);
    }

    /** Integrations › Email (SMTP) › Send test email. */
    public static function test(User $to, string $via): self
    {
        return new self('Sharet test email', [
            'preheader' => 'Your email settings work.',
            'eyebrow' => 'Email settings',
            'heading' => 'Your email settings work.',
            'lines' => ["This test was sent through {$via} to check that Sharet can deliver email for you.", 'Receipts, thank-you notes and account emails will now go out from the same address.'],
            'details' => ['Sent to' => $to->email, 'Sent' => now()->timezone('Africa/Nairobi')->format('j M Y, H:i').' EAT'],
        ]);
    }

    /** Password reset and email verification use the same design (AppServiceProvider). */
    public static function action(string $subject, string $eyebrow, string $heading, array $lines, string $buttonLabel, string $url, array $after = []): self
    {
        return new self($subject, compact('eyebrow', 'heading', 'lines', 'after') + [
            'preheader' => $lines[0] ?? '',
            'button' => ['label' => $buttonLabel, 'url' => $url],
        ]);
    }

    /** Sample of each email for the preview page. */
    public static function previews(): array
    {
        $user = new User(['name' => 'Amina Wanjiru', 'email' => 'amina@example.com']);
        $message = new ContactMessage([
            'audience' => 'Merchant — I want to accept payments from any rail', 'name' => 'Amina Wanjiru', 'email' => 'amina@example.com',
            'phone' => '+254 712 000 000', 'business' => 'Nairobi Events Co.', 'country' => 'Kenya',
            'brief' => "We sell event tickets and take M-PESA and cards.\nWe want one wallet and faster settlement.",
        ]);
        $message->created_at = now();

        return [
            'contact-thanks' => ['Contact form: thank you (to the sender)', self::contactThanks($message)],
            'contact-received' => ['Contact form: new message (to the team)', self::contactReceived($message)],
            'waiting-list' => ['Sign-up: welcome and waiting list', self::waitingList($user)],
            'account-ready' => ['Waiting list: account ready', self::accountReady($user, 'Merchant')],
            'password-reset' => ['Password reset', self::passwordReset('https://sharet.africa/reset-password/…')],
            'verify-email' => ['Verify email address', self::verifyEmail('https://sharet.africa/verify-email/…')],
            'test' => ['Test email (Integrations › Email)', self::test($user, 'smtp.example.com')],
        ];
    }

    public static function passwordReset(string $url): self
    {
        return self::action('Reset your Sharet password', 'Account security', 'Reset your password',
            ['We received a request to reset the password for your Sharet account.'], 'Choose a new password', $url,
            ['This link expires in '.config('auth.passwords.users.expire', 60).' minutes. If you did not ask for a reset, ignore this email: your password stays the same.']);
    }

    public static function verifyEmail(string $url): self
    {
        return self::action('Confirm your email address', 'One last step', 'Confirm your email address',
            ['Please confirm this is your email address so we can send you receipts and account alerts.'], 'Confirm email address', $url,
            ['If you did not create a Sharet account, ignore this email.']);
    }
}
