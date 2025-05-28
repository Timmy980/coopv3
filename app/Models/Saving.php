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

class Saving extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'amount',
        'transaction_date',
        'status', // 'pending', 'approved', 'failed'
        'source', // 'member', 'admin'
        'reference_number',
        'member_account_id',
        'cooperative_account_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function memberAccount(): BelongsTo
    {
        return $this->belongsTo(MemberAccount::class);
    }

    public function cooperativeAccount(): BelongsTo
    {
        return $this->belongsTo(CooperativeAccount::class);
    }
}
