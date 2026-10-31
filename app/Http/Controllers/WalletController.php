<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Wallet overview: balance and recent movements from the bavix wallet (User::getOrCreateWallet). */
class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();

        return view('wallet.index', [
            'balance' => $user->getWalletBalance(),
            'transactions' => $wallet->transactions()->latest()->limit(25)->get(),
        ]);
    }
}
