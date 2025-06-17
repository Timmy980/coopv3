<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with([
            'memberAccount.accountType',
            'memberAccount.savings' => function ($query) {
                $query->latest()->take(5);
            },
            'user' // Include user information
        ]);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('request_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('request_date', '<=', $request->end_date);
        }

        // Search by member account number if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('memberAccount', function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")->orWhereHas('user', function ($q) use ($search) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"]);
                });
            });
        }

        $withdrawalRequests = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Withdrawals/PendingApproval', [
            'withdrawalRequests' => $withdrawalRequests,
            'filters' => $request->only(['status', 'start_date', 'end_date', 'search'])
        ]);
    }

    public function approve(WithdrawalRequest $withdrawalRequest)
    {
        // Validate that the request is still pending
        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        // Check if the member account has sufficient balance
        if ($withdrawalRequest->memberAccount->balance < $withdrawalRequest->requested_amount) {
            return back()->with('error', 'Insufficient balance in member account.');
        }

        // Update the withdrawal request status
        $withdrawalRequest->update([
            'status' => 'approved',
            'approval_date' => now(),
        ]);

        // Update the member account balance
        $withdrawalRequest->memberAccount->update([
            'balance' => $withdrawalRequest->memberAccount->balance - $withdrawalRequest->requested_amount
        ]);

        return back()->with('success', 'Withdrawal request has been approved.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        // Validate that the request is still pending
        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        // Validate the rejection reason
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        // Update the withdrawal request status
        $withdrawalRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approval_date' => now(),
        ]);

        return back()->with('success', 'Withdrawal request has been rejected.');
    }

    public function show(WithdrawalRequest $withdrawalRequest)
    {
        $withdrawalRequest->load([
            'memberAccount.accountType',
            'memberAccount.savings' => function ($query) {
                $query->latest()->where('status', 'approved')->take(10);
            },
            'memberAccount.withdrawalRequests' => function ($query) {
                $query->latest()->take(10);
            },
            'user'
        ]);

        return Inertia::render('Admin/Withdrawals/Show', [
            'withdrawalRequest' => $withdrawalRequest
        ]);
    }
} 