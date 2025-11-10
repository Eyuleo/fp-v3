<?php
namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view the service listing
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Service $service): bool
    {
        // Public can view active services
        if ($service->is_active) {
            return true;
        }

        // Owner and admin can view inactive services
        if ($user) {
            return $user->id === $service->student_id || $user->isAdmin();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only verified students can create services
        return $user->isStudent() && $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Service $service): bool
    {
        // Owner or admin can update
        return $user->id === $service->student_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Service $service): bool
    {
        // Owner or admin can delete
        return $user->id === $service->student_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Service $service): bool
    {
        // Owner or admin can restore
        return $user->id === $service->student_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        // Only admin can force delete
        return $user->isAdmin();
    }
}
