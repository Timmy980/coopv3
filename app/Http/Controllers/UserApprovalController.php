<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
        $user->update(['status' => 'active']);
        return redirect()->back()->with('success', 'User has been approved successfully.');
    }

    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'User has been rejected.');
    }
}
