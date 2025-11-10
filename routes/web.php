<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Admin Dashboard and Console
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Analytics
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'dashboard'])->name('analytics');

    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/suspend', [App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/reinstate', [App\Http\Controllers\Admin\UserController::class, 'reinstate'])->name('users.reinstate');

    // Service Moderation
    Route::get('/services', [App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('services.index');
    Route::post('/services/{service}/approve', [App\Http\Controllers\Admin\ServiceController::class, 'approve'])->name('services.approve');
    Route::post('/services/{service}/disable', [App\Http\Controllers\Admin\ServiceController::class, 'disable'])->name('services.disable');

    // Category Management
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)->except(['show']);

    // Dispute Resolution
    Route::get('/disputes', [App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{dispute}/resolve', [App\Http\Controllers\Admin\DisputeController::class, 'resolve'])->name('disputes.resolve');

    // Platform Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
});

// Student Dashboard
Route::middleware(['auth', 'verified', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');

    Route::get('/services', [App\Http\Controllers\ServiceController::class, 'myServices'])->name('services');
});

// Client Dashboard
Route::middleware(['auth', 'verified', 'client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', function () {
        return view('client.dashboard');
    })->name('dashboard');
});

// Profile routes (accessible to all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::delete('/profile/portfolio', [ProfileController::class, 'deletePortfolioItem'])->name('profile.portfolio.delete');
});

// Public profile view
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.public');

// Service routes
Route::get('/services', [App\Http\Controllers\ServiceController::class, 'index'])->name('services.index');
Route::get('/services/create', [App\Http\Controllers\ServiceController::class, 'create'])->middleware(['auth', 'verified'])->name('services.create');
Route::post('/services', [App\Http\Controllers\ServiceController::class, 'store'])->middleware(['auth', 'verified'])->name('services.store');
Route::get('/services/{slug}', [App\Http\Controllers\ServiceController::class, 'show'])->name('services.show');
Route::get('/services/{slug}/edit', [App\Http\Controllers\ServiceController::class, 'edit'])->middleware(['auth', 'verified'])->name('services.edit');
Route::put('/services/{slug}', [App\Http\Controllers\ServiceController::class, 'update'])->middleware(['auth', 'verified'])->name('services.update');
Route::delete('/services/{slug}', [App\Http\Controllers\ServiceController::class, 'destroy'])->middleware(['auth', 'verified'])->name('services.destroy');
Route::patch('/services/{slug}/toggle-active', [App\Http\Controllers\ServiceController::class, 'toggleActive'])->middleware(['auth', 'verified'])->name('services.toggle-active');
Route::get('/services/{slug}/sample-work', [App\Http\Controllers\ServiceController::class, 'serveSampleWork'])->name('services.sample-work');

// Stripe webhook (no auth middleware - Stripe will send webhooks)
Route::post('/stripe/webhook', [App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Stripe Connect routes
Route::middleware(['auth', 'verified', 'student'])->prefix('stripe/connect')->name('stripe.connect.')->group(function () {
    Route::get('/onboarding', [App\Http\Controllers\StripeConnectController::class, 'onboarding'])->name('onboarding');
    Route::get('/return', [App\Http\Controllers\StripeConnectController::class, 'return'])->name('return');
    Route::get('/refresh', [App\Http\Controllers\StripeConnectController::class, 'refresh'])->name('refresh');
    Route::get('/status', [App\Http\Controllers\StripeConnectController::class, 'checkStatus'])->name('status');
});

// Order routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create/{service}', [App\Http\Controllers\OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/status', [App\Http\Controllers\OrderController::class, 'status'])->name('orders.status');

    // Order action routes
    Route::post('/orders/{order}/accept', [App\Http\Controllers\OrderActionController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/decline', [App\Http\Controllers\OrderActionController::class, 'decline'])->name('orders.decline');
    Route::get('/orders/{order}/deliver', [App\Http\Controllers\OrderActionController::class, 'showDeliverForm'])->name('orders.deliver');
    Route::post('/orders/{order}/deliver', [App\Http\Controllers\OrderActionController::class, 'deliver'])->name('orders.deliver.submit');
    Route::post('/orders/{order}/approve', [App\Http\Controllers\OrderActionController::class, 'approve'])->name('orders.approve');
    Route::get('/orders/{order}/request-revision', [App\Http\Controllers\OrderActionController::class, 'showRevisionForm'])->name('orders.request-revision');
    Route::post('/orders/{order}/request-revision', [App\Http\Controllers\OrderActionController::class, 'requestRevision'])->name('orders.request-revision.submit');
    Route::post('/orders/{order}/cancel', [App\Http\Controllers\OrderActionController::class, 'cancel'])->name('orders.cancel');
});

// Message routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/thread', [App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}/download', [App\Http\Controllers\MessageController::class, 'downloadAttachment'])->name('messages.download');
});

// Notification routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [App\Http\Controllers\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

// Review routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders/{order}/review', [App\Http\Controllers\ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/orders/{order}/review', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [App\Http\Controllers\ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'update'])->name('reviews.update');
    Route::post('/reviews/{review}/reply', [App\Http\Controllers\ReviewController::class, 'reply'])->name('reviews.reply');
});

require __DIR__ . '/auth.php';
