<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any profiles.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the profile.
     */
    public function view(User $user, User $profile): bool
    {
        // Anyone can view any profile
        return true;
    }

    /**
     * Determine if the user can update the profile.
     */
    public function update(User $user, User $profile): bool
    {
        // Users can update their own profile, or admins can update any profile
        return $user->id === $profile->id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the profile.
     */
    public function delete(User $user, User $profile): bool
    {
        // Users can delete their own account, or admins can delete any profile (except their own)
        return $user->id === $profile->id || ($user->isAdmin() && $user->id !== $profile->id);
    }

    /**
     * Determine if the user can suspend the profile.
     */
    public function suspend(User $user, User $profile): bool
    {
        // Only admins can suspend users, and they cannot suspend themselves
        return $user->isAdmin() && $user->id !== $profile->id;
    }

    /**
     * Determine if the user can reinstate the profile.
     */
    public function reinstate(User $user, User $profile): bool
    {
        // Only admins can reinstate users
        return $user->isAdmin();
    }
}
