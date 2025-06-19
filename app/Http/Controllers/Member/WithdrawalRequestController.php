<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\MemberAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WithdrawalRequestController extends Controller
{
    /**
     * Display a listing of the member's withdrawal requests.
     */
    public function index()
    {
        $withdrawalRequests = WithdrawalRequest::with(['memberAccount.accountType'])
            ->whereHas('memberAccount', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Member/WithdrawalRequests/Index', [
            'withdrawalRequests' => $withdrawalRequests
        ]);
    }

    /**
     * Show the form for creating a new withdrawal request.
     */
    public function create()
    {
        $memberAccounts = MemberAccount::with(['accountType'])
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->get();

        return Inertia::render('Member/WithdrawalRequests/Create', [
            'memberAccounts' => $memberAccounts
        ]);
    }

    /**
     * Store a newly created withdrawal request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_account_id' => 'required|exists:member_accounts,id',
            'requested_amount' => 'required|numeric|min:0.01',
            'member_bank_account_for_receipt' => 'required|string',
        ]);

        // Verify account ownership
        $memberAccount = MemberAccount::where('id', $validated['member_account_id'])
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        // Check withdrawal rules
        $this->validateWithdrawalRules($memberAccount, $validated['requested_amount']);

        // Create withdrawal request
        $withdrawalRequest = new WithdrawalRequest([
            'user_id' => Auth::id(),
            'member_account_id' => $validated['member_account_id'],
            'requested_amount' => $validated['requested_amount'],
            'request_date' => now(),
            'status' => 'pending',
            'member_bank_account_for_receipt' => $validated['member_bank_account_for_receipt'],
        ]);

        $withdrawalRequest->save();

        return redirect()->route('member.withdrawals.index')
            ->with('success', 'Withdrawal request submitted successfully.');
    }

    /**
     * Display the specified withdrawal request.
     */
    public function show(WithdrawalRequest $withdrawalRequest)
    {
        // Ensure the withdrawal request belongs to the authenticated user
        if ($withdrawalRequest->user_id !== Auth::id()) {
            abort(403);
        }

        $withdrawalRequest->load(['memberAccount.accountType']);

        return Inertia::render('Member/WithdrawalRequests/Show', [
            'withdrawalRequest' => $withdrawalRequest
        ]);
    }

    /**
     * Validate withdrawal rules based on account type settings
     */
    private function validateWithdrawalRules(MemberAccount $account, float $requestedAmount)
    {
        $rules = $account->accountType->withdrawal_rules;

        // Check minimum balance requirement
        if (isset($rules['minimum_balance_required'])) {
            $remainingBalance = $account->balance - $requestedAmount;
            if ($remainingBalance < $rules['minimum_balance_required']) {
                abort(422, 'Withdrawal would breach minimum balance requirement.');
            }
        }

        // Check maximum percentage per withdrawal
        if (isset($rules['max_percentage_per_withdrawal'])) {
            $maxAmount = $account->balance * ($rules['max_percentage_per_withdrawal'] / 100);
            if ($requestedAmount > $maxAmount) {
                abort(422, "Cannot withdraw more than {$rules['max_percentage_per_withdrawal']}% of account balance.");
            }
        }

        // Check maximum withdrawals per year
        if (isset($rules['max_withdrawals_per_year'])) {
            $yearlyWithdrawals = WithdrawalRequest::where('member_account_id', $account->id)
                ->whereYear('request_date', now()->year)
                ->count();

            if ($yearlyWithdrawals >= $rules['max_withdrawals_per_year']) {
                abort(422, "Maximum yearly withdrawals limit reached.");
            }
        }
    }
} 