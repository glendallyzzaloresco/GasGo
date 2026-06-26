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

        // Get all active vouchers from database sorted by unlock points (for both guests and logged-in users)
        $allVouchers = \App\Models\Voucher::where('is_active', true)
            ->orderBy('reward_points_required', 'asc')
            ->get();

        $points = collect();
        $totalEarned = 0;
        $totalRedeemed = 0;
        $balance = 0;
        $completedOrders = 0;
        $availableVouchers = collect();
        $unlockedVouchers = collect();
        $pointsToNextReward = 0;
        $nextMilestone = 10;      // Default first tier
        $nextReward = $allVouchers->first()?->discount_amount ?? 30; // Get from first voucher or default to 30

        if ($userId) {
            // Real data for logged-in users
            $points = LoyaltyPoint::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            $deliveredOrders = \App\Models\Order::where('user_id', $userId)
                ->where('status', 'delivered')
                ->where('created_at', '>=', now()->subYear())
                ->get();

            $completedOrders = $deliveredOrders->count();
            $totalSpend = $deliveredOrders->sum(function ($order) {
                return max(0, $order->total_amount);
            });

            // SYNC POINTS: Ensure points reflect spend amount for each delivered order
            // 1 point = ₱100 spent on a delivered order
            $this->syncPointsToDeliveredOrders($userId, $deliveredOrders);

            // Refresh point transactions after sync
            $points = LoyaltyPoint::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get balance after sync
            [$totalEarned, $totalRedeemed, $balance] = $this->getBalance($userId);

            // DETERMINE NEXT MILESTONE based on earned points and database vouchers
            $nextTarget = 10;      // Default to first tier
            $nextRewardAmount = 0; // Default
            $nextVoucher = $allVouchers->first();

            if ($allVouchers->count() > 0 && $balance < $allVouchers->last()->reward_points_required) {
                $nextVoucher = $allVouchers->first(function ($voucher) use ($balance) {
                    return $voucher->reward_points_required > $balance;
                });

                if ($nextVoucher) {
                    $nextTarget = $nextVoucher->reward_points_required;
                    $nextRewardAmount = $nextVoucher->discount_amount;
                    $pointsToNextReward = max(0, $nextTarget - $balance);
                } else {
                    $nextTarget = $allVouchers->last()->reward_points_required;
                    $nextRewardAmount = 0;
                    $pointsToNextReward = 0;
                }
            }

            $nextMilestone = $nextTarget;
            $nextReward = $nextRewardAmount;

            $availableVouchers = UserVoucher::where('user_id', $userId)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->orderBy('expires_at', 'asc')
                ->get();

            $activeClaimedVoucherNames = UserVoucher::where('user_id', $userId)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->pluck('voucher_name')
                ->toArray();

            $unlockedVouchers = $allVouchers->map(function ($voucher) use ($balance, $activeClaimedVoucherNames) {
                $voucher->isUnlocked = $balance >= $voucher->reward_points_required;
                $voucher->isClaimed = in_array($voucher->name, $activeClaimedVoucherNames);
                return $voucher;
            });
        }

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
                'title' => 'Buy 20+ LPG Quantity → Free LPG Tank',
                'badge' => 'BULK BONUS',
                'badgeColor' => 'info',
                'icon' => 'fas fa-fire',
                'rules' => [
                    'Purchase 20 or more LPG units in one checkout',
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
            // Guest view: Marketing voucher ladder/preview - use database vouchers
            $rewards = $allVouchers->map(function ($voucher, $index) {
                return [
                    'tier' => ['Bronze', 'Silver', 'Gold', 'Platinum'][$index] ?? 'Tier ' . ($index + 1),
                    'title' => $voucher->name,
                    'requirement' => $voucher->reward_points_required . ' points',
                    'spendRequirement' => $voucher->reward_points_required * 100,
                    'icon' => 'fas fa-tag',
                    'color' => ['bronze', 'silver', 'gold', 'platinum'][$index] ?? 'default',
                ];
            })->values()->toArray();
        } else {
            // Logged-in view: Personal earned/achievable rewards - use database vouchers
            $rewards = $allVouchers->map(function ($voucher) use ($balance) {
                return [
                    'title' => $voucher->name,
                    'requirement' => $voucher->reward_points_required . ' points',
                    'spendRequirement' => $voucher->reward_points_required * 100,
                    'icon' => 'fas fa-tag',
                    'earned' => $balance >= $voucher->reward_points_required,
                ];
            })->toArray();
        }

        $faqs = [
            [
                'question' => 'Do I need to log in to earn loyalty rewards?',
                'answer' => 'Yes, loyalty rewards are saved in your account. You must be logged in to track your delivered orders and earn vouchers.',
            ],
            [
                'question' => 'When are rewards counted?',
                'answer' => 'You earn 1 loyalty point for every ₱100 spent on delivered orders. Points from the past 12 months count toward vouchers, so higher spend unlocks bigger rewards like the ₱50 and ₱100 vouchers.',
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
            'unlockedVouchers',
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

    // Customer: claim an unlocked voucher
    public function claimVoucher(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('customer.login')
                ->with('error', 'Please log in to claim vouchers.');
        }

        $validated = $request->validate([
            'voucher_id' => 'required|integer|exists:vouchers,id',
        ]);

        $userId = Auth::id();
        $voucher = \App\Models\Voucher::find($validated['voucher_id']);

        // Check if voucher exists
        if (!$voucher || !$voucher->is_active) {
            return redirect()->route('customer.loyalty')
                ->with('error', 'This voucher is no longer available.');
        }

        // Block only when the user already has an active unused claim for this voucher.
        $existingUnusedClaim = UserVoucher::where('user_id', $userId)
            ->where('voucher_name', $voucher->name)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingUnusedClaim) {
            return redirect()->route('customer.loyalty')
                ->with('info', 'You already have this voucher claimed. Use it at checkout before claiming again.');
        }

        // Remove any expired unused claims for the same voucher name
        UserVoucher::where('user_id', $userId)
            ->where('voucher_name', $voucher->name)
            ->where('is_used', false)
            ->where('expires_at', '<=', now())
            ->delete();

        // Get user's completed orders
        $deliveredOrders = \App\Models\Order::where('user_id', $userId)
            ->where('status', 'delivered')
            ->where('created_at', '>=', now()->subYear())
            ->get();

        $totalSpend = $deliveredOrders->sum(function ($order) {
            return max(0, ($order->subtotal - $order->discount));
        });

        DB::transaction(function () use ($userId, $voucher) {
            // Lock all loyalty point transactions for this user and recalculate balance
            LoyaltyPoint::where('user_id', $userId)->lockForUpdate()->get();

            [$totalEarned, $totalRedeemed, $balance] = $this->getBalance($userId);

            // Check if user has enough available points to claim this voucher
            if ($balance < $voucher->reward_points_required) {
                abort(422, 'You have not yet unlocked this voucher or do not have enough available points. Earn more points to unlock it.');
            }

            // Add voucher to user_vouchers
            UserVoucher::create([
                'user_id' => $userId,
                'voucher_name' => $voucher->name,
                'discount_amount' => $voucher->discount_amount,
                'description' => $voucher->description,
                'unlocked_at' => now(),
                'expires_at' => now()->addDays(30),
                'is_used' => false,
            ]);

            // Deduct points for the claimed voucher so available balance updates immediately
            LoyaltyPoint::create([
                'user_id' => $userId,
                'points' => $voucher->reward_points_required,
                'type' => 'redeemed',
                'description' => 'Points redeemed to claim ' . $voucher->name,
            ]);
        });

        return redirect()->route('customer.loyalty')
            ->with('success', 'Voucher claimed successfully! Head to checkout to use it.');
    }

    // Admin: view all loyalty transactions
    public function adminIndex()
    {
        $transactions = LoyaltyPoint::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Show all voucher kinds to admin, sorted by active status then unlock points
        $vouchers = \App\Models\Voucher::query()
            ->orderByDesc('is_active')
            ->orderBy('reward_points_required', 'asc')
            ->get();

        // Calculate loyalty statistics
        $loyaltyMembers = count($transactions->pluck('user_id')->unique());
        $totalPointsEarned = $transactions->where('type', 'earned')->sum('points');
        $totalPointsRedeemed = $transactions->where('type', 'redeemed')->sum('points');
        $activePoints = $totalPointsEarned - $totalPointsRedeemed;

        return view('admin.rewards', compact('transactions', 'vouchers', 'loyaltyMembers', 'totalPointsEarned', 'totalPointsRedeemed', 'activePoints'));
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
     * Sync loyalty points with delivered orders based on spend
     * Idempotent: keeps one earned points entry per order and updates its point value
     */
    private function syncPointsToDeliveredOrders(int $userId, $deliveredOrders): void
    {
        foreach ($deliveredOrders as $order) {
            $orderSpend = max(0, $order->total_amount);
            $pointsEarned = (int) floor($orderSpend / 100);

            if ($pointsEarned <= 0) {
                // Remove zero-point entries for orders that no longer qualify
                LoyaltyPoint::where('user_id', $userId)
                    ->where('order_id', $order->id)
                    ->where('type', 'earned')
                    ->delete();
                continue;
            }

            LoyaltyPoint::updateOrCreate(
                [
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'type' => 'earned',
                ],
                [
                    'points' => $pointsEarned,
                    'description' => 'Points earned from Order #' . $order->id,
                ]
            );
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