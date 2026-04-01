<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Freebie;
use App\Models\HomepageSetting;
use App\Models\Inventory;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $totalOrders = Order::count();
        $revenue = Order::whereHas('delivery', function ($query) {
            $query->where('status', 'delivered');
        })->sum('total_amount');
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalCustomers = User::where('role', 'customer')->count();

        // Get all orders for modals
        $orders = Order::with('user', 'orderItems')->get();

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

        // Low stock products and freebies combined
        $products = Product::with('inventory')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->get()
            ->map(function ($item) {
                $item->stock = (int) ($item->quantity_on_hand ?? 0);
                return $item;
            })
            ->sortBy('stock')
            ->values();
        
        // Get product-category freebies
        $productFreebies = Product::with('inventory')
            ->where('is_active', true)
            ->where('category', 'freebie')
            ->get()
            ->map(function ($item) {
                $item->stock = (int) ($item->quantity_on_hand ?? 0);
                return $item;
            });
        
        $freebies = Freebie::where('is_active', true)
            ->orderBy('stock')
            ->get();
        
        // Merge products and freebies with type indicator
        $allItems = $products->map(function ($item) {
            $item->item_type = 'product';
            return $item;
        })->concat(
            $productFreebies->map(function ($item) {
                $item->item_type = 'product';
                return $item;
            })
        )->concat(
            $freebies->map(function ($item) {
                $item->item_type = 'freebie';
                return $item;
            })
        )->sortBy('stock');

        $lowStockCount = $allItems->where('stock', '<=', 5)->count();

        return view('admin.dashboard', compact(
            'totalOrders',
            'revenue',
            'pendingOrders',
            'totalCustomers',
            'orders',
            'riders',
            'activeRiders',
            'recentOrders',
            'products',
            'productFreebies',
            'freebies',
            'allItems',
            'lowStockCount',
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

    public function reports(Request $request)
    {
        $selectedPeriod = $request->query('period', 'this_month');
        $allowedPeriods = ['today', 'this_week', 'this_month', 'last_3_months', 'this_year'];
        if (! in_array($selectedPeriod, $allowedPeriods, true)) {
            $selectedPeriod = 'this_month';
        }

        $now = Carbon::now();
        $rangeStart = null;
        $rangeEnd = null;

        if ($selectedPeriod === 'today') {
            $rangeStart = $now->copy()->startOfDay();
            $rangeEnd = $now->copy()->endOfDay();
        } elseif ($selectedPeriod === 'this_week') {
            $rangeStart = $now->copy()->startOfWeek();
            $rangeEnd = $now->copy()->endOfWeek();
        } elseif ($selectedPeriod === 'this_month') {
            $rangeStart = $now->copy()->startOfMonth();
            $rangeEnd = $now->copy()->endOfMonth();
        } elseif ($selectedPeriod === 'last_3_months') {
            $rangeStart = $now->copy()->subMonths(2)->startOfMonth();
            $rangeEnd = $now->copy()->endOfMonth();
        } elseif ($selectedPeriod === 'this_year') {
            $rangeStart = $now->copy()->startOfYear();
            $rangeEnd = $now->copy()->endOfYear();
        }

        $periodLabel = match ($selectedPeriod) {
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'last_3_months' => 'Last 3 Months',
            'this_year' => 'This Year',
            default => 'This Month',
        };

        $applyDateRange = function ($query, string $column = 'created_at') use ($rangeStart, $rangeEnd) {
            if ($rangeStart && $rangeEnd) {
                $query->whereBetween($column, [$rangeStart, $rangeEnd]);
            }

            return $query;
        };

        $ordersInPeriod = Order::query()->whereHas('delivery', function ($query) {
            $query->where('status', 'delivered');
        });
        $applyDateRange($ordersInPeriod);

        // Total revenue from filtered delivered orders
        $totalRevenue = (clone $ordersInPeriod)->sum('total_amount');

        // Total orders count in selected period
        $totalOrders = (clone $ordersInPeriod)->count();
        
        // Average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        // Delivery completion percentage
        $totalDeliveriesQuery = Delivery::query();
        $applyDateRange($totalDeliveriesQuery);
        $totalDeliveries = $totalDeliveriesQuery->count();

        $completedDeliveriesQuery = Delivery::query()->where('status', 'delivered');
        $applyDateRange($completedDeliveriesQuery);
        $completedDeliveries = $completedDeliveriesQuery->count();
        $deliveryCompletion = $totalDeliveries > 0 ? round(($completedDeliveries / $totalDeliveries) * 100) : 0;
        
        // Top products by quantity sold
        $topProductsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('deliveries', 'orders.id', '=', 'deliveries.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'))
            ->where('deliveries.status', 'delivered')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(4);
        $applyDateRange($topProductsQuery, 'orders.created_at');
        $topProducts = $topProductsQuery->get();
        
        // Payment method breakdown
        $paymentMethodsQuery = Order::whereHas('delivery', function ($query) {
            $query->where('status', 'delivered');
        })
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('payment_method');
        $applyDateRange($paymentMethodsQuery);
        $paymentMethods = $paymentMethodsQuery->get();
        
        // Average delivery time - simplified approach
        $avgDeliveryTime = 35; // Default placeholder; enhance with actual calculation if needed
        
        // Average customer rating - simplified approach
        $avgRating = 4.7; // Default placeholder; enhance with actual calculation if needed

        // Marketing analytics
        $totalCustomers = User::where('role', 'customer')->count();
        $newCustomersInPeriodQuery = User::where('role', 'customer');
        $applyDateRange($newCustomersInPeriodQuery);
        $newCustomersInPeriod = $newCustomersInPeriodQuery->count();

        $payingCustomers = (clone $ordersInPeriod)
            ->select('orders.user_id')
            ->distinct('orders.user_id')
            ->count('orders.user_id');

        $repeatCustomers = (clone $ordersInPeriod)
            ->select('orders.user_id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('orders.user_id')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->count();

        $repeatCustomerRate = $payingCustomers > 0
            ? round(($repeatCustomers / $payingCustomers) * 100)
            : 0;

        $loyaltyMembersQuery = LoyaltyPoint::query();
        $applyDateRange($loyaltyMembersQuery);
        $loyaltyMembers = $loyaltyMembersQuery->distinct('user_id')->count('user_id');

        $redeemingCustomersQuery = LoyaltyPoint::where('type', 'redeemed');
        $applyDateRange($redeemingCustomersQuery);
        $redeemingCustomers = $redeemingCustomersQuery
            ->distinct('user_id')
            ->count('user_id');

        $loyaltyAdoptionRate = $totalCustomers > 0
            ? round(($loyaltyMembers / $totalCustomers) * 100)
            : 0;

        $rewardRedemptionRate = $loyaltyMembers > 0
            ? round(($redeemingCustomers / $loyaltyMembers) * 100)
            : 0;

        $avgRevenuePerCustomer = $payingCustomers > 0
            ? $totalRevenue / $payingCustomers
            : 0;

        $customerGrowth = collect(range(5, 0))->map(function ($offset) {
            $monthDate = Carbon::now()->startOfMonth()->subMonths($offset);
            $count = User::where('role', 'customer')
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();

            return [
                'label' => $monthDate->format('M'),
                'count' => $count,
            ];
        });

        $revenueTrend = collect(range(5, 0))->map(function ($offset) {
            $monthDate = Carbon::now()->startOfMonth()->subMonths($offset);
            $revenue = Order::whereHas('delivery', function ($query) {
                $query->where('status', 'delivered');
            })
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('total_amount');

            return [
                'label' => $monthDate->format('M'),
                'value' => round((float) $revenue, 2),
            ];
        });

        $paymentBreakdown = $paymentMethods->map(function ($payment) {
            return [
                'label' => ucfirst((string) $payment->payment_method),
                'value' => (float) $payment->revenue,
            ];
        })->values();

        $maxCustomerGrowth = max(1, (int) $customerGrowth->max('count'));

        $topCustomersQuery = Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('deliveries', 'orders.id', '=', 'deliveries.order_id')
            ->where('deliveries.status', 'delivered')
            ->where('users.role', 'customer')
            ->select(
                'users.name',
                'users.email',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total_amount) as total_spent')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(5);
        $applyDateRange($topCustomersQuery, 'orders.created_at');
        $topCustomers = $topCustomersQuery->get();

        if ($request->boolean('ajax')) {
            $totalPaymentRevenue = (float) $paymentMethods->sum('revenue');

            return response()->json([
                'periodLabel' => $periodLabel,
                'metrics' => [
                    'totalRevenue' => (float) $totalRevenue,
                    'totalOrders' => (int) $totalOrders,
                    'avgOrderValue' => (float) $avgOrderValue,
                    'deliveryCompletion' => (int) $deliveryCompletion,
                    'newCustomersInPeriod' => (int) $newCustomersInPeriod,
                    'repeatCustomerRate' => (int) $repeatCustomerRate,
                    'totalCustomers' => (int) $totalCustomers,
                    'payingCustomers' => (int) $payingCustomers,
                    'repeatCustomers' => (int) $repeatCustomers,
                    'loyaltyAdoptionRate' => (int) $loyaltyAdoptionRate,
                    'loyaltyMembers' => (int) $loyaltyMembers,
                    'rewardRedemptionRate' => (int) $rewardRedemptionRate,
                    'redeemingCustomers' => (int) $redeemingCustomers,
                    'avgRevenuePerCustomer' => (float) $avgRevenuePerCustomer,
                    'totalDeliveries' => (int) $totalDeliveries,
                    'avgDeliveryTime' => (float) $avgDeliveryTime,
                    'avgRating' => (float) $avgRating,
                ],
                'charts' => [
                    'revenueTrend' => [
                        'labels' => $revenueTrend->pluck('label')->values(),
                        'data' => $revenueTrend->pluck('value')->values(),
                    ],
                    'customerGrowth' => [
                        'labels' => $customerGrowth->pluck('label')->values(),
                        'data' => $customerGrowth->pluck('count')->values(),
                    ],
                    'paymentBreakdown' => [
                        'labels' => $paymentBreakdown->pluck('label')->values(),
                        'data' => $paymentBreakdown->pluck('value')->values(),
                    ],
                ],
                'lists' => [
                    'topProducts' => $topProducts,
                    'topCustomers' => $topCustomers,
                    'paymentMethods' => $paymentMethods->map(function ($payment) use ($totalPaymentRevenue) {
                        $percent = $totalPaymentRevenue > 0
                            ? round((((float) $payment->revenue) / $totalPaymentRevenue) * 100)
                            : 0;

                        return [
                            'payment_method' => (string) $payment->payment_method,
                            'revenue' => (float) $payment->revenue,
                            'percent' => $percent,
                        ];
                    })->values(),
                ],
            ]);
        }
        
        return view('admin.reports', compact(
            'selectedPeriod',
            'periodLabel',
            'totalRevenue',
            'totalOrders',
            'avgOrderValue',
            'deliveryCompletion',
            'topProducts',
            'paymentMethods',
            'totalDeliveries',
            'avgDeliveryTime',
            'avgRating',
            'totalCustomers',
            'newCustomersInPeriod',
            'payingCustomers',
            'repeatCustomers',
            'repeatCustomerRate',
            'loyaltyMembers',
            'redeemingCustomers',
            'loyaltyAdoptionRate',
            'rewardRedemptionRate',
            'avgRevenuePerCustomer',
            'customerGrowth',
            'maxCustomerGrowth',
            'topCustomers',
            'revenueTrend',
            'paymentBreakdown'
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

    public function notifications()
    {
        $items = [];

        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $pendingOrdersAt = Order::where('status', 'pending')->max('created_at');
        if ($pendingOrdersCount > 0) {
            $items[] = [
                'level' => 'warning',
                'icon' => 'fa-clock',
                'title' => 'Pending Orders',
                'message' => $pendingOrdersCount . ' order(s) need review.',
                'url' => url('/admin/orders'),
                'timestamp' => $pendingOrdersAt,
            ];
        }

        $approvedUnassignedCount = Order::where('status', 'approved')
            ->whereDoesntHave('delivery')
            ->count();
        $approvedUnassignedAt = Order::where('status', 'approved')
            ->whereDoesntHave('delivery')
            ->max('updated_at');
        if ($approvedUnassignedCount > 0) {
            $items[] = [
                'level' => 'info',
                'icon' => 'fa-motorcycle',
                'title' => 'Rider Assignment Needed',
                'message' => $approvedUnassignedCount . ' approved order(s) are waiting for rider assignment.',
                'url' => url('/admin/orders'),
                'timestamp' => $approvedUnassignedAt,
            ];
        }

        $lowStockCount = Inventory::query()
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where('price', '>', 0);
            })
            ->whereRaw('quantity_on_hand <= reorder_level')
            ->count();
        $lowStockAt = Inventory::query()
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where('price', '>', 0);
            })
            ->whereRaw('quantity_on_hand <= reorder_level')
            ->max('updated_at');
        if ($lowStockCount > 0) {
            $items[] = [
                'level' => 'warning',
                'icon' => 'fa-box-open',
                'title' => 'Low Stock Alert',
                'message' => $lowStockCount . ' product(s) are at or below reorder level.',
                'url' => url('/admin/inventory'),
                'timestamp' => $lowStockAt,
            ];
        }

        $activeDeliveriesCount = Delivery::whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])->count();
        $activeDeliveriesAt = Delivery::whereIn('status', ['assigned', 'picked_up', 'out_for_delivery'])->max('updated_at');
        if ($activeDeliveriesCount > 0) {
            $items[] = [
                'level' => 'success',
                'icon' => 'fa-truck',
                'title' => 'Deliveries In Progress',
                'message' => $activeDeliveriesCount . ' active delivery job(s) are ongoing.',
                'url' => url('/admin/deliveries'),
                'timestamp' => $activeDeliveriesAt,
            ];
        }

        $failedDeliveriesCount = Delivery::where('status', 'failed')
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();
        $failedDeliveriesAt = Delivery::where('status', 'failed')
            ->where('updated_at', '>=', now()->subDays(7))
            ->max('updated_at');
        if ($failedDeliveriesCount > 0) {
            $items[] = [
                'level' => 'danger',
                'icon' => 'fa-triangle-exclamation',
                'title' => 'Failed Deliveries',
                'message' => $failedDeliveriesCount . ' failed delivery case(s) in the last 7 days.',
                'url' => url('/admin/deliveries'),
                'timestamp' => $failedDeliveriesAt,
            ];
        }

        $notifications = collect($items)
            ->map(function ($item) {
                $timestamp = $item['timestamp'] ? Carbon::parse($item['timestamp']) : null;

                return [
                    'level' => $item['level'],
                    'icon' => $item['icon'],
                    'title' => $item['title'],
                    'message' => $item['message'],
                    'url' => $item['url'],
                    'time' => $timestamp ? $timestamp->toIso8601String() : null,
                    'time_human' => $timestamp ? $timestamp->diffForHumans() : 'just now',
                    'sort_time' => $timestamp ? $timestamp->timestamp : 0,
                ];
            })
            ->sortByDesc('sort_time')
            ->values()
            ->take(7)
            ->map(function ($item) {
                unset($item['sort_time']);
                return $item;
            })
            ->values();

        return response()->json([
            'count' => $notifications->count(),
            'items' => $notifications,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function settings()
    {
        $homepageSettings = HomepageSetting::singleton();
        $appName = config('app.name');
        $appEnv = config('app.env');
        $appDebug = config('app.debug');
        $dbConnection = config('database.default');
        $cacheDriver = config('cache.default');
        $queueDriver = config('queue.default');
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        return view('admin.settings', compact(
            'homepageSettings',
            'appName', 'appEnv', 'appDebug',
            'dbConnection', 'cacheDriver', 'queueDriver',
            'phpVersion', 'laravelVersion'
        ));
    }

    public function storeAdminUser(Request $request)
    {
        if (! Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        $actingUser = User::query()->findOrFail(Auth::id());
        if ($actingUser->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'role' => 'admin',
        ]);

        return back()->with('success', 'New admin account created successfully.');
    }

    public function updateGCash(Request $request)
    {
        $validated = $request->validate([
            'gcash_account_number' => 'nullable|string|max:255',
            'gcash_account_name' => 'nullable|string|max:255',
        ]);

        $homepageSettings = HomepageSetting::singleton();
        $homepageSettings->update($validated);

        return back()->with('success', 'GCash account details updated successfully.');
    }

    public function profile()
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = User::query()->findOrFail(Auth::id());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->name = $validated['name'];
        $user->email = strtolower($validated['email']);
        $user->phone = $validated['phone'] ?? $user->phone;

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        $message = 'Your profile has been updated successfully.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message], 200);
        }
        return back()->with('success', $message);
    }
    }
