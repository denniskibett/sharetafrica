<?php

namespace App\Http\Controllers;

use App\Models\Repayment;
use App\Models\Loan;
use Illuminate\Http\Request;

class RepaymentController extends Controller
{
    public function index()
    {
        $repayments = Repayment::latest()->paginate(10);
        return view('repayments.index', compact('repayments'));
    }

    public function create()
    {
        $loans = Loan::all();
        return view('repayments.create', compact('loans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'transaction' => 'required|string',
            'repayment_date' => 'required|date',
        ]);

        Repayment::create($request->all());

        return redirect()->route('repayments.index')->with('success', 'Repayment recorded.');
    }

    public function show(Repayment $repayment)
    {
        return view('repayments.show', compact('repayment'));
    }

    public function edit(Repayment $repayment)
    {
        $loans = Loan::all();
        return view('repayments.edit', compact('repayment', 'loans'));
    }

    public function update(Request $request, Repayment $repayment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'transaction' => 'required|string',
            'repayment_date' => 'required|date',
        ]);

        $repayment->update($request->all());

        return redirect()->route('repayments.index')->with('success', 'Repayment updated.');
    }

    public function destroy(Repayment $repayment)
    {
        $repayment->delete();
        return back()->with('success', 'Repayment deleted.');
    }
}
