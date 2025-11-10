<?php
namespace App\Http\Controllers;

use App\Actions\ReplyToReviewAction;
use App\Actions\SubmitReviewAction;
use App\Actions\UpdateReviewAction;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    /**
     * Show the form for creating a new review.
     */
    public function create(Order $order)
    {
        Gate::authorize('create', [Review::class, $order]);

        return view('reviews.create', compact('order'));
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, Order $order)
    {
        Gate::authorize('create', [Review::class, $order]);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text'   => 'nullable|string|max:1000',
        ]);

        $action = new SubmitReviewAction();
        $review = $action->execute($order, $request->user(), $validated);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Review submitted successfully!');
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit(Review $review)
    {
        Gate::authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, Review $review)
    {
        Gate::authorize('update', $review);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text'   => 'nullable|string|max:1000',
        ]);

        try {
            $action = new UpdateReviewAction();
            $action->execute($review, $validated);

            return redirect()
                ->route('orders.show', $review->order)
                ->with('success', 'Review updated successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Store a reply to the review.
     */
    public function reply(Request $request, Review $review)
    {
        Gate::authorize('reply', $review);

        $validated = $request->validate([
            'student_reply' => 'required|string|max:1000',
        ]);

        try {
            $action = new ReplyToReviewAction();
            $action->execute($review, $validated['student_reply']);

            return redirect()
                ->back()
                ->with('success', 'Reply posted successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
