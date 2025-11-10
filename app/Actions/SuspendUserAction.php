<?php
namespace App\Actions;

use App\Models\User;
use App\Notifications\UserSuspendedNotification;
use Illuminate\Support\Facades\DB;

class SuspendUserAction
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Set user as inactive
            $user->update(['is_active' => false]);

            // Revoke all sessions for this user
            DB::table('sessions')->where('user_id', $user->id)->delete();

            // Hide all active service listings if user is a student
            if ($user->isStudent()) {
                $user->services()->update(['is_active' => false]);
            }

            // Note: Payouts are held automatically by checking is_active in payment processing

            // Send notification to user
            $user->notify(new UserSuspendedNotification());
        });
    }
}
