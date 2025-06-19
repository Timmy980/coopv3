<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Saving;
use App\Models\MemberAccount;
use App\Models\PaymentGatewayTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingGatewayController extends Controller
{
    /**
     * Initiate a payment gateway transaction.
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'member_account_id' => 'required|exists:member_accounts,id',
            'cooperative_account_id' => 'required|exists:cooperative_accounts,id',
            'amount' => 'required|numeric|min:0',
        ]);

        // Ensure member account belongs to authenticated user
        $memberAccount = MemberAccount::where('id', $validated['member_account_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Create saving record
            $saving = Saving::create([
                'member_account_id' => $validated['member_account_id'],
                'cooperative_account_id' => $validated['cooperative_account_id'],
                'amount' => $validated['amount'],
                'transaction_date' => now(),
                'status' => Saving::STATUS_PENDING,
                'source' => Saving::SOURCE_MEMBER_GATEWAY,
                'initiated_by_id' => Auth::id(),
            ]);

            // Create payment gateway transaction
            $gatewayTransaction = PaymentGatewayTransaction::create([
                'transactionable_type' => Saving::class,
                'transactionable_id' => $saving->id,
                'gateway_provider' => config('services.payment.default_gateway'),
                'amount' => $validated['amount'],
                'currency' => 'NGN',
                'status' => 'pending',
            ]);

            // Initialize payment with gateway
            $paymentProvider = app(config('services.payment.provider_class'));
            $paymentData = $paymentProvider->initialize([
                'amount' => $validated['amount'] * 100, // Convert to kobo
                'email' => Auth::user()->email,
                'reference' => $gatewayTransaction->transaction_reference,
                'callback_url' => route('savings.gateway.verify', $gatewayTransaction->id),
                'metadata' => [
                    'saving_id' => $saving->id,
                    'member_account' => $memberAccount->account_number,
                ]
            ]);

            // Update gateway transaction with provider response
            $gatewayTransaction->update([
                'gateway_transaction_id' => $paymentData['transaction_id'] ?? null,
                'gateway_response' => $paymentData,
            ]);

            DB::commit();

            return response()->json([
                'authorization_url' => $paymentData['authorization_url'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Verify a payment gateway transaction.
     */
    public function verify(Request $request, PaymentGatewayTransaction $transaction)
    {
        // Ensure transaction belongs to authenticated user
        if ($transaction->transactionable->memberAccount->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Verify payment with gateway
            $paymentProvider = app(config('services.payment.provider_class'));
            $verificationData = $paymentProvider->verify($transaction->transaction_reference);

            // Update gateway transaction
            $transaction->update([
                'status' => $verificationData['status'],
                'gateway_fee' => $verificationData['fee'] / 100, // Convert from kobo
                'verified_at' => now(),
                'callback_data' => $request->all(),
            ]);

            // Update saving status
            $saving = $transaction->transactionable;
            if ($verificationData['status'] === 'success') {
                $saving->update([
                    'status' => Saving::STATUS_APPROVED,
                    'approved_at' => now(),
                    'payment_gateway_reference' => $transaction->transaction_reference,
                ]);
            } else {
                $saving->update([
                    'status' => Saving::STATUS_FAILED,
                ]);
            }

            DB::commit();

            return redirect()->route('member.savings.index')
                ->with('success', 'Payment processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Update saving status to failed
            $transaction->transactionable->update([
                'status' => Saving::STATUS_FAILED,
            ]);

            return redirect()->route('member.savings.index')
                ->with('error', 'Payment verification failed. Please try again.');
        }
    }
} 