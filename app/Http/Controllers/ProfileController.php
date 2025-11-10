<?php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request, ?int $id = null): View
    {
        // If no ID provided, show authenticated user's profile
        $user = $id ? \App\Models\User::findOrFail($id) : $request->user();

        // Load relationships for student profiles (only if tables exist)
        if ($user->isStudent()) {
            try {
                $user->load(['reviewsReceived' => function ($query) {
                    $query->latest()->limit(10);
                }]);
            } catch (\Exception $e) {
                // Reviews table doesn't exist yet, skip loading
            }
        }

        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Authorization check
        $this->authorize('update', $user);

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Authorization check
        $this->authorize('update', $user);

        $validated = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $avatarPath               = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = $avatarPath;
        }

        // Handle portfolio upload (for students)
        if ($request->hasFile('portfolio') && $user->isStudent()) {
            $portfolioFiles = $user->portfolio_paths ?? [];

            foreach ($request->file('portfolio') as $file) {
                $portfolioFiles[] = $file->store('portfolios', 'public');
            }

            $validated['portfolio_paths'] = $portfolioFiles;
        }

        // Update user profile
        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Authorization check
        $this->authorize('update', $user);

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Avatar deleted successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Authorization check
        $this->authorize('delete', $user);

        // Logout the user
        Auth::logout();

        // Delete avatar if exists
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Soft delete or hard delete the user
        $user->delete();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }

    /**
     * Delete a portfolio item.
     */
    public function deletePortfolioItem(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Authorization check
        $this->authorize('update', $user);

        $request->validate([
            'index' => ['required', 'integer', 'min:0'],
        ]);

        $portfolioPaths = $user->portfolio_paths ?? [];
        $index          = $request->input('index');

        if (isset($portfolioPaths[$index])) {
            // Delete the file from storage
            Storage::disk('public')->delete($portfolioPaths[$index]);

            // Remove from array
            array_splice($portfolioPaths, $index, 1);

            // Update user
            $user->update(['portfolio_paths' => $portfolioPaths]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Portfolio item deleted successfully.');
    }
}
