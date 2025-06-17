<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\MemberAccount;
use App\Models\PaymentGatewayTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Models\CooperativeAccount;

class SavingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of savings
     */
    public function index(Request $request)
    {
        $query = Saving::with(['memberAccount', 'cooperativeAccount']);

        // If user is a member, only show their savings
        if ($request->user()->hasRole('member')) {
            $query->whereHas('memberAccount', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }
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

        if ($request->user()->hasRole('member')) {
            return Inertia::render('Member/Savings/Index', [
                'savings' => $savings,
                'filters' => $request->only(['status', 'source', 'date_from', 'date_to']),
                'memberAccounts' => MemberAccount::where('user_id', Auth::id())->with('accountType')->get(),
                'cooperativeAccounts' => CooperativeAccount::active()->get(),
            ]);
        } else {
            return Inertia::render('Admin/Savings/Index', [
                'savings' => $savings,
                'filters' => $request->only(['status', 'source', 'date_from', 'date_to']),
                'memberAccounts' => MemberAccount::with(['accountType', 'user'])->get(),
                'cooperativeAccounts' => CooperativeAccount::active()->get(),
            ]);
        }
    }

    /**
     * Display a specific saving
     */
    public function show(Saving $saving)
    {
        if (request()->user()->hasRole('member')) {
            if ($saving->memberAccount->user_id !== Auth::id()) {
                abort(403);
            }
    
            $saving->load(['memberAccount', 'cooperativeAccount', 'initiatedBy', 'approvedBy', 'rejectedBy']);
    
            return Inertia::render('Member/Savings/Show', [
                'saving' => $saving
            ]);
        }
        if (!request()->user()->hasPermissionTo('view savings')) {
            abort(403);
        }

        $saving = Saving::with([
            'memberAccount',
            'memberAccount.user',
            'memberAccount.accountType',
            'cooperativeAccount',
            'initiatedBy',
            'approvedBy',
            'rejectedBy',
            'paymentGatewayTransaction'
        ])->find($saving->id);

        return Inertia::render('Admin/Savings/Show', [
            'saving' => $saving
        ]);
    }

    /**
     * Initiate a payment gateway transaction
     */
    public function initiateGatewayPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'member_account_id' => 'required|exists:member_accounts,id',
            'cooperative_account_id' => 'required|exists:cooperative_accounts,id'
        ]);

        // Verify member account belongs to user
        $memberAccount = MemberAccount::where('id', $validated['member_account_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Create saving record
            $saving = Saving::create([
                'amount' => $validated['amount'],
                'transaction_date' => now(),
                'status' => Saving::STATUS_PENDING,
                'source' => Saving::SOURCE_MEMBER_GATEWAY,
                'member_account_id' => $validated['member_account_id'],
                'cooperative_account_id' => $validated['cooperative_account_id'],
                'initiated_by_id' => $request->user()->id,
            ]);

            // Initialize payment gateway transaction
            // Note: Implementation depends on your payment gateway
            $gatewayTransaction = new PaymentGatewayTransaction([
                'amount' => $validated['amount'],
                'currency' => 'NGN', // Adjust based on your currency
                'status' => 'pending',
                'gateway_provider' => 'paystack', // Adjust based on your provider
            ]);

            $saving->paymentGatewayTransaction()->save($gatewayTransaction);

            DB::commit();

            // Return payment gateway initialization data
            return response()->json([
                'authorization_url' => 'YOUR_PAYMENT_GATEWAY_URL', // Implement based on your gateway
                'reference' => $gatewayTransaction->transaction_reference
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle payment gateway callback
     */
    public function handleGatewayCallback(Request $request)
    {
        // Verify the transaction with payment gateway
        // Implementation depends on your payment gateway

        $transaction = PaymentGatewayTransaction::where('transaction_reference', $request->reference)
            ->firstOrFail();

        $saving = $transaction->transaction;

        if ($request->status === 'success') {
            $saving->update([
                'status' => Saving::STATUS_APPROVED,
                'approved_at' => now()
            ]);

            $transaction->update([
                'status' => 'success',
                'verified_at' => now(),
                'gateway_response' => $request->all()
            ]);
        } else {
            $saving->update([
                'status' => Saving::STATUS_FAILED
            ]);

            $transaction->update([
                'status' => 'failed',
                'gateway_response' => $request->all()
            ]);
        }

        return redirect()->route('savings.show', $saving->id);
    }

    /**
     * Store a bank deposit saving
     */
    public function storeBankDeposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date|before_or_equal:today',
            'member_account_id' => 'required|exists:member_accounts,id',
            'cooperative_account_id' => 'required|exists:cooperative_accounts,id',
            'reference_number' => 'required|string',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string'
        ]);

        // Verify member account belongs to user
        $memberAccount = MemberAccount::where('id', $validated['member_account_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Store payment proof
        $paymentProofPath = $request->file('payment_proof')->store('payment_proofs');

        // Create saving record
        $saving = Saving::create([
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'status' => Saving::STATUS_PENDING,
            'source' => Saving::SOURCE_MEMBER_DEPOSIT,
            'reference_number' => $validated['reference_number'],
            'member_account_id' => $validated['member_account_id'],
            'cooperative_account_id' => $validated['cooperative_account_id'],
            'initiated_by_id' => $request->user()->id,
            'payment_proof_path' => $paymentProofPath,
            'notes' => $validated['notes']
        ]);

        return redirect()->route('savings.show', $saving->id);
    }

    /**
     * Upload proof for an existing saving
     */
    public function uploadProof(Request $request, Saving $saving)
    {
        $this->authorize('uploadProof', $saving);

        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        // Delete old proof if exists
        if ($saving->payment_proof_path) {
            Storage::delete($saving->payment_proof_path);
        }

        // Store new proof
        $paymentProofPath = $request->file('payment_proof')->store('payment_proofs');

        $saving->update([
            'payment_proof_path' => $paymentProofPath
        ]);

        return back();
    }

    /**
     * Store a single saving entry by admin
     */
    public function storeSingleEntry(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date|before_or_equal:today',
            'member_account_id' => 'required|exists:member_accounts,id',
            'cooperative_account_id' => 'required|exists:cooperative_accounts,id',
            'reference_number' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $saving = Saving::create([
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'status' => Saving::STATUS_PENDING,
            'source' => Saving::SOURCE_ADMIN_SINGLE,
            'reference_number' => $validated['reference_number'],
            'member_account_id' => $validated['member_account_id'],
            'cooperative_account_id' => $validated['cooperative_account_id'],
            'initiated_by_id' => $request->user()->id,
            'notes' => $validated['notes']
        ]);

        return redirect()->route('savings.show', $saving->id);
    }

    /**
     * Approve a saving
     */
    public function approve(Request $request, Saving $saving)
    {
        if (!$saving->canBeApprovedBy($request->user())) {
            throw ValidationException::withMessages([
                'saving' => ['You are not authorized to approve this saving.']
            ]);
        }

        $saving->update([
            'status' => Saving::STATUS_APPROVED,
            'approved_by_id' => $request->user()->id,
            'approved_at' => now()
        ]);

        $saving->memberAccount->update([
            'balance' => $saving->memberAccount->balance + $saving->amount
        ]);

        return back()->with('success', 'Saving has been approved successfully.');
    }

    /**
     * Reject a saving
     */
    public function reject(Request $request, Saving $saving)
    {
        if (!$saving->canBeApprovedBy($request->user())) {
            throw ValidationException::withMessages([
                'saving' => ['You are not authorized to reject this saving.']
            ]);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $saving->update([
            'status' => Saving::STATUS_REJECTED,
            'rejected_by_id' => $request->user()->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason']
        ]);

        return back()->with('success', 'Saving has been rejected successfully.');
    }

    /**
     * Display savings pending approval
     */
    public function pendingApproval()
    {
        $savings = Saving::with(['memberAccount', 'cooperativeAccount', 'initiatedBy'])
            ->where('status', Saving::STATUS_PENDING)
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Savings/PendingApproval', [
            'savings' => $savings
        ]);
    }

    /**
     * Display savings reports
     */
    public function reports(Request $request)
    {
        $query = Saving::with(['memberAccount', 'cooperativeAccount']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $savings = $query->latest()->paginate(15);

        return Inertia::render('Admin/Savings/Reports', [
            'savings' => $savings,
            'filters' => $request->all()
        ]);
    }
}
