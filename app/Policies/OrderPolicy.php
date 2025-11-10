<?php
namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Admin can view all orders
        if ($user->isAdmin()) {
            return true;
        }

        // Participants (student or client) can view the order
        return $order->isParticipant($user);
    }

    /**
     * Determine if the user can accept the order.
     */
    public function accept(User $user, Order $order): bool
    {
        // Only the assigned student can accept
        if ($user->id !== $order->student_id) {
            return false;
        }

        // Order must be pending
        if (! $order->isPending()) {
            return false;
        }

        // Student must have completed Stripe Connect onboarding
        if (! $user->hasCompletedStripeOnboarding()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can decline the order.
     */
    public function decline(User $user, Order $order): bool
    {
        // Only the assigned student can decline
        if ($user->id !== $order->student_id) {
            return false;
        }

        // Order must be pending
        return $order->isPending();
    }

    /**
     * Determine if the user can deliver work for the order.
     */
    public function deliver(User $user, Order $order): bool
    {
        // Only the assigned student can deliver
        if ($user->id !== $order->student_id) {
            return false;
        }

        // Order must be in progress or revision requested
        return $order->isInProgress() || $order->isRevisionRequested();
    }

    /**
     * Determine if the user can request a revision.
     */
    public function requestRevision(User $user, Order $order): bool
    {
        // Only the client can request revisions
        if ($user->id !== $order->client_id) {
            return false;
        }

        // Order must be delivered
        if (! $order->isDelivered()) {
            return false;
        }

        // Check if revisions are still available
        return $order->canRequestRevision();
    }

    /**
     * Determine if the user can approve the order.
     */
    public function approve(User $user, Order $order): bool
    {
        // Only the client can approve
        if ($user->id !== $order->client_id) {
            return false;
        }

        // Order must be delivered
        return $order->isDelivered();
    }

    /**
     * Determine if the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Admin can cancel any order
        if ($user->isAdmin()) {
            return true;
        }

        // Cannot cancel completed orders
        if ($order->isCompleted()) {
            return false;
        }

        // Cannot cancel already cancelled orders
        if ($order->isCancelled()) {
            return false;
        }

        // Client can cancel pending orders
        if ($user->id === $order->client_id && $order->isPending()) {
            return true;
        }

        // Student can decline (cancel) pending orders
        if ($user->id === $order->student_id && $order->isPending()) {
            return true;
        }

        // In-progress orders require admin approval
        if ($order->isInProgress()) {
            return false;
        }

        return false;
    }

    /**
     * Determine if the user can open a dispute for the order.
     */
    public function openDispute(User $user, Order $order): bool
    {
        // Only participants can open disputes
        if (! $order->isParticipant($user)) {
            return false;
        }

        // Cannot dispute completed or cancelled orders
        if ($order->isCompleted() || $order->isCancelled()) {
            return false;
        }

        // Cannot open dispute if one already exists
        if ($order->dispute()->exists()) {
            return false;
        }

        return true;
    }
}
