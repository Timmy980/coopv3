<?php
/**
 * Guarantor Model
 *
 * Links a user (guarantor) to a specific loan application.
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guarantor extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'loan_id',
        'user_id',
        'status', // 'pending', 'accepted', 'rejected'
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the loan that the guarantor is associated with.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the user who is acting as the guarantor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
