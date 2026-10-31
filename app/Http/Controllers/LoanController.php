<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanType;
use App\Models\User;
use App\Models\Repayment;
use App\Models\Broker;
use App\Models\Borrower;
use Illuminate\Http\Request;
use Carbon\Carbon;
use FPDF;

class LoanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        
        if ($user->role === 'admin') {
            // Get all loans with relationships
            $allLoans = Loan::with(['user', 'loanType', 'broker'])
                          ->orderBy('borrow_date', 'desc')
                          ->get();
    
            // Calculate statistics
            $stats = [
                'totalLoans' => $allLoans->count(),
                'total_disbursed' => $allLoans->sum('amount'),
                'total_repayments' => $allLoans->sum(function($loan) {
                    return $loan->repayments->sum('amount');
                }),
                'total_broker_fees' => $allLoans->sum('broker_fee'),
                'active_loans' => $allLoans->where('status', 'disbursed')->count(),
                'pending_loans' => $allLoans->where('status', 'pending')->count(),
            ];
    
            return view('loans.index', [
                'allLoans' => $allLoans,
                'stats' => $stats
            ]);
        } 
        elseif ($user->role === 'broker') {
            // Get the broker profile linked to the authenticated user
            $broker = Broker::where('user_id', $user->id)->first();
            
            if (!$broker) {
                return view('loans.index', [
                    'clientLoans' => collect(),
                    'stats' => [
                        'active_loans' => 0,
                        'pending_loans' => 0,
                        'broker_earnings' => 0,
                        'total_clients' => 0
                    ]
                ]);
            }
            
            // Get all borrower user IDs associated with this broker
            $borrowerUserIds = $broker->borrowers()->pluck('user_id');
            
            // Get loans for these borrower users
            $clientLoans = Loan::whereIn('user_id', $borrowerUserIds)
                ->with(['user', 'loanType'])
                ->orderBy('borrow_date', 'desc')  
                ->get();
    
            // Calculate broker statistics
            $stats = [
                'active_loans' => $clientLoans->where('status', 'active')->count(),
                'pending_loans' => $clientLoans->where('status', 'pending')->count(),
                'broker_earnings' => $clientLoans->sum('broker_fee'),
                'total_clients' => count(array_unique($borrowerUserIds->toArray()))
            ];
    
            return view('loans.index', [
                'clientLoans' => $clientLoans,
                'stats' => $stats
            ]);
        }
        elseif ($user->role === 'teller') {
            $activeLoans = Loan::with(['user', 'loanType'])
                             ->where('status', 'active')
                             ->orderBy('borrow_date', 'asc') // Show soonest due first
                             ->get();
    
            // Teller stats (similar to broker view)
            $stats = [
                'active_loans' => $activeLoans->count(),
                'pending_loans' => Loan::where('status', 'pending')->count(),
                'total_repayments' => $activeLoans->sum(function($loan) {
                    return $loan->repayments->sum('amount');
                }),
                'total_penalties' => $activeLoans->sum(function($loan) {
                    return $loan->repayments->sum('penalty_amount');
                })
            ];
    
            return view('loans.index', [
                'activeLoans' => $activeLoans,
                'stats' => $stats
            ]);
        }
        else { // Borrower
            $userLoans = $user->loans()
                            ->with('loanType')
                            ->orderBy('borrow_date', 'desc')
                            ->get();
    
            // Calculate borrower statistics
            $stats = [
                'active_loans' => $userLoans->where('status', 'active')->count(),
                'pending_loans' => $userLoans->where('status', 'pending')->count(),
                'total_borrowed' => $userLoans->sum('amount'),
                'total_repaid' => $userLoans->sum(function($loan) {
                    return $loan->repayments->sum('amount');
                }),
                'total_penalties' => $userLoans->sum(function($loan) {
                    return $loan->repayments->sum('penalty_amount');
                })
            ];
    
            return view('loans.index', [
                'userLoans' => $userLoans,
                'stats' => $stats
            ]);
        }
    }

    public function show($id, $loanId = null) {
        // Get the authenticated user
        $user = auth()->user();
        
        // Fetch all brokers and borrowers for the view
        $brokers = Broker::with('user')->get();
        $borrowers = Borrower::all();
        
        // Check if the user is authenticated
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        
        // Prepare the loan query with necessary relationships
        $loanQuery = Loan::with(['disbursements', 'repayments', 'loanType', 'user.borrower']);
        
        // Fetch loan with role-based access control
        try {
            if ($user->role === 'admin') {
                $loan = $loanQuery->where('user_id', $id)
                         ->where('id', $loanId)
                         ->firstOrFail();
            } else if (in_array($user->role, ['broker', 'teller'])) {
                $loan = $loanQuery->findOrFail($loanId);
            } else if ($user->role === 'borrower') {
                $loan = $loanQuery->where('id', $loanId)
                         ->where('user_id', $user->id)
                         ->whereIn('status', ['approved', 'disbursed'])
                         ->firstOrFail();
            } else {
                abort(403, 'Unauthorized role');
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Loan not found'], 404);
        }
    
        // VALIDATION CHECKS
        if (!$loan->loanType) {
            abort(422, 'Loan type is not defined for this loan');
        }
    
        // CORE CALCULATIONS
        $borrower = $loan->user->borrower;
        $clientType = $borrower->client_type ?? 0;
        $broker = $borrower->broker ?? null;
    
        $loanType = $loan->loanType;
        $principal = $loan->amount;
        $interestRate = $loanType->interest_rate;
        $interest = ($interestRate / 100) * $principal;
        $principalPlusInterest = $principal + $interest;
    
        // DATE CALCULATIONS
        $disbursementDate = $loan->disbursements->first()->date ?? $loan->borrow_date;
        $period = $loanType->period ?? 0;
        $periodUnit = $loanType->unit; 
        $dueDate = \Carbon\Carbon::parse($disbursementDate);
    
        switch ($periodUnit) {
            case 'days':
                $dueDate->addDays($period);
                break;
            case 'weeks':
                $dueDate->addWeeks($period);
                break;
            case 'months':
                $dueDate->addMonths($period);
                break;
            case 'years':
                $dueDate->addYears($period);
                break;
            default:
                abort(422, 'Invalid period unit');
        }
    
        // Get all repayments sorted by date
        $repayments = $loan->repayments->sortBy('repayment_date');
        $lastRepaymentDate = $repayments->isNotEmpty() 
            ? \Carbon\Carbon::parse($repayments->last()->repayment_date) 
            : null;
    
        // Calculate total repayments made before due date
        $repaymentsBeforeDue = $repayments->filter(function($repayment) use ($dueDate) {
            return \Carbon\Carbon::parse($repayment->repayment_date)->lt($dueDate);
        });
        $totalRepaymentsBeforeDue = $repaymentsBeforeDue->sum('amount');
    
        // Calculate total repayments made on or after due date
        $repaymentsAfterDue = $repayments->filter(function($repayment) use ($dueDate) {
            return \Carbon\Carbon::parse($repayment->repayment_date)->gte($dueDate);
        });
        $totalRepaymentsAfterDue = $repaymentsAfterDue->sum('amount');
    
        // Calculate outstanding balance as of due date
        $outstandingAtDueDate = $principalPlusInterest - $totalRepaymentsBeforeDue;
    
        // Calculate daily penalty and outstanding balance day by day after due date
        $penaltyAmount = 0;
        $currentBalance = $outstandingAtDueDate;
        $daysLate = 0;
        $basePenaltyRate = $loanType->penalty_rate;
    
        // If there's still balance at due date, start calculating penalties
        if ($currentBalance > 0) {
            $currentDate = $dueDate->copy ();
            $today = now();
            
            // Group repayments after due date by date
            $repaymentsByDate = $repaymentsAfterDue->groupBy(function($repayment) {
                return \Carbon\Carbon::parse($repayment->repayment_date)->toDateString();
            });
    
            while ($currentDate->lte($today) && $currentBalance > 0) {
                // Check if there are repayments on this day
                $dateKey = $currentDate->toDateString();
                $dailyRepayment = $repaymentsByDate->has($dateKey) 
                    ? $repaymentsByDate->get($dateKey)->sum('amount') 
                    : 0;
    
                // Apply repayment to balance
                if ($dailyRepayment > 0) {
                    if ($dailyRepayment >= $currentBalance) {
                        // Full repayment, no more penalty after this day
                        $currentBalance = 0;
                    } else {
                        // Partial repayment
                        $currentBalance -= $dailyRepayment;
                    }
                }
    
                // Add penalty for this day if balance remains
                if ($currentBalance > 0) {
                    $penaltyAmount += ($basePenaltyRate / 100) * $outstandingAtDueDate;
                    $daysLate++;
                }
    
                $currentDate->addDay();
            }
        }
    
        // Current outstanding balance
        $outstandingBalance = $currentBalance;
    
        // Broker fees and other calculations
        $clientType = $borrower->client_type ?? 0;
        $penaltyRate = ($clientType == 0 && $broker) 
            ? $broker->penalty_client 
            : ($broker ? $broker->penalty_broker : 0);
    
        $brokerStatus = $loan->broker_status;
        $brokerFees = 0;
        if ($brokerStatus == 1) {
            $brokerRate = ($clientType == 0 && $broker) 
                ? $broker->interest_client 
                : ($broker ? $broker->interest_broker : 0);
            $brokerFees = ($brokerRate / 100) * $interest;
        }
    
        // Calculate base penalty (simple daily rate)
        $basePenalty = $daysLate * $outstandingAtDueDate * ($basePenaltyRate / 100);
        
        // Calculate broker penalty fees
        $brokerPenaltyFees = ($penaltyRate / 100) * $basePenalty;
    
        // Total combined calculation (P+I+Pe) - (BI+BP) - R
        $totalCombined = $principalPlusInterest 
                    + $basePenalty 
                    - ($totalRepaymentsBeforeDue + $totalRepaymentsAfterDue);
    
        // Broker fees paid (including penalties from controller's penaltyAmount)
        $totalBrokerFees = $brokerFees + ($penaltyAmount * $penaltyRate / 100);
        $netEarnings = $interest + $penaltyAmount - $totalBrokerFees;
    
        // P&L CALCULATION
        $pl = ($principal + $interest + $penaltyAmount) - $brokerFees - ($totalRepaymentsBeforeDue + $totalRepaymentsAfterDue);
    
        return view('loans.show', compact(
            'loan',
            'loanType',
            'interestRate',
            'period',
            'periodUnit',
            'clientType',
            'dueDate',
            'disbursementDate',
            'daysLate',
            'penaltyAmount',
            'brokerFees',
            'pl',
            'principal',
            'interest',
            'totalRepaymentsBeforeDue',
            'totalRepaymentsAfterDue',
            'outstandingBalance',
            'principalPlusInterest',
            'brokers',
            'borrowers',
            'basePenaltyRate',
            'broker',
            'lastRepaymentDate',
            'outstandingAtDueDate',
            'penaltyRate',
            'basePenalty', 
            'brokerPenaltyFees',
            'totalCombined',
            'totalBrokerFees',
            'netEarnings'
        ));
    }

    public function create()
    {
        $users = auth()->user()->role === 'admin' ? User::all() : collect([auth()->user()]);
        $loanTypes = LoanType::all(); // Retrieve all loan types

        return view('loans.create', [
            'users' => $users,
            'loanTypes' => $loanTypes, 
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'loan_type_id' => 'required|exists:loan_types,id',
            'amount' => 'required|numeric',
            'borrow_date' => 'required|date',
            'status' => 'required|string',
            'broker_status' => 'required|string',
        ]);
    
        // Create a new loan with the validated data
        Loan::create([
            'user_id' => $request->user_id,
            'loan_type_id' => $request->loan_type_id,
            'amount' => $request->amount,
            'borrow_date' => $request->borrow_date,
            'status' => $request->status,
            'broker_status' => $request->broker_status,
        ]);
    
        return redirect()->route('loans.index')->with('success', 'Loan created successfully.');
    }

    public function edit($id)
    {
        $repayment = Repayment::findOrFail($id);
        return view('repayments.edit', compact('repayment'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'transaction' => 'required|string',
            'repayment_date' => 'required|date',
        ]);
    
        $repayment = Repayment::findOrFail($id);
        $repayment->update($request->only(['amount', 'transaction', 'repayment_date']));
    
        return redirect()->route('repayments.index')->with('success', 'Repayment updated successfully.');
    }

    public function destroy($id)
    {
        $loan = Loan::findOrFail($id);
        $loan->delete();

        return redirect()->route('loans.index');
    }

        public function generatePdf($id, $loanId = null)
        {
            $user = auth()->user();
            
            if (!$user) {
                abort(403, 'Unauthorized');
            }
            
            $loanQuery = Loan::with(['disbursements', 'repayments', 'loanType', 'user.borrower']);
            
            // Role-based access control
            if ($user->role === 'admin') {
                $loan = $loanQuery->where('user_id', $id)
                         ->where('id', $loanId)
                         ->firstOrFail();
            } else if (in_array($user->role, ['broker', 'teller'])) {
                $loan = $loanQuery->findOrFail($loanId);
            } else if ($user->role === 'borrower') {
                $loan = $loanQuery->where('id', $loanId)
                         ->where('user_id', $user->id)
                         ->whereIn('status', ['approved', 'disbursed'])
                         ->firstOrFail();
            } else {
                abort(403, 'Unauthorized role');
            }
    
            // VALIDATION CHECKS
            if (!$loan->loanType) {
                abort(422, 'Loan type is not defined for this loan');
            }
    
            // CALCULATION LOGIC
            $borrower = $loan->user->borrower;
            $clientType = $borrower->client_type ?? 0;
            $broker = $borrower->broker ?? null;
            $loanType = $loan->loanType;
            $principal = $loan->amount;
            $interestRate = $loanType->interest_rate;
            $interest = ($interestRate / 100) * $principal;
            $principalPlusInterest = $principal + $interest;
    
            // Date calculations
            $disbursementDate = $loan->disbursements->first()->date ?? $loan->borrow_date;
            $period = $loanType->period ?? 0;
            $periodUnit = $loanType->unit; 
            $dueDate = Carbon::parse($disbursementDate);
    
            switch ($periodUnit) {
                case 'days': $dueDate->addDays($period); break;
                case 'weeks': $dueDate->addWeeks($period); break;
                case 'months': $dueDate->addMonths($period); break;
                case 'years': $dueDate->addYears($period); break;
                default: abort(422, 'Invalid period unit');
            }
    
            // Repayment calculations
            $repayments = $loan->repayments->sortBy('repayment_date');
            $lastRepaymentDate = $repayments->isNotEmpty() 
                ? Carbon::parse($repayments->last()->repayment_date) 
                : null;
    
            // Repayment splits
            $repaymentsBeforeDue = $repayments->filter(fn($r) => Carbon::parse($r->repayment_date)->lt($dueDate));
            $totalRepaymentsBeforeDue = $repaymentsBeforeDue->sum('amount');
            $repaymentsAfterDue = $repayments->filter(fn($r) => Carbon::parse($r->repayment_date)->gte($dueDate));
            $totalRepaymentsAfterDue = $repaymentsAfterDue->sum('amount');
    
            // Penalty calculations
            $outstandingAtDueDate = $principalPlusInterest - $totalRepaymentsBeforeDue;
            $penaltyAmount = 0;
            $currentBalance = $outstandingAtDueDate;
            $daysLate = 0;
            $basePenaltyRate = $loanType->penalty_rate;
    
            if ($currentBalance > 0) {
                $currentDate = $dueDate->copy();
                $today = now();
                
                $repaymentsByDate = $repaymentsAfterDue->groupBy(function($r) {
                    return Carbon::parse($r->repayment_date)->toDateString();
                });
    
                while ($currentDate->lte($today)) {
                    $dateKey = $currentDate->toDateString();
                    $dailyRepayment = $repaymentsByDate->has($dateKey) 
                        ? $repaymentsByDate->get($dateKey)->sum('amount') 
                        : 0;
    
                    if ($dailyRepayment > 0) {
                        $currentBalance = max(0, $currentBalance - $dailyRepayment);
                    }
    
                    if ($currentBalance > 0) {
                        $penaltyAmount += ($basePenaltyRate / 100) * $outstandingAtDueDate;
                        $daysLate++;
                    }
    
                    $currentDate->addDay();
                }
            }
    
            // Broker calculations
            $penaltyRate = ($clientType == 0 && $broker) 
                ? $broker->penalty_client 
                : ($broker ? $broker->penalty_broker : 0);
    
            $brokerFees = 0;
            if ($loan->broker_status == 1) {
                $brokerRate = ($clientType == 0 && $broker) 
                    ? $broker->interest_client 
                    : ($broker ? $broker->interest_broker : 0);
                $brokerFees = ($brokerRate / 100) * $interest;
            }
    
            $basePenalty = $daysLate * $outstandingAtDueDate * ($basePenaltyRate / 100);
            $brokerPenaltyFees = ($penaltyRate / 100) * $basePenalty;
            $totalCombined = $principalPlusInterest + $basePenalty - ($totalRepaymentsBeforeDue + $totalRepaymentsAfterDue);
            $totalBrokerFees = $brokerFees + ($penaltyAmount * $penaltyRate / 100);
            $netEarnings = $interest + $penaltyAmount - $totalBrokerFees;
    
            // Create combined transactions
            $transactions = collect()
                ->merge($loan->disbursements->map(function ($disbursement) use ($principal, $interest) {
                    return (object)[
                        'date' => $disbursement->date,
                        'description' => 'Loan Disbursement',
                        'type' => 'debit',
                        'amount' => $disbursement->amount,
                        'balance' => $principal + $interest
                    ];
                }))
                ->merge($loan->repayments->map(function ($repayment) {
                    return (object)[
                        'date' => $repayment->repayment_date,
                        'description' => 'Loan Repayment',
                        'type' => 'credit',
                        'amount' => $repayment->amount,
                        'balance' => null
                    ];
                }))
                ->sortBy('date');
    
            // Calculate running balance
            $runningBalance = $principal + $interest;
            $transactions = $transactions->map(function ($transaction) use (&$runningBalance) {
                if ($transaction->type === 'credit') {
                    $runningBalance -= $transaction->amount;
                }
                $transaction->balance = $runningBalance;
                return $transaction;
            });
    
            // PDF GENERATION
            $pdf = new \FPDF();
            $pdf->AddPage('P', 'A4');
            $pdf->SetAutoPageBreak(true, 15);
            
            // Configuration
            $primaryColor = [41, 128, 185];
            $secondaryColor = [108, 117, 125];
            $font = 'Helvetica';
            
            // Header Section
            $pdf->SetFont($font, 'B', 12);
            $pdf->Cell(0, 8, config('app.name'), 0, 1, 'L');
            $pdf->SetFont($font, '', 9);
            $pdf->Cell(0, 5, 'Generated: ' . now()->format('d/m/Y H:i'), 0, 1, 'L');
            $pdf->Ln(5);
        
            // Borrower/Loan Info
            $pdf->SetFont($font, 'B', 10);
            $pdf->Cell(95, 6, 'Borrower Information', 0, 0);
            $pdf->Cell(95, 6, 'Loan Details', 0, 1);
            $pdf->SetFont($font, '', 9);
        
            $pdf->Cell(95, 6, 'Name: ' . $loan->user->name, 0, 0);
            $pdf->Cell(95, 6, 'Loan ID: #' . str_pad($loan->id, 5, '0', STR_PAD_LEFT), 0, 1);
            $pdf->Cell(95, 6, 'Contact: ' . ($loan->user->phone ?? 'N/A'), 0, 0);
            $pdf->Cell(95, 6, 'Disbursed: ' . Carbon::parse($disbursementDate)->format('d M Y'), 0, 1);
            $pdf->Ln(3);
        
            // Summary Box
            $pdf->SetFillColor(241, 243, 245);
            $pdf->SetFont($font, 'B', 10);
            $pdf->Cell(0, 8, ' Account Summary ', 0, 1, 'L', true);
            $pdf->SetFont($font, '', 9);
        
            $this->pdfSummaryRow($pdf, 'Principal Amount', $principal, $primaryColor);
            $this->pdfSummaryRow($pdf, 'Interest Rate', $interestRate . '%', $secondaryColor);
            $this->pdfSummaryRow($pdf, 'Total Interest', $interest, $primaryColor);
            $this->pdfSummaryRow($pdf, 'Amount Due', $principalPlusInterest, $primaryColor);
            $this->pdfSummaryRow($pdf, 'Total Paid', $totalRepaymentsBeforeDue + $totalRepaymentsAfterDue, $secondaryColor);
            $this->pdfSummaryRow($pdf, 'Current Balance', $totalCombined, $primaryColor, 'B');
            $pdf->Ln(5);
        
            // Key Dates
            $pdf->SetFont($font, 'B', 9);
            $pdf->Cell(63, 6, 'Disbursement: ' . Carbon::parse($disbursementDate)->format('d M Y'), 0, 0);
            $pdf->Cell(63, 6, 'Due Date: ' . $dueDate->format('d M Y'), 0, 0);
            $pdf->Cell(63, 6, 'Status: ' . ($daysLate > 0 ? "{$daysLate} Days Late" : 'Current'), 0, 1);
            $pdf->Ln(5);
        
            // Transactions Table
            $this->pdfSectionHeader($pdf, 'Transaction History', $primaryColor);
            
            $pdf->SetFont($font, 'B', 8);
            $pdf->Cell(30, 6, 'Date', 1, 0, 'C');
            $pdf->Cell(40, 6, 'Description', 1, 0, 'C');
            $pdf->Cell(30, 6, 'Debit', 1, 0, 'C');
            $pdf->Cell(30, 6, 'Credit', 1, 0, 'C');
            $pdf->Cell(30, 6, 'Balance', 1, 1, 'C');
        
            if ($transactions->isNotEmpty()) {
                $pdf->SetFont($font, '', 8);
                foreach ($transactions as $transaction) {
                    $pdf->Cell(30, 6, Carbon::parse($transaction->date)->format('d M'), 1, 0, 'C');
                    $pdf->Cell(40, 6, substr($transaction->description, 0, 15), 1, 0);
                    $pdf->Cell(30, 6, $transaction->type === 'debit' ? number_format($transaction->amount, 2) : '-', 1, 0, 'R');
                    $pdf->Cell(30, 6, $transaction->type === 'credit' ? number_format($transaction->amount, 2) : '-', 1, 0, 'R');
                    $pdf->Cell(30, 6, number_format($transaction->balance, 2), 1, 1, 'R');
                }
            } else {
                $pdf->SetFont($font, 'I', 8);
                $pdf->Cell(0, 6, 'No transactions found', 1, 1, 'C');
            }
    
            // Footer
            $pdf->SetY(-15);
            $pdf->SetFont($font, 'I', 8);
            $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . ' | ' . config('app.url'), 0, 0, 'C');
        
            return $pdf->Output('D', 'loan-statement-'.$loan->id.'.pdf');
        }
    
        private function pdfSectionHeader($pdf, $text, $color)
        {
            $pdf->SetFillColor($color[0], $color[1], $color[2]);
            $pdf->SetTextColor(255);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(0, 6, " $text ", 0, 1, 'L', true);
            $pdf->SetTextColor(0);
            $pdf->Ln(2);
        }
        
        private function pdfSummaryRow($pdf, $label, $value, $color, $style = '')
        {
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor($color[0], $color[1], $color[2]);
            $pdf->Cell(60, 6, $label, 0, 0);
            
            $pdf->SetFont('Helvetica', $style, 9);
            $pdf->SetTextColor(0);
            $pdf->Cell(0, 6, is_numeric($value) ? number_format($value, 2) : $value, 0, 1, 'R');
            $pdf->Cell(0, 1, '', 'T');
        }
    }