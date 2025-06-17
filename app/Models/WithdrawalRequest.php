<?php

/**
 * WithdrawalRequest Model
 *
 * Manages member requests for withdrawing funds from their accounts.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'member_account_id',
        'requested_amount',
        'request_date',
        'status', // 'pending', 'approved', 'rejected', 'disbursed'
        'approval_date',
        'disbursement_date',
        'member_bank_account_for_receipt',
        'coop_account_disbursement_id',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'request_date' => 'datetime',
        'approval_date' => 'datetime',
        'disbursement_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the user who made the withdrawal request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the member account from which the withdrawal is requested.
     */
    public function memberAccount(): BelongsTo
    {
        return $this->belongsTo(MemberAccount::class);
    }

    /**
     * Get the cooperative account from which the funds were disbursed.
     */
    public function disbursementAccount(): BelongsTo
    {
        return $this->belongsTo(CooperativeAccount::class, 'coop_account_disbursement_id', 'id');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
