<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    // Customer: view loyalty points and transaction history
    public function index()
    {
        $points = LoyaltyPoint::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $totalEarned = LoyaltyPoint::where('user_id', Auth::id())
            ->where('type', 'earned')
            ->sum('points');

        $totalRedeemed = LoyaltyPoint::where('user_id', Auth::id())
            ->where('type', 'redeemed')
            ->sum('points');

        $balance = $totalEarned - $totalRedeemed;

        return view('customer.loyalty', compact('points', 'totalEarned', 'totalRedeemed', 'balance'));
    }

    // Award points to a customer (called after order delivery)
    public function earnPoints($userId, $orderId, $amount)
    {
        $pointsEarned = floor($amount / 100); // 1 point per ₱100 spent

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
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $totalEarned = LoyaltyPoint::where('user_id', Auth::id())
            ->where('type', 'earned')
            ->sum('points');

        $totalRedeemed = LoyaltyPoint::where('user_id', Auth::id())
            ->where('type', 'redeemed')
            ->sum('points');

        $balance = $totalEarned - $totalRedeemed;

        if ($validated['points'] > $balance) {
            return redirect()->back()->with('error', 'Insufficient points.');
        }

        LoyaltyPoint::create([
            'user_id'     => Auth::id(),
            'points'      => $validated['points'],
            'type'        => 'redeemed',
            'description' => 'Points redeemed for discount',
        ]);

        return redirect()->route('customer.loyalty')->with('success', $validated['points'] . ' points redeemed successfully.');
    }

    // Admin: view all loyalty transactions
    public function adminIndex()
    {
        $transactions = LoyaltyPoint::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.rewards', compact('transactions'));
    }
}

class LoyaltyController extends Controller
{
    // Customer: view loyalty points and transaction history
    public function index()
    {
        $points = LoyaltyPoint::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        [$totalEarned, $totalRedeemed, $balance] = $this->getBalance(Auth::id());

        return view('customer.loyalty', compact('points', 'totalEarned', 'totalRedeemed', 'balance'));
    }

    // Award points to a customer (called internally after order delivery)
    public function earnPoints($userId, $orderId, $amount)
    {
        $pointsEarned = floor($amount / 100); // 1 point per ₱100 spent

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
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            // Lock the user's rows to prevent concurrent redemptions
            LoyaltyPoint::where('user_id', Auth::id())->lockForUpdate()->get();

            [$totalEarned, $totalRedeemed, $balance] = $this->getBalance(Auth::id());

            if ($validated['points'] > $balance) {
                abort(422, 'Insufficient points.');
            }

            LoyaltyPoint::create([
                'user_id'     => Auth::id(),
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

        return view('admin.rewards', compact('transactions'));
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
