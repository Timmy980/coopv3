<?php

/**
 * JournalEntry Model
 *
 * Represents a single manual double-entry transaction in the general journal.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'description',
        'debit_accountable_type', // Type of the debit account (e.g., 'user', 'cooperative')
        'debit_accountable_id',
        'credit_accountable_type', // Type of the credit account (e.g., 'user', 'cooperative')
        'credit_accountable_id',
        'amount',
        'transaction_date',
        'created_by', // ID of the user who created the entry
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user (admin) who created the journal entry.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the debit account associated with this journal entry.
     */
    public function debitAccount(): MorphTo
    {
        return $this->morphTo('debit_accountable');
    }

    /**
     * Get the credit account associated with this journal entry.
     */
    public function creditAccount(): MorphTo
    {
        return $this->morphTo('credit_accountable');
    }
}
