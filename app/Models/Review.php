<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reviewer_id',
        'reviewee_id',
        'rating',
        'text',
        'student_reply',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * Get the order that this review is for.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the reviewer (client who wrote the review).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the reviewee (student being reviewed).
     */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    /**
     * Check if the review can be edited (within 24 hours).
     */
    public function canBeEdited(): bool
    {
        return $this->created_at->diffInHours(now()) < 24;
    }

    /**
     * Check if the student has already replied.
     */
    public function hasStudentReply(): bool
    {
        return ! empty($this->student_reply);
    }
}
