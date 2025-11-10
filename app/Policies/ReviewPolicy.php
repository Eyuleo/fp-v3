<?php
namespace App\Policies;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    /**
     * Determine whether the user can create a review for an order.
     */
    public function create(User $user, Order $order): Response
    {
        // Only clients can create reviews
        if (! $user->isClient()) {
            return Response::deny('Only clients can write reviews.');
        }

        // Order must be completed
        if (! $order->isCompleted()) {
            return Response::deny('Reviews can only be written for completed orders.');
        }

        // User must be the client of the order
        if ($order->client_id !== $user->id) {
            return Response::deny('You can only review orders you placed.');
        }

        // Check if review already exists
        if ($order->review) {
            return Response::deny('You have already reviewed this order.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the review.
     */
    public function update(User $user, Review $review): Response
    {
        // Only the reviewer can update their review
        if ($review->reviewer_id !== $user->id) {
            return Response::deny('You can only edit your own reviews.');
        }

        // Check if within 24-hour edit window
        if (! $review->canBeEdited()) {
            return Response::deny('Reviews can only be edited within 24 hours of submission.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can reply to the review.
     */
    public function reply(User $user, Review $review): Response
    {
        // Only the reviewee (student) can reply
        if ($review->reviewee_id !== $user->id) {
            return Response::deny('Only the reviewed student can reply to this review.');
        }

        // Check if already replied
        if ($review->hasStudentReply()) {
            return Response::deny('You have already replied to this review.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can view the review.
     */
    public function view(User $user, Review $review): bool
    {
        // Reviews are public, anyone can view them
        return true;
    }

    /**
     * Determine whether the user can delete the review.
     */
    public function delete(User $user, Review $review): bool
    {
        // Only admins can delete reviews
        return $user->isAdmin();
    }
}
