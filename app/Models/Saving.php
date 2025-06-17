<?php
/**
 * Saving Model
 *
 * Records individual savings transactions made by members.
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Saving extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FAILED = 'failed';

    // Source constants
    const SOURCE_MEMBER_GATEWAY = 'member_gateway';      // Member via payment gateway
    const SOURCE_MEMBER_DEPOSIT = 'member_deposit';      // Member bank deposit
    const SOURCE_ADMIN_SINGLE = 'admin_single';          // Admin single entry
    const SOURCE_ADMIN_BULK = 'admin_bulk';              // Admin bulk entry

    protected $fillable = [
        'amount',
        'transaction_date',
        'status', // 'pending', 'approved', 'failed'
        'source', // 'member', 'admin'
        'reference_number',
        'member_account_id',
        'cooperative_account_id',
        'initiated_by_id',
        'approved_by_id',
        'rejected_by_id',
        'bulk_batch_id',
        'payment_gateway_transaction_id',
        'payment_gateway_reference',
        'payment_proof_path',
        'notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the member account associated with this saving.
     */
    public function memberAccount(): BelongsTo
    {
        return $this->belongsTo(MemberAccount::class);
    }

    /**
     * Get the cooperative account associated with this saving.
     */
    public function cooperativeAccount(): BelongsTo
    {
        return $this->belongsTo(CooperativeAccount::class);
    }

    /**
     * Get the user who initiated this saving.
     */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }

    /**
     * Get the user who approved this saving.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * Get the user who rejected this saving.
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    /**
     * Get the payment bulk upload batch this saving belongs to.
     */
    public function bulkBatch(): BelongsTo
    {
        return $this->belongsTo(SavingBulkBatch::class, 'bulk_batch_id');
    }

    /**
     * Get the payment gateway transaction associated with this saving.
     */
    public function paymentGatewayTransaction(): MorphMany
    {
        return $this->morphMany(PaymentGatewayTransaction::class, 'transactionable');
    }
    

    // Helper methods
    public function requiresApproval(): bool
    {
        return in_array($this->source, [
            self::SOURCE_MEMBER_DEPOSIT,
            self::SOURCE_ADMIN_SINGLE,
            self::SOURCE_ADMIN_BULK
        ]);
    }

    public function canBeApprovedBy(User $user): bool
    {
        // Admin entries need approval from different admin
        if (in_array($this->source, [self::SOURCE_ADMIN_SINGLE, self::SOURCE_ADMIN_BULK])) {
            return $user->id !== $this->initiated_by_id && $user->hasRole('admin');
        }
        
        // Member deposits need admin approval
        if ($this->source === self::SOURCE_MEMBER_DEPOSIT) {
            return $user->hasRole('admin');
        }
        
        return false;
    }

    public function isGatewayTransaction(): bool
    {
        return $this->source === self::SOURCE_MEMBER_GATEWAY;
    }

    public function isBulkEntry(): bool
    {
        return $this->source === self::SOURCE_ADMIN_BULK;
    }
}
