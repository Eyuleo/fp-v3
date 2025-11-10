<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'bio',
        'university',
        'phone',
        'avatar_path',
        'portfolio_paths',
        'stripe_connect_account_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'portfolio_paths'   => 'array',
        ];
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a student.
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is a client.
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path && Storage::disk('public')->exists($this->avatar_path)) {
            return asset('storage/' . $this->avatar_path);
        }

        // Return default avatar or initials-based avatar
        return $this->getDefaultAvatarUrl();
    }

    /**
     * Get default avatar URL (can be replaced with a service like UI Avatars).
     */
    protected function getDefaultAvatarUrl(): string
    {
        $initials = strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
        return "https://ui-avatars.com/api/?name={$initials}&size=200&background=3B82F6&color=fff";
    }

    /**
     * Get the user's initials for avatar fallback.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Get services offered by this student.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'student_id');
    }

    /**
     * Get orders where this user is the student (service provider).
     */
    public function ordersAsStudent(): HasMany
    {
        return $this->hasMany(Order::class, 'student_id');
    }

    /**
     * Get orders where this user is the client (buyer).
     */
    public function ordersAsClient(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get reviews written by this user (as client).
     */
    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get reviews received by this user (as student).
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    /**
     * Get the average rating for this student.
     */
    public function getAverageRatingAttribute(): float
    {
        try {
            return round($this->reviewsReceived()->avg('rating') ?? 0, 1);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get the average rating for this student (method version).
     */
    public function averageRating(): float
    {
        return $this->average_rating;
    }

    /**
     * Alias for reviewsGiven
     */
    public function reviews(): HasMany
    {
        return $this->reviewsGiven();
    }

    /**
     * Get the total number of reviews for this student.
     */
    public function getReviewCountAttribute(): int
    {
        try {
            return $this->reviewsReceived()->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if the student has completed Stripe Connect onboarding.
     */
    public function hasCompletedStripeOnboarding(): bool
    {
        if (empty($this->stripe_connect_account_id)) {
            return false;
        }

        // Check with Stripe API if onboarding is actually complete
        try {
            $stripe  = new \Stripe\StripeClient(config('stripe.secret'));
            $account = $stripe->accounts->retrieve($this->stripe_connect_account_id);

            // Check if details are submitted and capabilities are active
            $cardPaymentsActive = isset($account->capabilities->card_payments)
            && $account->capabilities->card_payments === 'active';
            $transfersActive = isset($account->capabilities->transfers)
            && $account->capabilities->transfers === 'active';

            return $account->details_submitted && $cardPaymentsActive && $transfersActive;
        } catch (\Exception $e) {
            // If we can't verify, assume not complete to show the banner
            return false;
        }
    }

    /**
     * Get disputes opened by this user.
     */
    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'opened_by_id');
    }
}
