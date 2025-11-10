<?php
namespace App\Http\Controllers\Admin;

use App\Actions\ResolveDisputeAction;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispute::with(['order.service', 'order.student', 'order.client', 'openedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to showing open disputes
            $query->where('status', 'open');
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load([
            'order.service',
            'order.student',
            'order.client',
            'order.messages',
            'order.payment',
            'openedBy',
            'resolvedBy',
        ]);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'resolution_type'  => 'required|in:release,refund,partial',
            'resolution_notes' => 'required|string|max:1000',
            'partial_amount'   => 'nullable|numeric|min:0|max:' . $dispute->order->price,
        ]);

        if ($validated['resolution_type'] === 'partial' && ! isset($validated['partial_amount'])) {
            return back()->with('error', 'Partial amount is required for partial resolution.');
        }

        app(ResolveDisputeAction::class)->execute(
            $dispute,
            $validated['resolution_type'],
            $validated['resolution_notes'],
            $validated['partial_amount'] ?? null,
            $request->user()->id
        );

        return redirect()->route('admin.disputes.index')
            ->with('success', 'Dispute has been resolved successfully.');
    }
}
