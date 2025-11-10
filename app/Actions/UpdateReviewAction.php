<?php
namespace App\Actions;

use App\Models\Review;
use Illuminate\Support\Facades\DB;

class UpdateReviewAction
{
    /**
     * Execute the action to update a review.
     */
    public function execute(Review $review, array $data): Review
    {
        // Check if the review can still be edited (within 24 hours)
        if (! $review->canBeEdited()) {
            throw new \Exception('Review can only be edited within 24 hours of submission.');
        }

        return DB::transaction(function () use ($review, $data) {
            // Update the review
            $review->update([
                'rating' => $data['rating'],
                'text'   => $data['text'] ?? null,
            ]);

            // Recalculate student's average rating
            $this->recalculateStudentRating($review->reviewee);

            return $review->fresh();
        });
    }

    /**
     * Recalculate the student's average rating.
     */
    protected function recalculateStudentRating($student): void
    {
        // The average rating is calculated dynamically via the User model's accessor
        $student->average_rating;
    }
}
