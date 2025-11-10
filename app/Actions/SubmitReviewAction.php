<?php
namespace App\Actions;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewPostedNotification;
use Illuminate\Support\Facades\DB;

class SubmitReviewAction
{
    /**
     * Execute the action to submit a review.
     */
    public function execute(Order $order, User $reviewer, array $data): Review
    {
        return DB::transaction(function () use ($order, $reviewer, $data) {
            // Create the review
            $review = Review::create([
                'order_id'    => $order->id,
                'reviewer_id' => $reviewer->id,
                'reviewee_id' => $order->student_id,
                'rating'      => $data['rating'],
                'text'        => $data['text'] ?? null,
            ]);

            // Recalculate student's average rating (will be cached on User model)
            $this->recalculateStudentRating($order->student);

            // Send notification to the student
            $order->student->notify(new ReviewPostedNotification($review));

            return $review;
        });
    }

    /**
     * Recalculate the student's average rating.
     */
    protected function recalculateStudentRating(User $student): void
    {
        // The average rating is calculated dynamically via the User model's accessor
        // This method can be used to cache the rating if needed in the future
        // For now, we'll just trigger the calculation by accessing the attribute
        $student->average_rating;
    }
}
