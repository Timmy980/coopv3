<?php

/**
 * Transaction Model
 * 
 * Unified transaction table that records all financial movements
 * This provides a single source of truth for all account activities
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'transaction_date',
        'transaction_type', // 'saving', 'withdrawal', 'loan_disbursement', 'loan_repayment', 'interest_accrual', 'fee', 'journal_entry'
        'reference_type', // 'saving', 'loan', 'withdrawal_request', 'journal_entry'
        'reference_id', // ID of the related record
        'account_type', // 'member_account', 'coop_account'
        'account_id',
        'amount',
        'balance_before', // Account balance before this transaction
        'balance_after', // Account balance after this transaction
        'description',
        'reference_number',
        'created_by',
        'reversal_of_transaction_id', // For reversals
        'is_reversed',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'is_reversed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the user who created this transaction
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the account (member or cooperative)
     */
    public function account()
    {
        if ($this->debit_account_type === 'member_account') {
            return $this->belongsTo(MemberAccount::class, 'account_id', 'id');
        }
        return $this->belongsTo(CooperativeAccount::class, 'account_id', 'id');
    }


    /**
     * Get the original record this transaction references
     */
    public function referencedRecord()
    {
        switch ($this->reference_type) {
            case 'saving':
                return $this->belongsTo(Saving::class, 'reference_id', 'id');
            case 'loan':
                return $this->belongsTo(Loan::class, 'reference_id', 'id');
            case 'withdrawal_request':
                return $this->belongsTo(WithdrawalRequest::class, 'reference_id', 'id');
            case 'journal_entry':
                return $this->belongsTo(JournalEntry::class, 'reference_id', 'id');
            default:
                return null;
        }
    }

     /**
     * Scope to get transactions for a specific account
     */
    public function scopeForAccount($query, $accountType, $accountId)
    {
        return $query->where('account_type', $accountType)
                    ->where('account_id', $accountId);
    }

    /**
     * Scope to get transactions within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Check if this is a credit transaction (positive amount)
     */
    public function isCredit()
    {
        return $this->amount > 0;
    }

    /**
     * Check if this is a debit transaction (negative amount)
     */
    public function isDebit()
    {
        return $this->amount < 0;
    }

    /**
     * Get absolute amount for display
     */
    public function getAbsoluteAmountAttribute()
    {
        return abs($this->amount);
    }
}