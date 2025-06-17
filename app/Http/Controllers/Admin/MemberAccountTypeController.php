<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\AccountType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class MemberAccountTypeController extends Controller
{
    /**
     * Display a listing of the account types.
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        
        $accountTypes = AccountType::query()
            ->when($search, function ($query, $search) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            })
            ->withCount('memberAccounts')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/memberAccountTypes/Index', [
            'accountTypes' => $accountTypes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created account type.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:account_types,name',
            'description' => 'required|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'withdrawal_rules' => 'required|array',
            'withdrawal_rules.max_percentage_per_withdrawal' => 'nullable|numeric|min:0|max:100',
            'withdrawal_rules.max_withdrawals_per_year' => 'nullable|integer|min:0',
            'withdrawal_rules.penalty_before_months' => 'nullable|integer|min:0',
            'withdrawal_rules.penalty_percentage' => 'nullable|numeric|min:0|max:100',
            'withdrawal_rules.minimum_balance_required' => 'nullable|numeric|min:0',
        ]);

        AccountType::create($validated);

        return redirect()->route('account_types.index')
            ->with('success', 'Account type created successfully.');
    }

    /**
     * Update the specified account type.
     */
    public function update(Request $request, AccountType $accountType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('account_types', 'name')->ignore($accountType->id)
            ],
            'description' => 'required|string',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'withdrawal_rules' => 'required|array',
            'withdrawal_rules.max_percentage_per_withdrawal' => 'nullable|numeric|min:0|max:100',
            'withdrawal_rules.max_withdrawals_per_year' => 'nullable|integer|min:0',
            'withdrawal_rules.penalty_before_months' => 'nullable|integer|min:0',
            'withdrawal_rules.penalty_percentage' => 'nullable|numeric|min:0|max:100',
            'withdrawal_rules.minimum_balance_required' => 'nullable|numeric|min:0',
        ]);

        $accountType->update($validated);

        return redirect()->route('account_types.index')
            ->with('success', 'Account type updated successfully.');
    }

    /**
     * Remove the specified account type from storage.
     */
    public function destroy(AccountType $accountType): RedirectResponse
    {
        // Check if there are any member accounts using this account type
        if ($accountType->memberAccounts()->count() > 0) {
            return redirect()->route('account_types.index')
                ->with('error', 'Cannot delete account type that has active member accounts.');
        }

        $accountType->delete();

        return redirect()->route('account_types.index')
            ->with('success', 'Account type deleted successfully.');
    }
}