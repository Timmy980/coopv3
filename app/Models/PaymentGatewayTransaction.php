<?php
/**
 * PaymentGatewayTransaction Model
 *
 * Stores payment gateway transaction details
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentGatewayTransaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    public $incrementing = true; // Use auto-incrementing IDs
    protected $keyType = 'int';

    protected $fillable = [
        'transactionable_type', // Type of the transaction (e.g., 'Saving', 'LoanRepayment')
        'transactionable_id',
        'gateway_provider', // 'paystack', 'flutterwave', etc.
        'transaction_reference',
        'gateway_transaction_id',
        'amount',
        'currency',
        'gateway_fee',
        'status',
        'gateway_response',
        'callback_data',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'gateway_response' => 'json',
        'callback_data' => 'json',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the credit account associated with this journal entry.
     */
    public function transaction(): MorphTo
    {
        return $this->morphTo('transactionable');
    }
}