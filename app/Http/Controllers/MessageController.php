<?php
namespace App\Http\Controllers;

use App\Helpers\ContentFilter;
use App\Models\FlaggedMessage;
use App\Models\Message;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get all unique conversations (threads)
        $threads = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver', 'order', 'service'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($user) {
                // Group by the other participant
                $otherUserId = $message->sender_id === $user->id
                    ? $message->receiver_id
                    : $message->sender_id;

                // Include order_id or service_id in grouping for context
                return $otherUserId . '_' . ($message->order_id ?? 'service_' . $message->service_id);
            })
            ->map(function ($messages) {
                return $messages->first(); // Get the most recent message for each thread
            });

        return view('messages.index', compact('threads'));
    }

    public function show(Request $request)
    {
        $user        = auth()->user();
        $otherUserId = $request->query('user_id');
        $orderId     = $request->query('order_id');
        $serviceId   = $request->query('service_id');

        // Build query for messages in this thread
        $query = Message::where(function ($q) use ($user, $otherUserId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $otherUserId)
                ->orWhere('sender_id', $otherUserId)->where('receiver_id', $user->id);
        });

        if ($orderId) {
            $query->where('order_id', $orderId);
            $order = Order::findOrFail($orderId);
            $this->authorize('view', $order);
        } elseif ($serviceId) {
            $query->where('service_id', $serviceId);
            $service = Service::findOrFail($serviceId);
        }

        $messages = $query->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Message::where('receiver_id', $user->id)
            ->where('sender_id', $otherUserId)
            ->where('is_read', false)
            ->when($orderId, fn($q) => $q->where('order_id', $orderId))
            ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
            ->update(['is_read' => true]);

        $otherUser = \App\Models\User::findOrFail($otherUserId);

        return view('messages.show', compact('messages', 'otherUser', 'orderId', 'serviceId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'order_id'    => 'nullable|exists:orders,id',
            'service_id'  => 'nullable|exists:services,id',
            'content'     => 'required|string|max:5000',
            'attachment'  => 'nullable|file|max:25600|mimes:pdf,doc,docx,jpg,jpeg,png,zip,txt',
        ]);

        // Authorization check
        if ($validated['order_id']) {
            $order = Order::findOrFail($validated['order_id']);
            $this->authorize('view', $order);
        }

        // Check for prohibited content
        $violations = ContentFilter::containsProhibitedContent($validated['content']);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('message-attachments', 'private');
        }

        $message = Message::create([
            'sender_id'       => auth()->id(),
            'receiver_id'     => $validated['receiver_id'],
            'order_id'        => $validated['order_id'] ?? null,
            'service_id'      => $validated['service_id'] ?? null,
            'content'         => $validated['content'],
            'attachment_path' => $attachmentPath,
            'is_read'         => false,
        ]);

        // Flag message if violations detected
        if (! empty($violations)) {
            FlaggedMessage::create([
                'message_id'     => $message->id,
                'flagged_reason' => implode(', ', $violations),
                'created_at'     => now(),
            ]);

            // Notify sender of policy violation
            $violationMessage = ContentFilter::getViolationMessage($violations);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message->load(['sender', 'receiver']),
                    'success' => true,
                    'warning' => $violationMessage,
                ]);
            }

            return back()->with('warning', $violationMessage);
        }

        // Send notification to receiver
        $message->receiver->notify(new \App\Notifications\MessageReceivedNotification($message));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message->load(['sender', 'receiver']),
                'success' => true,
            ]);
        }

        return back()->with('success', 'Message sent successfully.');
    }

    public function downloadAttachment(Message $message)
    {
        $this->authorize('view', $message);

        if (! $message->attachment_path) {
            abort(404, 'No attachment found.');
        }

        if (! Storage::disk('private')->exists($message->attachment_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download($message->attachment_path);
    }
}
