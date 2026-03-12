<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $totalOrders = Order::count();
        $revenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalCustomers = User::where('role', 'customer')->count();

        // Riders with their user info
        $riders = Rider::with('user')->get();

        $activeRiders = $riders->where('availability', 'available')->count()
                      + $riders->where('availability', 'busy')->count();

        // Count today's deliveries per rider (rider_id references users table)
        $todayDeliveries = Delivery::whereDate('created_at', today())
            ->selectRaw('rider_id, count(*) as total')
            ->groupBy('rider_id')
            ->pluck('total', 'rider_id');

        // Active delivery per rider
        $activeDeliveries = Delivery::whereNotIn('status', ['delivered', 'cancelled'])
            ->with('order')
            ->get()
            ->keyBy('rider_id');

        // Recent orders with user info
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Low stock products
        $products = Product::where('is_active', true)
            ->orderBy('stock')
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'revenue',
            'pendingOrders',
            'totalCustomers',
            'riders',
            'activeRiders',
            'recentOrders',
            'products',
            'todayDeliveries',
            'activeDeliveries'
        ));
    }

    public function orders()
    {
        return view('admin.orders');
    }

    public function products()
    {
        return view('admin.products');
    }

    public function categories()
    {
        return view('admin.categories');
    }

    public function riders()
    {
        return view('admin.riders');
    }

    public function deliveries()
    {
        return view('admin.deliveries');
    }

    public function promotions()
    {
        return view('admin.promotions');
    }

    public function rewards()
    {
        $transactions = \App\Models\LoyaltyPoint::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.rewards', compact('transactions'));
    }

    public function reports()
    {
        // Total revenue from completed orders
        $totalRevenue = Order::whereIn('status', ['completed', 'delivered'])->sum('total_amount');
        
        // Total orders count
        $totalOrders = Order::count();
        
        // Average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        // Delivery completion percentage
        $totalDeliveries = Delivery::count();
        $completedDeliveries = Delivery::where('status', 'delivered')->count();
        $deliveryCompletion = $totalDeliveries > 0 ? round(($completedDeliveries / $totalDeliveries) * 100) : 0;
        
        // Top products by quantity sold
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(4)
            ->get();
        
        // Payment method breakdown
        $paymentMethods = Order::whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('payment_method')
            ->get();
        
        // Average delivery time - simplified approach
        $avgDeliveryTime = 35; // Default placeholder; enhance with actual calculation if needed
        
        // Average customer rating - simplified approach
        $avgRating = 4.7; // Default placeholder; enhance with actual calculation if needed
        
        return view('admin.reports', compact(
            'totalRevenue',
            'totalOrders',
            'avgOrderValue',
            'deliveryCompletion',
            'topProducts',
            'paymentMethods',
            'totalDeliveries',
            'avgDeliveryTime',
            'avgRating'
        ));
    }

    public function customers()
    {
        // Total customers
        $customers = User::where('role', 'customer')
            ->with(['orders', 'loyaltyPoints'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate stats for each customer
        $customerStats = $customers->map(function ($customer) {
            $totalOrders = $customer->orders->count();
            $totalSpent = $customer->orders->sum('total_amount');
            $loyaltyPoints = $customer->loyaltyPoints->where('type', 'earned')->sum('points') - 
                            $customer->loyaltyPoints->where('type', 'redeemed')->sum('points');
            $lastOrder = $customer->orders->sortByDesc('created_at')->first();

            // Determine loyalty tier based on points
            if ($loyaltyPoints >= 300) {
                $loyaltyTier = 'Gold';
                $loyaltyBadge = 'success';
            } elseif ($loyaltyPoints >= 150) {
                $loyaltyTier = 'Silver';
                $loyaltyBadge = 'primary';
            } elseif ($loyaltyPoints > 0) {
                $loyaltyTier = 'Member';
                $loyaltyBadge = 'secondary';
            } else {
                $loyaltyTier = null;
                $loyaltyBadge = null;
            }

            return [
                'customer' => $customer,
                'totalOrders' => $totalOrders,
                'totalSpent' => $totalSpent,
                'loyaltyPoints' => $loyaltyPoints,
                'loyaltyTier' => $loyaltyTier,
                'loyaltyBadge' => $loyaltyBadge,
                'lastOrder' => $lastOrder
            ];
        });

        // Statistics
        $totalCustomers = $customers->count();
        $activeThisMonth = $customers->filter(function ($c) {
            return $c->orders->filter(fn($o) => $o->created_at->isCurrentMonth())->count() > 0;
        })->count();
        $loyaltyMembers = $customers->filter(function ($c) {
            $points = $c->loyaltyPoints->where('type', 'earned')->sum('points') - 
                     $c->loyaltyPoints->where('type', 'redeemed')->sum('points');
            return $points > 0;
        })->count();
        $newThisMonth = $customers->filter(fn($c) => $c->created_at->isCurrentMonth())->count();

        return view('admin.customers', compact(
            'customerStats',
            'totalCustomers',
            'activeThisMonth',
            'loyaltyMembers',
            'newThisMonth'
        ));
    }
}
