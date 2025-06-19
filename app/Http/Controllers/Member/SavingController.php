<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Saving;
use App\Models\MemberAccount;
use App\Models\CooperativeAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SavingController extends Controller
{
    /**
     * Display a listing of the member's savings.
     */
    public function index(Request $request)
    {
        $query = Saving::with(['memberAccount', 'cooperativeAccount']);

        // Only show member's savings
        $query->whereHas('memberAccount', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        $savings = $query->latest('transaction_date')->paginate(15);

        return Inertia::render('Member/Savings/Index', [
            'savings' => $savings,
            'filters' => $request->only(['status', 'source', 'date_from', 'date_to']),
            'memberAccounts' => MemberAccount::where('user_id', Auth::id())->with('accountType')->get(),
            'cooperativeAccounts' => CooperativeAccount::active()->get(),
        ]);
    }

    /**
     * Display a specific saving
     */
    public function show(Saving $saving)
    {
        if ($saving->memberAccount->user_id !== Auth::id()) {
            abort(403);
        }

        $saving->load(['memberAccount', 'cooperativeAccount', 'initiatedBy', 'approvedBy', 'rejectedBy']);

        return Inertia::render('Member/Savings/Show', [
            'saving' => $saving
        ]);
    }

    /**
     * Store a new bank deposit saving.
     */
    public function storeDeposit(Request $request)
    {
        $validated = $request->validate([
            'member_account_id' => 'required|exists:member_accounts,id',
            'cooperative_account_id' => 'required|exists:cooperative_accounts,id',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Ensure member account belongs to authenticated user
        $memberAccount = MemberAccount::where('id', $validated['member_account_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Store payment proof
        $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

        // Create saving record
        $saving = Saving::create([
            'member_account_id' => $validated['member_account_id'],
            'cooperative_account_id' => $validated['cooperative_account_id'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'reference_number' => $validated['reference_number'],
            'payment_proof_path' => $proofPath,
            'notes' => $validated['notes'],
            'status' => Saving::STATUS_PENDING,
            'source' => Saving::SOURCE_MEMBER_DEPOSIT,
            'initiated_by_id' => Auth::id(),
        ]);

        return redirect()->route('member.savings.index')
            ->with('success', 'Bank deposit submitted successfully. Awaiting approval.');
    }

    /**
     * Update payment proof for a pending bank deposit.
     */
    public function updateProof(Request $request, Saving $saving)
    {
        // Ensure saving belongs to authenticated user and is a pending bank deposit
        if (
            $saving->memberAccount->user_id !== Auth::id() ||
            $saving->source !== Saving::SOURCE_MEMBER_DEPOSIT ||
            $saving->status !== Saving::STATUS_PENDING
        ) {
            abort(403);
        }

        $request->validate([
            'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Delete old proof if exists
        if ($saving->payment_proof_path) {
            Storage::disk('public')->delete($saving->payment_proof_path);
        }

        // Store new proof
        $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        $saving->update(['payment_proof_path' => $proofPath]);

        return redirect()->route('member.savings.index')
            ->with('success', 'Payment proof updated successfully.');
    }
}
