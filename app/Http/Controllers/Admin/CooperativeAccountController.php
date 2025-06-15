<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\CooperativeAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CooperativeAccountController extends Controller
{
    /**
     * Display a listing of cooperative accounts.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $accounts = CooperativeAccount::query()
            ->when($search, function ($query, $search) {
                $query->whereRaw('LOWER(account_name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(account_number) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(bank_name) LIKE ?', ["%{$search}%"]);
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->withCount(['savings', 'disbursedLoans', 'receivedLoanRepayments', 'disbursedWithdrawals'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Admin/cooperativeAccounts/Index', [
            'accounts' => $accounts,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'accountTypes' => [
                'savings' => 'Savings Account',
                'current' => 'Current Account',
                'fixed_deposit' => 'Fixed Deposit',
                'loan_disbursement' => 'Loan Disbursement Account',
                'operational' => 'Operational Account',
                'reserve' => 'Reserve Account',
            ],
            'statuses' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'suspended' => 'Suspended',
            ],
        ]);
    }

    /**
     * Show the form for creating a new cooperative account.
     */
    public function create(): Response
    {
        return Inertia::render('CooperativeAccounts/Create', [
            'accountTypes' => [
                'savings' => 'Savings Account',
                'current' => 'Current Account',
                'fixed_deposit' => 'Fixed Deposit',
                'loan_disbursement' => 'Loan Disbursement Account',
                'operational' => 'Operational Account',
                'reserve' => 'Reserve Account',
            ],
        ]);
    }

    /**
     * Store a newly created cooperative account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255|unique:cooperative_accounts,account_number',
            'balance' => 'nullable|numeric|min:0',
        ]);

        CooperativeAccount::create($validated);

        return Redirect::route('cooperative_accounts.index')
                      ->with('success', 'Cooperative account created successfully.');
    }

    /**
     * Display the specified cooperative account.
     */
    public function show(CooperativeAccount $cooperativeAccount): Response
    {
        $cooperativeAccount->load([
            'savings:id,cooperative_account_id,amount,created_at',
            'disbursedLoans:id,coop_account_disbursement_id,approved_amount,created_at',
            'receivedLoanRepayments:id,coop_account_receipt_id,amount,created_at',
            'disbursedWithdrawals:id,coop_account_disbursement_id,requested_amount,created_at',
        ]);

        // Calculate transaction summary
        $totalSavings = $cooperativeAccount->savings->sum('amount');
        $totalDisbursements = $cooperativeAccount->disbursedLoans->sum('approved_amount') + 
                             $cooperativeAccount->disbursedWithdrawals->sum('requested_amount');
        $totalRepayments = $cooperativeAccount->receivedLoanRepayments->sum('amount');

        return Inertia::render('Admin/cooperativeAccounts/Show', [
            'account' => $cooperativeAccount,
            'summary' => [
                'total_savings' => $totalSavings,
                'total_disbursements' => $totalDisbursements,
                'total_repayments' => $totalRepayments,
                'net_flow' => $totalSavings + $totalRepayments - $totalDisbursements,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified cooperative account.
     */
    public function edit(CooperativeAccount $cooperativeAccount): Response
    {
        return Inertia::render('CooperativeAccounts/Edit', [
            'account' => $cooperativeAccount,
            'accountTypes' => [
                'savings' => 'Savings Account',
                'current' => 'Current Account',
                'fixed_deposit' => 'Fixed Deposit',
                'loan_disbursement' => 'Loan Disbursement Account',
                'operational' => 'Operational Account',
                'reserve' => 'Reserve Account',
            ],
        ]);
    }

    /**
     * Update the specified cooperative account.
     */
    public function update(Request $request, CooperativeAccount $cooperativeAccount)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255|unique:cooperative_accounts,account_number,' . $cooperativeAccount->id,
            'balance' => 'nullable|numeric|min:0',
        ]);

        $cooperativeAccount->update($validated);

        return Redirect::route('cooperative_accounts.index')
                      ->with('success', 'Cooperative account updated successfully.');
    }

    /**
     * Remove the specified cooperative account.
     */
    public function destroy(CooperativeAccount $cooperativeAccount)
    {
        // Check if account has any associated transactions
        $hasTransactions = $cooperativeAccount->savings()->exists() ||
                          $cooperativeAccount->disbursedLoans()->exists() ||
                          $cooperativeAccount->receivedLoanRepayments()->exists() ||
                          $cooperativeAccount->disbursedWithdrawals()->exists();

        if ($hasTransactions) {
            return back()->withErrors([
                'delete' => 'Cannot delete account with existing transactions. Please contact administrator.'
            ]);
        }

        $cooperativeAccount->delete();

        return Redirect::route('cooperative_accounts.index')
                      ->with('success', 'Cooperative account deleted successfully.');
    }

    /**
     * Toggle the status of the specified cooperative account.
     */
    public function toggleStatus(CooperativeAccount $cooperativeAccount)
    {
        $newStatus = match ($cooperativeAccount->status) {
            'active' => 'inactive',
            'inactive' => 'active',
            'suspended' => 'active',
            default => 'active',
        };

        $cooperativeAccount->update(['status' => $newStatus]);

        return Redirect::route('cooperative_accounts.index')
                      ->with('success', "Account status changed to {$newStatus}.");
    }
}