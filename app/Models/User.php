<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @method bool hasRole(string $role)
 * @method \Illuminate\Support\Collection getRoleNames()
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'unique_id',
        'phone_number',
        'date_of_birth', // Date of birth in 'Y-m-d' format
        'address', // Full address as a string
        'state', // State of residence
        'lga', // Local Government Area of residence
        'sex', // (e.g., male, female)
        'status', // 'pending_approval', 'active', 'inactive', 'rejected'
        'profile_picture', // URL or path to profile picture
        'email_verified_at', // Timestamp for email verification
        'bank_account_number', // Bank account number for transactions
        'bank_name', // Name of the bank for transactions
        'bank_branch', // Branch of the bank for transactions
        'bank_account_type', // Type of bank account (e.g., savings, current)
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relationship', // Relationship to next of kin (e.g., parent, sibling)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date:Y-m-d', // Cast date of birth to a date format
        ];
    }


    /**
     * Get the member accounts for the user.
     */
    public function memberAccounts(): HasMany
    {
        return $this->hasMany(MemberAccount::class, 'user_id', 'id');
    }

    /**
     * Get the loans applied for by the user (as borrower).
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'borrower_user_id', 'id');
    }

    /**
     * Get the loans guaranteed by the user.
     */
    public function guaranteedLoans(): HasMany
    {
        return $this->hasMany(Guarantor::class, 'user_id', 'id');
    }

    /**
     * Get the support tickets opened by the user.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'user_id', 'id');
    }

    /**
     * Get the messages sent by the user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_user_id', 'id');
    }

    /**
     * Get the messages received by the user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_user_id', 'id');
    }

    /**
     * Get the journal entries created by the user (admin).
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'created_by', 'id');
    }


    /**
     * Get the user's full name.
     *
     * @return string
     */
    protected function name(): Attribute
    {
        return Attribute::get(
            fn () => "{$this->first_name} {$this->last_name}"
        );
    }
}
