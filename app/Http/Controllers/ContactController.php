<?php

namespace App\Http\Controllers;

use App\Mail\SharetMail;
use App\Models\ContactMessage;
use App\Support\Outbox;
use Illuminate\Http\Request;

/** Website contact form: store, thank the sender, copy the team. Answers JSON for the in-page form. */
class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Bots fill the hidden "website" field; pretend success and drop it
        if (filled($request->input('website'))) {
            return $this->done($request);
        }

        $data = $request->validate([
            'audience' => 'required|string|max:120',
            'name' => 'required|string|max:120',
            'email' => 'required|email:rfc|max:190',
            'phone' => 'nullable|string|max:40',
            'business' => 'nullable|string|max:160',
            'country' => 'nullable|string|max:60',
            'brief' => 'required|string|min:10|max:5000',
        ], [
            'brief.required' => 'Tell us a little about what you need.',
            'brief.min' => 'Please add a sentence or two so we can help.',
        ]);

        $message = ContactMessage::create($data);

        Outbox::later($message->email, SharetMail::contactThanks($message), fn () => $message->forceFill(['thanked_at' => now()])->save());
        Outbox::later(config('mail.contact_to'), SharetMail::contactReceived($message), fn () => $message->forceFill(['notified_at' => now()])->save());

        return $this->done($request);
    }

    protected function done(Request $request)
    {
        $text = 'Thank you — your note is with the team. We have emailed you a copy and will reply within 48 working hours.';

        return $request->expectsJson()
            ? response()->json(['message' => $text])
            : back()->with('contact_status', $text);
    }
}
