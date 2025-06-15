<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Display a listing of the member's accounts.
     */
    public function index()
    {
        $accounts = MemberAccount::with(['accountType'])
            ->where('user_id', Auth::id())
            ->withCount(['savings', 'withdrawalRequests'])
            ->get();

        return Inertia::render('Member/Accounts/Index', [
            'accounts' => $accounts
        ]);
    }

    /**
     * Display the specified account.
     */
    public function show(MemberAccount $account)
    {
        // Ensure the account belongs to the authenticated user
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }

        $account->load([
            'accountType',
            'savings' => function ($query) {
                $query->latest()->limit(5);
            },
            'withdrawalRequests' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return Inertia::render('Member/Accounts/Show', [
            'account' => $account
        ]);
    }
} 