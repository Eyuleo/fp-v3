<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Determine the correct dashboard route based on user role
        $dashboardRoute = match ($user->role) {
            'admin'   => 'admin.dashboard',
            'student' => 'student.dashboard',
            'client'  => 'client.dashboard',
            default   => 'client.dashboard',
        };

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route($dashboardRoute, absolute: false) . '?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route($dashboardRoute, absolute: false) . '?verified=1');
    }
}
