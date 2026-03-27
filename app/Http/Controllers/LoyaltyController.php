<?php

namespace App\Http\Controllers;

use App\Models\Freebie;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    // Customer: view loyalty points and transaction history
    public function index()
    {
        $userId = Auth::id();

        $points = collect();
        $totalEarned = 0;
        $totalRedeemed = 0;
        $balance = 0;

        if ($userId) {
            $points = LoyaltyPoint::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            [$totalEarned, $totalRedeemed, $balance] = $this->getBalance($userId);
        }

        return view('customer.loyalty', compact('points', 'totalEarned', 'totalRedeemed', 'balance'));
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
}