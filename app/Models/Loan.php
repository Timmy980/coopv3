<?php
/**
 * Loan Model
 *
 * Manages individual loan applications, their status, and financial details.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'loan_product_id',
        'application_date',
        'requested_amount',
        'approved_amount',
        'disbursement_date',
        'status',
        'total_interest_due',
        'outstanding_principal',
        'outstanding_interest',
        'repayment_due_date',
        'coop_account_disbursement_id',
        'member_bank_account_for_receipt',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'total_interest_due' => 'decimal:2',
        'outstanding_principal' => 'decimal:2',
        'outstanding_interest' => 'decimal:2',
        'application_date' => 'datetime',
        'disbursement_date' => 'datetime',
        'repayment_due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the borrower(user) for the loan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the loan product associated with the loan.
     */
    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    /**
     * Get the guarantors for the loan.
     */
    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class);
    }

    /**
     * Get the loan repayments for the loan.
     */
    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * Get the cooperative account from which the loan was disbursed.
     */
    public function disbursementAccount(): BelongsTo
    {
        return $this->belongsTo(CooperativeAccount::class, 'coop_account_disbursement_id', 'id');
    }
}
