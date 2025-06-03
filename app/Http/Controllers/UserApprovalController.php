<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AccountType;
use App\Models\MemberAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserApprovalController extends Controller
{
    public function index()
    {
        return Inertia::render('UserApproval/Index', [
            'users' => User::where('status', 'pending_approval')
                ->select('id', 'first_name', 'last_name', 'email', 'phone_number', 'created_at')
                ->orderBy('created_at', 'desc')
                ->paginate(10)
        ]);
    }

    public function approve(User $user)
    {
        DB::transaction(function () use ($user) {
            // Update user status to active
            $user->update(['status' => 'active']);
            
            // Get all account types that should be auto-created
            $autoCreateAccountTypes = AccountType::where('auto_create', true)->where('status', 'active')->get();
            
            // Create member accounts for each auto-create account type
            foreach ($autoCreateAccountTypes as $accountType) {
                MemberAccount::create([
                    'account_number' => $this->generateAccountNumber($user->id, $accountType->id),
                    'account_type_id' => $accountType->id,
                    'user_id' => $user->id,
                    'balance' => 0.00,
                    'status' => 'active'
                ]);
            }
        });

        return redirect()->back()->with('success', 'User has been approved successfully and member accounts have been created.');
    }

    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'User has been rejected.');
    }

    /**
     * Generate a unique account number for the member account
     * Format: {user_id}{account_type_id}{timestamp_suffix}
     */
    private function generateAccountNumber($userId, $accountTypeId)
    {
        $timestamp = now()->format('ymdHis');
        return sprintf('%d%d%s', $userId, $accountTypeId, $timestamp);
    }
}