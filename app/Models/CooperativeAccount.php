<?php
/**
 * CooperativeAccount Model
 *
 * Represents the cooperative society's internal and external bank accounts.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CooperativeAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'account_name',
        'account_type',
        'bank_name',
        'account_number',
        'balance',
        'status', // 'active', 'inactive', 'suspended'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the savings transactions directed to this cooperative account.
     */
    public function savings(): HasMany
    {
        return $this->hasMany(Saving::class);
    }

    /**
     * Get the loans disbursed from this cooperative account.
     */
    public function disbursedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'coop_account_disbursement_id', 'id');
    }

    /**
     * Get the loan repayments received into this cooperative account.
     */
    public function receivedLoanRepayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class, 'coop_account_receipt_id', 'id');
    }

    /**
     * Get the withdrawal requests disbursed from this cooperative account.
     */
    public function disbursedWithdrawals(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'coop_account_disbursement_id', 'id');
    }

    /**
     * Get the debit journal entries associate with this cooperative account.
     */
    public function debitJournalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'debit_accountable');
    }

    /**
     * Get the credit journal entries associated with this cooperative account.
     */
    public function creditJournalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'credit_accountable');
    }
    
}
