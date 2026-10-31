<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SharetMail;
use App\Models\Enquiry;
use App\Support\Table;
use Illuminate\Http\Request;

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

    public function messages(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('admin.contact.index');
        }

        $query = Enquiry::query()->with('user:id,name,email,phone,country')
            ->when($request->filled('audience'), fn ($q) => $q->where('audience', $request->query('audience')))
            ->when(($t = trim((string) $request->query('search'))) !== '', fn ($q) => $q->where(fn ($w) => $w->where('brief', 'like', "%$t%")->orWhere('business', 'like', "%$t%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$t%")->orWhere('email', 'like', "%$t%"))));
        $request->query->remove('search');

        return Table::respond($request, $query, fn (Enquiry $m) => [
            'id' => $m->id,
            'person' => Table::person($m->user ?? (object) ['name' => 'Unknown', 'email' => '']),
            'topic' => SharetMail::AUDIENCES[$m->audience] ?? ucfirst((string) $m->audience),
            'business' => $m->business,
            'brief' => $m->brief,
            'phone' => $m->user?->phone,
            'country' => $m->user?->country,
            'status' => Table::pill(ucfirst((string) ($m->status ?: 'new')), $m->responded_at ? 'success' : 'info'),
            'date' => $m->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i'),
        ], [], ['date' => 'created_at'], [
            'total' => Enquiry::count(), 'total_hint' => 'All time',
            'week' => Enquiry::where('created_at', '>=', now()->subDays(7))->count(), 'week_hint' => 'Last 7 days',
            'open' => Enquiry::whereNull('responded_at')->count(), 'open_hint' => 'Not answered yet',
            'answered' => Enquiry::whereNotNull('responded_at')->count(), 'answered_hint' => 'Replied to',
        ], extra: ['audiences' => SharetMail::AUDIENCES, 'inbox' => config('mail.contact_to')]);
    }
}
