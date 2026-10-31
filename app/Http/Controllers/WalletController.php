<?php

namespace App\Http\Controllers;

/** The wallet has no page of its own: it is part of the dashboard (dashboard/partials/wallet). Old links land there. */
class WalletController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }
}
