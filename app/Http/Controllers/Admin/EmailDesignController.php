<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SharetMail;
use App\Models\Enquiry;

/** Administration › Email designs (preview every email) and Enquiries (website contact form, live `enquiries` table). */
class EmailDesignController extends Controller
{
    public function index()
    {
        return view('admin.emails.index', ['emails' => collect(SharetMail::previews())->map(fn ($e, $key) => ['key' => $key, 'name' => $e[0], 'subject' => $e[1]->subjectLine])->values()]);
    }

    public function show(string $key)
    {
        $email = SharetMail::previews()[$key] ?? abort(404);
        $html = $email[1]->render();

        // ?theme=dark: show the dark-mode version (frames don't inherit the device's dark setting)
        if (request('theme') === 'dark') {
            $html = str_replace('@media (prefers-color-scheme: dark)', '@media all', $html);
        }

        return response($html);
    }

    public function messages()
    {
        return view('admin.contact.index', ['messages' => Enquiry::with('user:id,name,email,phone,country')->latest()->paginate(25)]);
    }
}
