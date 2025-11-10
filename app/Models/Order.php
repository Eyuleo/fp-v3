<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_PENDING            = 'pending';
    public const STATUS_IN_PROGRESS        = 'in_progress';
    public const STATUS_DELIVERED          = 'delivered';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';
    public const STATUS_COMPLETED          = 'completed';
    public const STATUS_CANCELLED          = 'cancelled';

    // Maximum number of revisions allowed
    public const MAX_REVISIONS = 2;

    protected $fillable = [
        'service_id',
        'student_id',
        'client_id',
        'price',
        'commission',
        'requirements',
        'delivery_date',
        'status',
        'revision_count',
        'cancelled_reason',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'commission'     => 'decimal:2',
            'delivery_date'  => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    /**
     * Get the service that this order is for.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the student (service provider) for this order.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the client (buyer) for this order.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get messages for this order.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the payment for this order.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the review for this order.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Get the dispute for this order.
     */
    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    /**
     * Check if the order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the order is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if the order is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if the order has a revision requested.
     */
    public function isRevisionRequested(): bool
    {
        return $this->status === self::STATUS_REVISION_REQUESTED;
    }

    /**
     * Check if the order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the order is late (past delivery date).
     */
    public function isLate(): bool
    {
        return $this->isInProgress()
        && $this->delivery_date
        && now()->isAfter($this->delivery_date);
    }

    /**
     * Check if more revisions can be requested.
     */
    public function canRequestRevision(): bool
    {
        return $this->isDelivered() && $this->revision_count < self::MAX_REVISIONS;
    }

    /**
     * Get the net amount (price minus commission) for the student.
     */
    public function getNetAmountAttribute(): float
    {
        return (float) ($this->price - $this->commission);
    }

    /**
     * Check if a user is a participant in this order.
     */
    public function isParticipant(User $user): bool
    {
        return $this->student_id === $user->id || $this->client_id === $user->id;
    }
}
