<?php
/**
 * MemberAccount Model
 *
 * Represents an individual member's account within the cooperative.
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'account_number',
        'account_type_id',
        'user_id',
        'balance',
        'status', // 'active', 'inactive', 'suspended'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the account type that the member account belongs to.
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the user that owns the member account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the savings transactions for the member account.
     */
    public function savings(): HasMany
    {
        return $this->hasMany(Saving::class);
    }

    /**
     * Get the withdrawal requests from the member account.
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Get the debit journal entries associated with the member account.
     */
    public function debitJournalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'debit_accountable');
    }

    /**
     * Get the credit journal entries associated with the member account.
     */
    public function creditJournalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'credit_accountable');
    }
}