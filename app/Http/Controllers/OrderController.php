<?php
namespace App\Http\Controllers;

use App\Actions\Orders\PlaceOrderAction;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Show the form for creating a new order.
     */
    public function create(Service $service)
    {
        // Ensure user is authenticated and verified
        if (! Auth::check() || ! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('error', 'Please verify your email before placing an order.');
        }

        // Ensure user is a client
        if (! Auth::user()->isClient()) {
            abort(403, 'Only clients can place orders.');
        }

        // Ensure service is active
        if (! $service->is_active) {
            abort(404, 'This service is not available.');
        }

        // Load service relationships
        $service->load('student', 'category');

                                // Calculate platform commission (we'll get this from settings later)
        $commissionRate = 0.15; // 15% commission
        $commission     = $service->price * $commissionRate;
        $totalPrice     = $service->price + $commission;

        return view('orders.create', compact('service', 'commission', 'totalPrice'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Authorization will be handled by OrderPolicy
        $this->authorize('view', $order);

        // Handle successful payment return from Stripe
        if (request()->has('payment') && request('payment') === 'success') {
            $sessionId = request('session_id');

            if ($sessionId && ! $order->payment) {
                try {
                    $handleSuccess = new \App\Actions\Payments\HandleCheckoutSuccessAction();
                    $handleSuccess->execute($order, $sessionId);

                    return redirect()
                        ->route('orders.show', $order)
                        ->with('success', 'Payment successful! Your order has been placed.');
                } catch (\Exception $e) {
                    \Log::error('Failed to process payment success', [
                        'order_id' => $order->id,
                        'error'    => $e->getMessage(),
                    ]);

                    return redirect()
                        ->route('orders.show', $order)
                        ->with('warning', 'Payment received but processing is pending. Please contact support if this persists.');
                }
            }
        }

        // Handle cancelled payment
        if (request()->has('payment') && request('payment') === 'cancelled') {
            return redirect()
                ->route('orders.show', $order)
                ->with('warning', 'Payment was cancelled. You can try again or contact support.');
        }

        // Load relationships
        $order->load([
            'service',
            'student',
            'client',
            'messages.sender',
            'review',
            'payment',
        ]);

        // Load dispute if table exists
        try {
            $order->load('dispute');
        } catch (\Exception $e) {
            // Dispute table doesn't exist yet
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            $orders = Order::where('student_id', $user->id)
                ->with(['service', 'client'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } elseif ($user->isClient()) {
            $orders = Order::where('client_id', $user->id)
                ->with(['service', 'student'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Admin can see all orders
            $orders = Order::with(['service', 'student', 'client'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('orders.index', compact('orders'));
    }

    /**
     * Get the current status of an order (for polling).
     */
    public function status(Order $order)
    {
        $this->authorize('view', $order);

        return response()->json([
            'status'     => $order->status,
            'updated_at' => $order->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request, PlaceOrderAction $placeOrderAction)
    {
        $service = Service::findOrFail($request->service_id);

        // Handle file upload if present
        $requirementsText = $request->requirements;
        $attachmentPath   = null;

        if ($request->hasFile('requirements_file')) {
            $file           = $request->file('requirements_file');
            $attachmentPath = $file->store('order-requirements', 'private');
            $requirementsText .= "\n\n[File attached: " . $file->getClientOriginalName() . "]";
        }

        try {
            $result = $placeOrderAction->execute(
                $service,
                Auth::user(),
                $requirementsText
            );

            // If there's an attachment, create a message with it
            if ($attachmentPath) {
                $result['order']->messages()->create([
                    'sender_id'       => Auth::id(),
                    'receiver_id'     => $service->student_id,
                    'content'         => 'Requirements file attached',
                    'attachment_path' => $attachmentPath,
                    'is_read'         => false,
                ]);
            }

            // Redirect to Stripe Checkout
            return redirect($result['checkout_url']);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
