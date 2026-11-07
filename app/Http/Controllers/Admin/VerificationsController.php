<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/** Administration › Verifications: people whose automatic checks did not pass wait here for staff (approve or reject on their page). */
class VerificationsController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pending', 'rejected', 'approved'], true) ? $request->query('tab') : 'pending';
        $people = User::where('kyc_review', $tab)->orderBy('updated_at', $tab === 'pending' ? 'asc' : 'desc')
            ->paginate(25, ['id', 'name', 'email', 'phone', 'kyc_level', 'updated_at'])->withQueryString();
        $counts = User::whereNotNull('kyc_review')->selectRaw('kyc_review, count(*) n')->groupBy('kyc_review')->pluck('n', 'kyc_review');

        return view('admin.verifications.index', compact('people', 'tab', 'counts'));
    }
}
