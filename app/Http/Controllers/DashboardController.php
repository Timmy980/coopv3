<?php

namespace App\Http\Controllers;

use App\Models\MemberAccount;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        return $this->memberDashboard();
    }

    private function adminDashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }

    private function memberDashboard()
    {
        $accounts = MemberAccount::with(['accountType'])
            ->where('user_id', Auth::id())
            ->withCount(['savings', 'withdrawalRequests'])
            ->get();

        return Inertia::render('Member/Dashboard', [
            'accounts' => $accounts
        ]);
    }
} 