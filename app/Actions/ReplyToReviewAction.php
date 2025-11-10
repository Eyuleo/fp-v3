<?php
namespace App\Actions;

use App\Models\Review;

class ReplyToReviewAction
{
    /**
     * Execute the action to reply to a review.
     */
    public function execute(Review $review, string $reply): Review
    {
        // Check if the student has already replied
        if ($review->hasStudentReply()) {
            throw new \Exception('You have already replied to this review.');
        }

        // Update the review with the student's reply
        $review->update([
            'student_reply' => $reply,
        ]);

        return $review->fresh();
    }
}
