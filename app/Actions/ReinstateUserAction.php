<?php
namespace App\Actions;

use App\Models\User;
use App\Notifications\UserReinstatedNotification;
use Illuminate\Support\Facades\DB;

class ReinstateUserAction
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Set user as active
            $user->update(['is_active' => true]);

            // Note: Services remain inactive - user must manually reactivate them
            // This gives the user control over which services to republish

            // Send notification to user
            $user->notify(new UserReinstatedNotification());
        });
    }
}
