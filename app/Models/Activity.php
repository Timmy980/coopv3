<?php
/**
 * Activity Model
 *
 * Tracks significant actions performed by users in the application.
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = true; // Use auto-incrementing IDs
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'action', // e.g., 'create', 'update', 'delete', 'view'
        'description',
        'model_type',
        'model_id',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_data' => 'array', // Cast JSONB to array
        'new_data' => 'array', // Cast JSONB to array
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that the activity is associated with.
     */
    public function model(): MorphTo
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }
}