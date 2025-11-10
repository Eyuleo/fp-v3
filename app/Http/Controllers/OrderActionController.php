<?php
namespace App\Http\Controllers;

use App\Actions\Orders\AcceptOrderAction;
use App\Actions\Orders\ApproveOrderAction;
use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\DeclineOrderAction;
use App\Actions\Orders\DeliverWorkAction;
use App\Actions\Orders\RequestRevisionAction;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderActionController extends Controller
{
    /**
     * Accept an order (student).
     */
    public function accept(Order $order)
    {
        $this->authorize('accept', $order);

        try {
            $action = new AcceptOrderAction();
            $action->execute($order);

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order accepted successfully! Delivery date has been set.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Decline an order (student).
     */
    public function decline(Request $request, Order $order)
    {
        $this->authorize('decline', $order);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $action = new DeclineOrderAction();
            $action->execute($order, $validated['reason'] ?? null);

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order declined. A refund has been initiated.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show deliver work form (student).
     */
    public function showDeliverForm(Order $order)
    {
        $this->authorize('deliver', $order);

        return view('orders.deliver', compact('order'));
    }

    /**
     * Deliver work (student).
     */
    public function deliver(Request $request, Order $order)
    {
        $this->authorize('deliver', $order);

        $validated = $request->validate([
            'delivery_note' => 'nullable|string|max:1000',
            'files.*'       => 'nullable|file|max:25600|mimes:pdf,doc,docx,zip,jpg,jpeg,png',
        ]);

        try {
            $action = new DeliverWorkAction();
            $action->execute(
                $order,
                $validated['delivery_note'] ?? null,
                $request->file('files')
            );

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Work delivered successfully! The client will review it.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show request revision form (client).
     */
    public function showRevisionForm(Order $order)
    {
        $this->authorize('requestRevision', $order);

        return view('orders.request-revision', compact('order'));
    }

    /**
     * Request revision (client).
     */
    public function requestRevision(Request $request, Order $order)
    {
        $this->authorize('requestRevision', $order);

        $validated = $request->validate([
            'feedback' => 'required|string|max:1000',
        ]);

        try {
            $action = new RequestRevisionAction();
            $action->execute($order, $validated['feedback']);

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Revision requested. The student will work on your feedback.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Approve and complete order (client).
     */
    public function approve(Order $order)
    {
        $this->authorize('approve', $order);

        try {
            $action = new ApproveOrderAction();
            $action->execute($order);

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order completed! Payment has been released to the student.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel order (client or student).
     */
    public function cancel(Request $request, Order $order)
    {
        $this->authorize('cancel', $order);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $action = new CancelOrderAction();
            $action->execute($order, $validated['reason'], $request->user()->id);

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
