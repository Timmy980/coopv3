<?php

/**
 * Account Statement Service - Simplified with running balances
 */

use App\Models\Transaction;
use App\Models\User;

class AccountStatementService
{
    /**
     * Generate statement for any account
     */
    public function generateStatement($accountType, $accountId, $startDate, $endDate)
    {
        // Get opening balance (balance_after from last transaction before start date)
        $openingBalance = $this->getOpeningBalance($accountType, $accountId, $startDate);

        // Get all transactions in the period - balance is already calculated!
        $transactions = Transaction::forAccount($accountType, $accountId)
            ->inDateRange($startDate, $endDate)
            ->where('is_reversed', false)
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get();

        // Transform for display
        $statementLines = $transactions->map(function ($transaction) {
            return [
                'date' => $transaction->transaction_date,
                'description' => $transaction->description,
                'reference' => $transaction->reference_number,
                'debit' => $transaction->isDebit() ? $transaction->getAbsoluteAmountAttribute() : null,
                'credit' => $transaction->isCredit() ? $transaction->getAbsoluteAmountAttribute() : null,
                'balance' => $transaction->balance_after,
                'transaction_id' => $transaction->id,
            ];
        });

        $closingBalance = $transactions->last()->balance_after ?? $openingBalance;

        return [
            'account_type' => $accountType,
            'account_id' => $accountId,
            'period' => ['start' => $startDate, 'end' => $endDate],
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'transactions' => $statementLines,
            'total_debits' => $statementLines->whereNotNull('debit')->sum('debit'),
            'total_credits' => $statementLines->whereNotNull('credit')->sum('credit'),
        ];
    }

    /**
     * Get opening balance
     */
    private function getOpeningBalance($accountType, $accountId, $asOfDate)
    {
        $lastTransactionBeforePeriod = Transaction::forAccount($accountType, $accountId)
            ->where('transaction_date', '<', $asOfDate)
            ->where('is_reversed', false)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastTransactionBeforePeriod ? $lastTransactionBeforePeriod->balance_after : 0;
    }

    /**
     * Get current balance for any account
     */
    public function getCurrentBalance($accountType, $accountId)
    {
        $lastTransaction = Transaction::forAccount($accountType, $accountId)
            ->where('is_reversed', false)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->balance_after : 0;
    }

    /**
     * Generate user's complete financial statement
     */
    public function generateUserStatement($userId, $startDate, $endDate)
    {
        $user = User::with('memberAccounts')->find($userId);
        $statements = collect();

        // Generate statements for all user's member accounts
        foreach ($user->memberAccounts as $account) {
            $statements->push([
                'account_type' => 'savings',
                'account' => $account,
                'statement' => $this->generateStatement('member_account', $account->id, $startDate, $endDate)
            ]);
        }

        return $statements;
    }
}
