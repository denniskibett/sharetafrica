<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SharetMail;
use App\Models\User;
use App\Support\Identity;
use App\Support\Outbox;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        Identity::clean($request);
        $request->validate(Identity::rules(phoneRequired: true) + [
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], Identity::MESSAGES);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'onboarding_status' => 'waiting_list',
            'status' => true,
        ]);

        // Everyone starts on the waiting list (same as the website forms); an admin invites them,
        // then they choose their lane in onboarding. The role is never taken from the form.
        $user->assignRole(\Spatie\Permission\Models\Role::findOrCreate('waiting_list', 'web'));
        \App\Models\WaitingListEntry::firstOrCreate(['user_id' => $user->id], [
            'intent' => 'undecided', 'source' => 'register', 'status' => 'pending',
            'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        \App\Support\Footprint::record($user, $request, 'register');

        $codeFirst = \App\Support\EmailCode::enabled();
        $codeFirst ? \App\Support\EmailCode::require($user) : event(new Registered($user));
        Outbox::later($user->email, SharetMail::waitingList($user));
    
        Auth::login($user);
        session()->put('sa_conversion', 'signup');   // counted once on the next page (partials.sharet-analytics)
    
        return redirect($codeFirst ? route('verification.code') : route('dashboard', absolute: false));
    }
}
