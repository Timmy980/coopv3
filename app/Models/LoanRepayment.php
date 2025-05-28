<?php
/**
 * LoanRepayment Model
 *
 * Tracks individual repayments made towards a loan.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanRepayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'loan_id',
        'amount',
        'principal_paid',
        'interest_paid',
        'repayment_date',
        'source',
        'coop_account_receipt_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'interest_paid' => 'decimal:2',
        'repayment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the loan associated with this repayment.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the cooperative account where the repayment was received.
     */
    public function receiptAccount(): BelongsTo
    {
        return $this->belongsTo(CooperativeAccount::class, 'coop_account_receipt_id', 'id');
    }
}
