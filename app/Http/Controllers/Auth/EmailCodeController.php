<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\EmailCode;
use Illuminate\Http\Request;

/** The sign-up email code page (App\Support\EmailCode). */
class EmailCodeController extends Controller
{
    public function show(Request $request)
    {
        if (! EmailCode::pending($request->user())) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-code', ['email' => $request->user()->email]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|max:12'], ['code.required' => 'Enter the 6-digit code from the email.']);
        $result = EmailCode::check($request->user(), $request->input('code'));

        return $result === true
            ? redirect()->route('dashboard')->with('status', 'email-confirmed')
            : back()->withErrors(['code' => $result]);
    }

    public function resend(Request $request)
    {
        EmailCode::pending($request->user()) && EmailCode::send($request->user());

        return back()->with('status', 'code-sent');
    }
}
