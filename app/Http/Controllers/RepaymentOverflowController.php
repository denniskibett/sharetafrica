<?php

namespace App\Http\Controllers;

use App\Models\RepaymentOverflow;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;

class RepaymentOverflowController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            // Load users with their loans and repayment overflows
            $users = User::with(['loans.repaymentOverflows', 'loans.repaymentOverflowsTo'])->get();
            return view('repayment_overflows.index', [
                'users' => $users,
                'repayment_overflows' => [],
            ]);
        } else {
            // Load loans for the authenticated user with repayment overflows
            $loans = auth()->user()->loans()->with(['repaymentOverflows', 'repaymentOverflowsTo'])->get();
            return view('repayment_overflows.index', [
                'loans' => $loans,
            ]);
        }
    }
    
    public function create()
    {
        $loans = Loan::all();
        return view('repayment_overflows.create', compact('loans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'applied_to_loan_id' => 'nullable|exists:loans,id',
            'status' => 'nullable|string',
        ]);

        RepaymentOverflow::create($request->all());

        return redirect()->route('repayment_overflows.index')->with('success', 'Overflow saved.');
    }

    public function show(RepaymentOverflow $repaymentOverflow)
    {
        return view('repayment_overflows.show', compact('repaymentOverflow'));
    }

    public function edit(RepaymentOverflow $repaymentOverflow)
    {
        $loans = Loan::all();
        return view('repayment_overflows.edit', compact('repaymentOverflow', 'loans'));
    }

    public function update(Request $request, RepaymentOverflow $repaymentOverflow)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'applied_to_loan_id' => 'nullable|exists:loans,id',
            'status' => 'nullable|string',
        ]);

        $repaymentOverflow->update($request->all());

        return redirect()->route('repayment_overflows.index')->with('success', 'Overflow updated.');
    }

    public function destroy(RepaymentOverflow $repaymentOverflow)
    {
        $repaymentOverflow->delete();
        return back()->with('success', 'Overflow deleted.');
    }
}
