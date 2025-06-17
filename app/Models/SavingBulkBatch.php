<?php
/**
 * SavingBulkBatch Model
 *
 * Groups savings entries that were created in bulk operations
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingBulkBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_reference',
        'total_records',
        'processed_records',
        'failed_records',
        'total_amount',
        'status', // 'processing', 'completed', 'failed'
        'created_by_id',
        'account_type_id',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'failed_records' => 'integer',
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function savings(): HasMany
    {
        return $this->hasMany(Saving::class, 'bulk_batch_id');
    }
}