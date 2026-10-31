<?php

namespace App\Http\Controllers;

use App\Mail\SharetMail;
use App\Models\User;
use App\Support\Outbox;
use Illuminate\Http\Request;

/**
 * New sign-ups get the waiting_list role. They see a status page; admins assign each person a lane
 * (individual, merchant, business, techie), which emails them that their account is ready.
 */
class WaitingListController extends Controller
{
    public const LANES = [
        'individual' => 'Personal wallet',
        'merchant' => 'Merchant',
        'business' => 'Business / trade',
        'techie' => 'Developer / platform',
    ];

    public function show(Request $request)
    {
        // Someone already given a lane goes straight to their dashboard
        if (! $request->user()->hasRole('waiting_list') && $request->user()->roles()->exists()) {
            return redirect()->route('dashboard');
        }

        return view('waiting-list.show', ['user' => $request->user()]);
    }

    public function index()
    {
        return view('waiting-list.index', [
            'people' => User::role('waiting_list')->latest()->get(['id', 'name', 'email', 'phone', 'created_at']),
            'lanes' => self::LANES,
        ]);
    }

    public function assign(Request $request, User $user)
    {
        $data = $request->validate(['lane' => 'required|in:'.implode(',', array_keys(self::LANES))]);

        $user->syncRoles([$data['lane']]);
        Outbox::later($user->email, SharetMail::accountReady($user, self::LANES[$data['lane']]));

        return response()->json(['message' => "{$user->name} moved to ".self::LANES[$data['lane']].'. We emailed them.']);
    }
}
