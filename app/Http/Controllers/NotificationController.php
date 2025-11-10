<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $count = auth()->user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Redirect to the action URL if available
        $actionUrl = $notification->data['action_url'] ?? route('dashboard');
        return redirect($actionUrl);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
