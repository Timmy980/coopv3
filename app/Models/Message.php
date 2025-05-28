<?php
/**
 * Message Model
 *
 * Handles announcements, individual messages, and support ticket responses.
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'sender_user_id',
        'recipient_user_id',
        'message_type', // 'announcement', 'individual', 'support_response'
        'subject',
        'content',
        'is_read',
        'support_ticket_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];


    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id', 'id');
    }

    /**
     * Get the recipient of the message.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id', 'id');
    }

    /**
     * Get the support ticket this message is a response to (if applicable).
     */
    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }
}