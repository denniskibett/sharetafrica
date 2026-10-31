<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Borrower; 
use App\Models\Broker;
use App\Models\Loans;
use App\Models\Repayments;
use App\Models\Penalties;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index()
    {
        // Fetch all users
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::with([
            'loans',
            'disbursements',
            'repayments'
        ])->findOrFail($id);
    
        $totalLoans = $user->loans->sum('amount');
        $totalDisbursed = $user->disbursements->sum('amount');
        $totalRepaid = $user->repayments->sum('amount');
    
        return view('users.show', compact(
            'user',
            'totalLoans',
            'totalDisbursed',
            'totalRepaid'
        ));
    }
    

    public function create()
    {
        $brokers = Broker::all();
        return view('users.create', compact('brokers'));
    }
    
    public function store(Request $request)
    {
        // Validate the request data with role-specific rules
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|unique:users,phone',
            'role' => 'required|in:admin,broker,borrower',
    
            // Broker-specific fields
            'penalty_client' => 'required_if:role,broker|numeric|nullable',
            'penalty_broker' => 'required_if:role,broker|numeric|nullable',
            'interest_client' => 'required_if:role,broker|numeric|nullable',
            'interest_broker' => 'required_if:role,broker|numeric|nullable',
            'cert_no' => 'required_if:role,broker|unique:brokers,cert_no|nullable',
    
            // Borrower-specific fields
            'national_id' => 'required_if:role,borrower|string|max:20|unique:borrowers,national_id|nullable',
            'client_type' => 'required_if:role,borrower|in:0,1|nullable',
            'status' => 'required_if:role,borrower|in:0,1|nullable',
            'broker_id' => 'nullable|exists:brokers,id',
        ]);
    
        // Create the user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'phone' => $validatedData['phone'],
            'role' => $validatedData['role'],
        ]);
    
        // Handle role-specific data
        switch ($validatedData['role']) {
            case 'broker':
                Broker::create([
                    'user_id' => $user->id,
                    'penalty_client' => $validatedData['penalty_client'],
                    'penalty_broker' => $validatedData['penalty_broker'],
                    'interest_client' => $validatedData['interest_client'],
                    'interest_broker' => $validatedData['interest_broker'],
                    'cert_no' => $validatedData['cert_no'],
                ]);
                break;
    
            case 'borrower':
                Borrower::create([
                    'user_id' => $user->id,
                    'broker_id' => $validatedData['broker_id'] ?? null,
                    'national_id' => $validatedData['national_id'],
                    'client_type' => $validatedData['client_type'],
                    'status' => $validatedData['status'],
                ]);
                break;
    
            // No additional data needed for 'admin' role
        }
    
        // Redirect with success message
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }
    

    public function edit(User $user)
    {
        $brokers = Broker::all();
        $broker = $user->broker;
        $borrower = $user->borrower;
    
        return view('users.edit', compact('user', 'brokers', 'broker', 'borrower'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'phone' => 'required|unique:users,phone,' . $user->id,
            'national_id' => 'required_if:role,borrower|string|max:20|unique:borrowers,national_id,' . optional($user->borrower)->id,
            'broker_id' => 'nullable|exists:brokers,id',
            'client_type' => 'required_if:role,borrower|in:0,1',
            'status' => 'required_if:role,borrower|in:0,1',
            'penalty_client' => 'required_if:role,broker|numeric',
            'penalty_broker' => 'required_if:role,broker|numeric',
            'interest_client' => 'required_if:role,broker|numeric',
            'interest_broker' => 'required_if:role,broker|numeric',
            'cert_no' => 'required_if:role,broker|unique:brokers,cert_no,' . optional($user->broker)->id,
        ];
    
        $request->validate($rules);
    
        // Update user
        $userData = $request->only('name', 'email', 'phone');
        if ($request->filled('password')) {
            $userData['password'] = bcrypt($request->password);
        }
        $user->update($userData);
    
        // Update related model
        if ($user->role === 'borrower') {
            $borrowerData = $request->only('broker_id', 'national_id', 'client_type', 'status');
            if ($user->borrower) {
                $user->borrower->update($borrowerData);
            } else {
                $user->borrower()->create($borrowerData);
            }
        } elseif ($user->role === 'broker') {
            $brokerData = $request->only('penalty_client', 'penalty_broker', 'interest_client', 'interest_broker', 'cert_no');
            if ($user->broker) {
                $user->broker->update($brokerData);
            } else {
                $user->broker()->create($brokerData);
            }
        }
    
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        // Delete the user
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index');
    }
}
