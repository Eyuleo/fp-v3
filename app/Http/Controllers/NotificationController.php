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

    public function recent()
    {
        $notifications = auth()->user()
            ->notifications()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'title'      => $notification->data['title'] ?? 'Notification',
                    'message'    => $notification->data['message'] ?? '',
                    'action_url' => $notification->data['action_url'] ?? '#',
                    'read_at'    => $notification->read_at,
                    'time_ago'   => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
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
