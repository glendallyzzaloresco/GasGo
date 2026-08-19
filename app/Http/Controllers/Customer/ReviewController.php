<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\ServiceReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Store a customer review for a delivered order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
            'service_tags' => 'nullable|array',
            'service_tags.*' => 'string|max:50',
        ]);

        $userId = Auth::id();
        $order = Order::with('delivery')->where('id', $validated['order_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($order->status !== 'delivered') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You can only review delivered orders.'], 422);
            }
            return back()->with('error', 'You can only review completed deliveries.');
        }

        if ($order->serviceReview) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This order has already been reviewed.'], 422);
            }
            return back()->with('error', 'You have already reviewed this order.');
        }

        $riderId = $order->delivery?->rider_id;
        $pointsEarned = $order->claimable_points;

        DB::transaction(function () use ($validated, $userId, $order, $riderId, $pointsEarned) {
            ServiceReview::create([
                'user_id'      => $userId,
                'order_id'     => $order->id,
                'rider_id'     => $riderId,
                'rating'       => $validated['rating'],
                'comment'      => $validated['comment'] ?? null,
                'service_tags' => $validated['service_tags'] ?? [],
                'is_featured'  => ($validated['rating'] >= 4), // Auto-feature 4 & 5-star reviews initially
                'is_approved'  => true,
            ]);

            // Award spend-based loyalty points for reviewing this order
            if ($pointsEarned > 0) {
                LoyaltyPoint::updateOrCreate(
                    [
                        'user_id'     => $userId,
                        'order_id'    => $order->id,
                        'type'        => 'earned',
                    ],
                    [
                        'points'      => $pointsEarned,
                        'description' => 'Points claimed by reviewing Order #' . $order->order_number,
                    ]
                );
            }
        });

        $message = $pointsEarned > 0 
            ? "Thank you for your feedback! You claimed +{$pointsEarned} loyalty points for this order."
            : "Thank you for your feedback!";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Admin view for managing reviews.
     */
    public function adminIndex(Request $request)
    {
        $query = ServiceReview::with(['user', 'order', 'rider'])->latest();

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('status')) {
            if ($request->status === 'featured') {
                $query->where('is_featured', true);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        $reviews = $query->paginate(15)->withQueryString();
        $averageRating = ServiceReview::where('is_approved', true)->avg('rating') ?? 5.0;
        $totalReviews = ServiceReview::where('is_approved', true)->count();

        return view('admin.reviews.index', compact('reviews', 'averageRating', 'totalReviews'));
    }

    /**
     * Admin: Toggle featured status.
     */
    public function toggleFeatured(ServiceReview $review)
    {
        $review->update(['is_featured' => !$review->is_featured]);
        $status = $review->is_featured ? 'featured on the homepage' : 'removed from homepage highlights';
        return back()->with('success', "Review is now {$status}.");
    }

    /**
     * Admin: Toggle approval status.
     */
    public function toggleApproval(ServiceReview $review)
    {
        $review->update(['is_approved' => !$review->is_approved]);
        $status = $review->is_approved ? 'approved' : 'hidden';
        return back()->with('success', "Review is now {$status}.");
    }

    /**
     * Admin: Delete review.
     */
    public function destroy(ServiceReview $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted successfully.');
    }
}
