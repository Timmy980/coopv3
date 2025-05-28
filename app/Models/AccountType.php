<?php
/**
 * AccountType Model
 *
 * Defines different types of member accounts with their specific rules.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountType extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true; // Use auto-incrementing IDs
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'description',
        'withdrawal_rules',
        'interest_rate',
    ];

    protected $casts = [
        'withdrawal_rules' => 'array', // Cast JSONB to array
        'interest_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the member accounts associated with this account type.
     */
    public function memberAccounts(): HasMany
    {
        return $this->hasMany(MemberAccount::class);
    }
}
