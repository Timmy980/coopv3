<?php

/**
 * Transaction Service
 * 
 * Handles transaction creation with automatic balance calculation
 */

use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\MemberAccount;
use App\Models\CooperativeAccount;

class TransactionService
{
    /**
     * Create a new transaction with automatic balance calculation
     */
    public function createTransaction(array $data)
    {
        DB::beginTransaction();

        try {
            // Get the current balance for the account
            $currentBalance = $this->getCurrentBalance($data['account_type'], $data['account_id']);

            // Calculate new balance
            $newBalance = $currentBalance + $data['amount'];

            // Create the transaction
            $transaction = Transaction::create([
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => $data['transaction_type'],
                'account_type' => $data['account_type'],
                'account_id' => $data['account_id'],
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'amount' => $data['amount'],
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'description' => $data['description'],
                'reference_number' => $data['reference_number'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            // Update the account's current balance (optional, for quick access)
            $this->updateAccountBalance($data['account_type'], $data['account_id'], $newBalance);

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create multiple transactions (for double-entry scenarios)
     */
    public function createDoubleEntry(array $debitTransaction, array $creditTransaction, $referenceData = null)
    {
        DB::beginTransaction();

        try {
            // Create debit transaction (negative amount)
            $debitTxn = $this->createTransaction(array_merge($debitTransaction, [
                'amount' => -abs($debitTransaction['amount'])
            ]));

            // Create credit transaction (positive amount)
            $creditTxn = $this->createTransaction(array_merge($creditTransaction, [
                'amount' => abs($creditTransaction['amount'])
            ]));

            DB::commit();
            return ['debit' => $debitTxn, 'credit' => $creditTxn];
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get current balance for an account
     */
    private function getCurrentBalance($accountType, $accountId)
    {
        $lastTransaction = Transaction::forAccount($accountType, $accountId)
            ->where('is_reversed', false)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->balance_after : 0;
    }

    /**
     * Update account's current balance (for quick access without querying transactions)
     */
    private function updateAccountBalance($accountType, $accountId, $newBalance)
    {
        if ($accountType === 'member_account') {
            MemberAccount::where('id', $accountId)
                ->update(['balance' => $newBalance]);
        } else {
            CooperativeAccount::where('id', $accountId)
                ->update(['balance' => $newBalance]);
        }
    }

    /**
     * Reverse a transaction
     */
    public function reverseTransaction($transactionId, $reason, $userId)
    {
        DB::beginTransaction();

        try {
            $originalTransaction = Transaction::findOrFail($transactionId);

            if ($originalTransaction->is_reversed) {
                throw new \Exception('Transaction already reversed');
            }

            // Create reversal transaction
            $reversalTransaction = $this->createTransaction([
                'transaction_date' => now(),
                'transaction_type' => 'reversal',
                'account_type' => $originalTransaction->account_type,
                'account_id' => $originalTransaction->account_id,
                'amount' => -$originalTransaction->amount, // Opposite amount
                'description' => "Reversal: {$reason}",
                'reference_number' => "REV-{$originalTransaction->reference_number}",
                'created_by' => $userId,
                'reversal_of_transaction_id' => $transactionId,
            ]);

            // Mark original as reversed
            $originalTransaction->update(['is_reversed' => true]);

            DB::commit();
            return $reversalTransaction;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
