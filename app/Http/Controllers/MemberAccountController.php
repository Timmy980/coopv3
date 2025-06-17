<?php

namespace App\Http\Controllers;

use App\Models\MemberAccount;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MemberAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = MemberAccount::with(['user:id,first_name,last_name,email', 'accountType:id,name'])
            ->withCount(['savings', 'withdrawalRequests']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                               ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                               ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
                  })
                  ->orWhereHas('accountType', function ($typeQuery) use ($search) {
                      $typeQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Account type filter
        if ($request->filled('account_type')) {
            $query->where('account_type_id', $request->account_type);
        }

        $accounts = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Get filter options
        $accountTypes = AccountType::pluck('name', 'id');
        $users = User::where('status', 'active')->select('id', 'first_name', 'last_name', 'email')->get();
        $statuses = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended'
        ];

        return Inertia::render('Admin/memberAccounts/Index', [
            'accounts' => $accounts,
            'filters' => $request->only(['search', 'status', 'account_type']),
            'accountTypes' => $accountTypes,
            'users' => $users,
            'statuses' => $statuses
        ]);
    }

    public function show(MemberAccount $memberAccount)
    {
        $memberAccount->load([
            'user:id,first_name,last_name,email,phone_number',
            'accountType:id,name,description,interest_rate',
            'savings' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            },
            'withdrawalRequests' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ]);

        return Inertia::render('Admin/memberAccounts/Show', [
            'account' => $memberAccount
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'account_type_id' => 'required|exists:account_types,id',
            'balance' => 'nullable|numeric|min:0',
        ]);

        // Check if user already has an account of this type
        $existingAccount = MemberAccount::where('user_id', $validated['user_id'])
            ->where('account_type_id', $validated['account_type_id'])
            ->first();

        if ($existingAccount) {
            return redirect()->back()->withErrors([
                'user_id' => 'This user already has an account of this type.'
            ]);
        }

        $validated['account_number'] = $this->generateAccountNumber($validated['user_id'], $validated['account_type_id']);
        $validated['balance'] = $validated['balance'] ?? 0.00;
        $validated['status'] = 'active';

        MemberAccount::create($validated);

        return redirect()->back()->with('success', 'Member account created successfully.');
    }

    public function update(Request $request, MemberAccount $memberAccount)
    {
        $validated = $request->validate([
            'balance' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $memberAccount->update($validated);

        return redirect()->back()->with('success', 'Member account updated successfully.');
    }

    public function destroy(MemberAccount $memberAccount)
    {
        // Check if account has any transactions
        if ($memberAccount->savings_count > 0 || $memberAccount->withdrawal_requests_count > 0) {
            return redirect()->back()->withErrors([
                'delete' => 'Cannot delete account with existing transactions.'
            ]);
        }

        $memberAccount->delete();

        return redirect()->back()->with('success', 'Member account deleted successfully.');
    }

    public function toggleStatus(MemberAccount $memberAccount)
    {
        $newStatus = $memberAccount->status === 'active' ? 'inactive' : 'active';
        $memberAccount->update(['status' => $newStatus]);

        $action = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Member account has been {$action} successfully.");
    }

    /**
     * Generate a unique account number for the member account
     */
    private function generateAccountNumber($userId, $accountTypeId)
    {
        do {
            $timestamp = now()->format('ymdHis');
            $accountNumber = sprintf('%d%d%s', $userId, $accountTypeId, $timestamp);
        } while (MemberAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}