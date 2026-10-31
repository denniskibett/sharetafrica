<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Models\Repayment;
use App\Models\Disbursement;
use App\Models\RepaymentOverflow;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = [];
    
        switch ($user->role) {
            case 'admin':
                $data = [
                    'totalLoans' => Loan::count(),
                    'totalDisbursements' => Disbursement::sum('amount'),
                    'totalRepayments' => Repayment::sum('amount'),
                    'completedLoans' => Loan::completed()->count(),
                    'recentLoans' => Loan::with(['user', 'loanType'])
                                        ->latest()
                                        ->take(5)
                                        ->get(),
                    'dueLoans' => Loan::with(['user', 'loanType'])
                        ->whereHas('loanType', function ($query) {
                            $query->select('id', 'period', 'unit');
                        })
                        ->whereRaw('
                            CASE 
                                WHEN loan_types.unit = "days" THEN DATE_ADD(borrow_date, INTERVAL loan_types.period DAY)
                                WHEN loan_types.unit = "months" THEN DATE_ADD(borrow_date, INTERVAL loan_types.period MONTH)
                            END <= ?', [now()])
                        ->where('status', 'disbursed')
                        ->join('loan_types', 'loans.loan_type_id', '=', 'loan_types.id')
                        ->select('loans.*')
                        ->orderByRaw('
                            CASE 
                                WHEN loan_types.unit = "days" THEN DATE_ADD(borrow_date, INTERVAL loan_types.period DAY)
                                WHEN loan_types.unit = "months" THEN DATE_ADD(borrow_date, INTERVAL loan_types.period MONTH)
                            END ASC')
                        ->take(10)
                        ->get()
                        ->map(function ($loan) {
                            $dueDate = \Carbon\Carbon::parse($loan->borrow_date);
                            
                            if ($loan->loanType->unit === 'days') {
                                $dueDate->addDays($loan->loanType->period);
                            } elseif ($loan->loanType->unit === 'months') {
                                $dueDate->addMonths($loan->loanType->period);
                            }

                            $today = \Carbon\Carbon::now();
                            $remainingDays = $today->diffInDays($dueDate, false); // Get signed difference

                            $loan->remaining_days = $remainingDays;
                            $loan->due_date = $dueDate;

                            if ($remainingDays < 0) {
                                $loan->status = 'overdue';
                                $loan->overdue_days = abs($remainingDays);
                            } else {
                                $loan->status = 'due';
                                $loan->overdue_days = 0;
                            }

                            // Calculate formatted overdue period
                            if ($remainingDays < 0) {
                                $interval = $today->diff($dueDate);
                                $loan->overdue_period = [
                                    'months' => $interval->m,
                                    'days' => $interval->d
                                ];
                            }

                            return $loan;
                        }),     
                    'borrowerCount' => User::where('role', 'borrower')->count(),
                    'brokerCount' => User::where('role', 'broker')->count(),
                    'tellerCount' => User::where('role', 'teller')->count(),
                    'activeBorrowerLoans' => Loan::active()->count(),
                    'brokeredLoansCount' => Loan::where('broker_status', 1)->count(),
                    'todayTransactions' => Repayment::whereDate('created_at', today())->count(),
                ];
                break;
    
            case 'borrower':
                $data = [
                    'activeLoans' => $user->loans()->active()->count(),
                    'totalRepayments' => $user->repayments()->sum('repayments.amount'),
                    'pendingLoans' => $user->loans()->pending()->count(),
                ];
                break;
    
            case 'broker':
                $data = [
                    'brokerLoans' => Loan::where('broker_status', 1)->count(),
                    'clients' => $user->borrower()->count(),
                    'pendingBrokerLoans' => Loan::where('broker_status', 1)->count(),
                    'totalCommission' => Loan::where('broker_status', 1)->count(),
                    'recentClients' => Loan::where('broker_status', 1)->count(),
                ];
                break;
    
            case 'teller':
                $data = [
                    'todaysDisbursements' => Disbursement::whereDate('disbursement_date', today())->sum('amount'),
                    'collectedRepayments' => Repayment::where('teller_id', $user->id)->sum('amount'),
                    'pendingDisbursements' => Disbursement::pending()->count(),
                    'recentTransactions' => Repayment::with('loan')
                        ->where('teller_id', $user->id)
                        ->latest()
                        ->take(5)
                        ->get(),
                ];
                break;
        }
    
        return view('dashboard', $data);
    }
}