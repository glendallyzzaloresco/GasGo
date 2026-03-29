<?php

namespace App\Http\Controllers;

use App\Models\Freebie;
use App\Models\LoyaltyPoint;
use App\Models\UserVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    // Customer: view loyalty points and transaction history
    public function index()
    {
        $userId = Auth::id();
        $isGuest = !$userId;

        $points = collect();
        $totalEarned = 0;
        $totalRedeemed = 0;
        $balance = 0;
        $completedOrders = 0;
        $availableVouchers = collect();
        $pointsToNextReward = 0;
        $nextMilestone = 10;      // Default first tier
        $nextReward = 30;         // Default first tier reward

        if ($userId) {
            // Real data for logged-in users
            $points = LoyaltyPoint::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Count delivered orders for loyalty tier (in last 12 months)
            $completedOrders = \App\Models\Order::where('user_id', $userId)
                ->where('status', 'delivered')
                ->where('created_at', '>=', now()->subYear())
                ->count();

            // SYNC POINTS: Ensure points match delivered orders count (1 point per order)
            $this->syncPointsToDeliveredOrders($userId, $completedOrders);

            // Get balance after sync
            [$totalEarned, $totalRedeemed, $balance] = $this->getBalance($userId);

            // DETERMINE NEXT MILESTONE based on delivered orders
            $nextTarget = 10;      // Default to first tier
            $nextRewardAmount = 30; // ₱30 OFF
            
            if ($completedOrders >= 30) {
                // All rewards unlocked
                $nextTarget = 30;
                $nextRewardAmount = 0;
                $pointsToNextReward = 0;
            } else if ($completedOrders >= 20) {
                // Tier 3: ₱100 OFF at 30 orders
                $nextTarget = 30;
                $nextRewardAmount = 100;
                $pointsToNextReward = max(0, $nextTarget - $completedOrders);
            } else if ($completedOrders >= 10) {
                // Tier 2: ₱50 OFF at 20 orders
                $nextTarget = 20;
                $nextRewardAmount = 50;
                $pointsToNextReward = max(0, $nextTarget - $completedOrders);
            } else {
                // Tier 1: ₱30 OFF at 10 orders
                $nextTarget = 10;
                $nextRewardAmount = 30;
                $pointsToNextReward = max(0, $nextTarget - $completedOrders);
            }

            // Pass milestone data to view
            $nextMilestone = $nextTarget;
            $nextReward = $nextRewardAmount;

            // Get active/available user vouchers (not expired, not used)
            $availableVouchers = UserVoucher::where('user_id', $userId)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->orderBy('expires_at', 'asc')
                ->get();
        }

        // Marketing data for both guest and logged-in users
        $promos = [
            [
                'id' => 'freebie_auto',
                'title' => 'FREE Freebie with Any LPG Order',
                'badge' => 'AUTO-INCLUDED',
                'badgeColor' => 'success',
                'icon' => 'fas fa-gift',
                'rules' => [
                    'Buy at least 1 LPG item',
                    '1 freebie per order',
                    'Automatically added at checkout when qualified',
                ],
            ],
            [
                'id' => 'bulk_bonus',
                'title' => 'Buy 10+ LPG Quantity → Free LPG Tank',
                'badge' => 'BULK BONUS',
                'badgeColor' => 'info',
                'icon' => 'fas fa-fire',
                'rules' => [
                    'Purchase 10 or more LPG units in one checkout',
                    'Automatically added in checkout when qualified',
                    'Free tank delivered with your order',
                ],
            ],
        ];

        $loyaltySteps = [
            ['number' => 1, 'title' => 'Register / Login', 'icon' => 'fas fa-user-plus'],
            ['number' => 2, 'title' => 'Place Orders', 'icon' => 'fas fa-shopping-cart'],
            ['number' => 3, 'title' => 'Order Delivered', 'icon' => 'fas fa-box'],
            ['number' => 4, 'title' => 'Earn & Redeem', 'icon' => 'fas fa-coins'],
        ];

        // Different rewards display for guests (voucher ladder) vs logged-in users (earned rewards)
        if ($isGuest) {
            // Guest view: Marketing voucher ladder/preview
            $rewards = [
                ['tier' => 'Bronze', 'title' => '₱30 OFF Voucher', 'requirement' => '10 delivered orders within 12 months', 'icon' => 'fas fa-tag', 'color' => 'bronze'],
                ['tier' => 'Silver', 'title' => '₱50 OFF Voucher', 'requirement' => '20 delivered orders within 12 months', 'icon' => 'fas fa-tag', 'color' => 'silver'],
                ['tier' => 'Gold', 'title' => '₱100 OFF Voucher', 'requirement' => '30 delivered orders within 12 months', 'icon' => 'fas fa-crown', 'color' => 'gold'],
            ];
        } else {
            // Logged-in view: Personal earned/achievable rewards
            $rewards = [
                ['title' => '₱30 OFF Voucher', 'requirement' => '10 delivered orders', 'icon' => 'fas fa-tag', 'earned' => $completedOrders >= 10],
                ['title' => '₱50 OFF Voucher', 'requirement' => '20 delivered orders', 'icon' => 'fas fa-tag', 'earned' => $completedOrders >= 20],
                ['title' => '₱100 OFF Voucher', 'requirement' => '30 delivered orders', 'icon' => 'fas fa-crown', 'earned' => $completedOrders >= 30],
            ];
        }

        $faqs = [
            [
                'question' => 'Do I need to log in to earn loyalty rewards?',
                'answer' => 'Yes, loyalty rewards are saved in your account. You must be logged in to track your delivered orders and earn vouchers.',
            ],
            [
                'question' => 'When are rewards counted?',
                'answer' => 'Only delivered orders count towards your loyalty rewards. Each delivered order unlocks you closer to earning vouchers. Orders must be delivered within the last 12 months to count.',
            ],
            [
                'question' => 'How long are vouchers valid?',
                'answer' => 'Each voucher is valid for 30 days after it\'s unlocked. After 30 days, the voucher expires and can no longer be used. Make sure to redeem before expiration!',
            ],
            [
                'question' => 'Do promos apply to guests?',
                'answer' => 'Yes! Exclusive promos like "FREE Freebie with Any LPG Order" and "Bulk Bonus" apply to all customers, both guests and registered users. Promos are automatically applied at checkout when you qualify.',
            ],
        ];

        return view('customer.loyalty', compact(
            'points',
            'totalEarned',
            'totalRedeemed',
            'balance',
            'completedOrders',
            'availableVouchers',
            'pointsToNextReward',
            'nextMilestone',
            'nextReward',
            'isGuest',
            'promos',
            'loyaltySteps',
            'rewards',
            'faqs'
        ));
    }

    // Award points to a customer (called internally after order delivery)
    public function earnPoints($userId, $orderId, $amount)
    {
        $pointsEarned = floor($amount / 100); // 1 point per P100 spent

        if ($pointsEarned > 0) {
            LoyaltyPoint::create([
                'user_id'     => $userId,
                'order_id'    => $orderId,
                'points'      => $pointsEarned,
                'type'        => 'earned',
                'description' => 'Points earned from Order #' . $orderId,
            ]);
        }

        return $pointsEarned;
    }

    // Customer: redeem points
    public function redeem(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('customer.login')
                ->with('error', 'Please log in to redeem loyalty points.');
        }

        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $userId) {
            // Lock the user rows to prevent concurrent redemptions
            LoyaltyPoint::where('user_id', $userId)->lockForUpdate()->get();

            [$totalEarned, $totalRedeemed, $balance] = $this->getBalance($userId);

            if ($validated['points'] > $balance) {
                abort(422, 'Insufficient points.');
            }

            LoyaltyPoint::create([
                'user_id'     => $userId,
                'points'      => $validated['points'],
                'type'        => 'redeemed',
                'description' => 'Points redeemed for discount',
            ]);
        });

        return redirect()->route('customer.loyalty')->with('success', $validated['points'] . ' points redeemed successfully.');
    }

    // Admin: view all loyalty transactions
    public function adminIndex()
    {
        $transactions = LoyaltyPoint::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $rewards = Freebie::query()
            ->where('redemption_type', 'loyalty_points')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.rewards', compact('transactions', 'rewards'));
    }

    // Admin: create reward
    public function storeReward(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string',
            'reward_points_required'=> 'required|integer|min:0',
            'stock'                 => 'required|integer|min:0',
            'image'                 => 'nullable|image|max:2048',
            'is_active'             => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('freebies', 'public');
        }

        $validated['category'] = $request->input('category', 'Rewards');
        $validated['redemption_type'] = 'loyalty_points';
        $validated['is_active'] = $request->boolean('is_active');

        Freebie::create($validated);

        return redirect()->route('admin.rewards')->with('success', 'Reward created successfully.');
    }

    // Admin: update reward
    public function updateReward(Request $request, Freebie $reward)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string',
            'reward_points_required'=> 'required|integer|min:0',
            'stock'                 => 'required|integer|min:0',
            'image'                 => 'nullable|image|max:2048',
            'is_active'             => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('freebies', 'public');
        }

        $validated['category'] = $request->input('category', $reward->category ?: 'Rewards');
        $validated['redemption_type'] = 'loyalty_points';
        $validated['is_active'] = $request->boolean('is_active');

        $reward->update($validated);

        return redirect()->route('admin.rewards')->with('success', 'Reward updated successfully.');
    }

    // Admin: delete reward
    public function destroyReward(Freebie $reward)
    {
        $reward->delete();

        return redirect()->route('admin.rewards')->with('success', 'Reward deleted successfully.');
    }

    /**
     * Sync loyalty points with delivered orders (1 point per order)
     * Idempotent: won't create duplicate points for the same order
     */
    private function syncPointsToDeliveredOrders(int $userId, int $deliveredOrderCount): void
    {
        // Get all delivered orders for the user
        $deliveredOrders = \App\Models\Order::where('user_id', $userId)
            ->where('status', 'delivered')
            ->pluck('id');

        // For each delivered order, ensure there's exactly one 'earned' point entry
        foreach ($deliveredOrders as $orderId) {
            $existingPoint = LoyaltyPoint::where('user_id', $userId)
                ->where('order_id', $orderId)
                ->where('type', 'earned')
                ->exists();

            // Only create if this order doesn't have a point entry yet (idempotent)
            if (!$existingPoint) {
                LoyaltyPoint::create([
                    'user_id'     => $userId,
                    'order_id'    => $orderId,
                    'points'      => 1,
                    'type'        => 'earned',
                    'description' => 'Points earned from Order #' . $orderId,
                ]);
            }
        }
    }

    // Calculate earned, redeemed, and balance for a given user
    private function getBalance(int $userId): array
    {
        $totalEarned = LoyaltyPoint::where('user_id', $userId)
            ->where('type', 'earned')
            ->sum('points');

        $totalRedeemed = LoyaltyPoint::where('user_id', $userId)
            ->where('type', 'redeemed')
            ->sum('points');

        return [$totalEarned, $totalRedeemed, $totalEarned - $totalRedeemed];
    }

    /**
     * Issue a voucher to user when they reach 100 points
     * Only issues one voucher per 100-point milestone
     */
    private function issueVoucherIfEligible(int $userId, int $balance): void
    {
        // Determine how many vouchers should have been issued based on points
        // E.g., 100-199 points = 1 voucher, 200-299 = 2 vouchers, etc.
        $vouchersEarned = (int)($balance / 100);

        // Count how many active (non-expired) vouchers user already has
        $existingVouchers = UserVoucher::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->count();

        // Issue new vouchers if they've earned more than they have
        while ($existingVouchers < $vouchersEarned) {
            UserVoucher::create([
                'user_id'         => $userId,
                'voucher_name'    => '₱30 OFF Voucher',
                'discount_amount' => 30,
                'description'     => 'Earned through loyalty points. Valid for 30 days.',
                'unlocked_at'     => now(),
                'expires_at'      => now()->addDays(30),
            ]);

            $existingVouchers++;
        }
    }
}