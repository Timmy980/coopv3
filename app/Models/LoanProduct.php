<?php
/**
 * LoanProduct Model
 *
 * Defines the terms and conditions for different loan products.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true; // Use auto-incrementing IDs
    protected $keyType = 'int';

    protected $fillable = [
        'product_name',
        'interest_rate',
        'repayment_period_months',
        'application_fee_amount',
        'application_fee_percentage',
        'required_guarantors',
        'max_loan_amount_percentage_of_savings',
        'max_loan_amount_fixed',
        'eligibility_criteria',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'application_fee_amount' => 'decimal:2',
        'application_fee_percentage' => 'decimal:2',
        'max_loan_amount_percentage_of_savings' => 'decimal:2',
        'max_loan_amount_fixed' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the loans associated with this loan product.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
