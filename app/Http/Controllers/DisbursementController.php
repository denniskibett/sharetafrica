<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;

class DisbursementController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            // Load all disbursements with their associated loans and users
            $disbursements = Disbursement::with(['loan.user'])->get();
            return view('disbursements.index', [
                'disbursements' => $disbursements,
            ]);
        } else {
            // Load loans for the authenticated user with their disbursements
            $loans = auth()->user()->loans()->with('disbursements')->get();
            return view('disbursements.index', [
                'loans' => $loans,
            ]);
        }
    }

    public function show($id)
    {
        $disbursement = Disbursement::findOrFail($id);
        return view('disbursements.show', compact('disbursement'));
    }

    public function create()
    {
        $loans = Loan::all();
        return view('disbursements.create', compact('loans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric',
            'disburse_date' => 'required|date',
            'payment_date' => 'required|date',
            'mode' => 'required|string',
        ]);

        Disbursement::create($request->all());
        return redirect()->route('disbursements.index');
    }

    public function destroy($id)
    {
        $disbursement = Disbursement::findOrFail($id);
        $disbursement->delete();

        return redirect()->route('disbursements.index');
    }
}
