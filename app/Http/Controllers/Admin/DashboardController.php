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
use App\Services\SalesForecastService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $totalOrders = Order::count();
        $revenue = Order::whereHas('delivery', function ($query) {
            $query->where('status', 'delivered');
        })->selectRaw('COALESCE(SUM(subtotal - discount), 0) as revenue')->value('revenue') ?? 0;
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
        return redirect()->route('admin.users');
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

    public function reports(Request $request, SalesForecastService $salesForecastService)
    {
        $analytics = $salesForecastService->buildInventoryAnalytics([
            'period' => $request->query('period', 'this_month'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'forecast_product_id' => $request->query('forecast_product_id'),
            'forecast_period' => $request->query('forecast_period', 'next_month'),
        ]);

        if ($request->boolean('ajax')) {
            return response()->json($analytics);
        }

        return view('admin.reports', $analytics);
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
            $deliveredOrders = $customer->orders->where('status', 'delivered');
            $productTotal = $deliveredOrders->sum(fn($order) => $order->fee_free_total);
            $deliveryTotal = $deliveredOrders->sum('delivery_fee');
            $totalSpent = $productTotal + $deliveryTotal;
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

    public function users()
    {
        // Fetch all riders
        $riders = User::where('role', 'rider')
            ->with('rider')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate rider stats
        $riderStats = $riders->map(function ($rider) {
            $totalDeliveries = $rider->deliveries()->count();
            $completedDeliveries = $rider->deliveries()->where('status', 'delivered')->count();
            $todayDeliveries = $rider->deliveries()->whereDate('created_at', today())->count();
            $availability = $rider->rider?->availability ?? 'offline';

            return [
                'rider' => $rider,
                'totalDeliveries' => $totalDeliveries,
                'completedDeliveries' => $completedDeliveries,
                'todayDeliveries' => $todayDeliveries,
                'availability' => $availability,
            ];
        });

        // Fetch all customers
        $customers = User::where('role', 'customer')
            ->with(['orders', 'loyaltyPoints'])
            ->orderBy('created_at', 'desc')
            ->get();

        $customerStats = $customers->map(function ($customer) {
            $totalOrders = $customer->orders->count();
            $deliveredOrders = $customer->orders->where('status', 'delivered');
            $productTotal = $deliveredOrders->sum(fn($order) => $order->fee_free_total);
            $deliveryTotal = $deliveredOrders->sum('delivery_fee');
            $totalSpent = $productTotal + $deliveryTotal;
            $loyaltyPoints = $customer->loyaltyPoints->where('type', 'earned')->sum('points') - 
                            $customer->loyaltyPoints->where('type', 'redeemed')->sum('points');

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
                'productTotal' => $productTotal,
                'deliveryTotal' => $deliveryTotal,
                'totalSpent' => $totalSpent,
                'loyaltyPoints' => $loyaltyPoints,
                'loyaltyTier' => $loyaltyTier,
                'loyaltyBadge' => $loyaltyBadge,
            ];
        });

        // Fetch all admins
        $admins = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistics
        $totalRiders = $riders->count();
        $totalCustomers = $customers->count();
        $totalAdmins = $admins->count();
        $activeRiders = $riders->filter(fn($r) => $r->rider?->availability === 'available')->count();

        return view('admin.users', compact(
            'riderStats',
            'customerStats',
            'admins',
            'totalRiders',
            'totalCustomers',
            'totalAdmins',
            'activeRiders'
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
            ->where('quantity_on_hand', '<=', 5)
            ->count();
        $lowStockAt = Inventory::query()
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where('price', '>', 0);
            })
            ->where('quantity_on_hand', '<=', 5)
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
            'gcash_image' => 'nullable|image|max:2048',
        ]);

        $homepageSettings = HomepageSetting::singleton();

        if ($request->hasFile('gcash_image')) {
            if (!empty($homepageSettings->gcash_image_path)) {
                Storage::delete($homepageSettings->gcash_image_path);
            }
            $validated['gcash_image_path'] = $request->file('gcash_image')->store('payment-methods');
        }

        unset($validated['gcash_image']);
        $homepageSettings->update($validated);

        return back()->with('success', 'GCash account details updated successfully.');
    }

    public function updatePaymentMethods(Request $request)
    {
        $validated = $request->validate([
            'payment_methods' => 'nullable|array',
            'payment_methods.*.label' => 'nullable|string|max:100',
            'payment_methods.*.account_name' => 'nullable|string|max:255',
            'payment_methods.*.account_number' => 'nullable|string|max:255',
            'payment_methods.*.existing_image' => 'nullable|string|max:255',
            'payment_methods.*.image' => 'nullable|image|max:2048',
        ]);

        $uploadedMethods = $request->file('payment_methods', []);
        $methods = collect($validated['payment_methods'] ?? [])
            ->map(function ($method, $index) use ($uploadedMethods) {
                $label = trim((string) ($method['label'] ?? ''));
                $key = Str::of($label)
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/', '_')
                    ->trim('_')
                    ->toString();

                if ($key === '' || in_array($key, ['cash', 'gcash'], true)) {
                    return null;
                }

                $imagePath = trim((string) ($method['existing_image'] ?? ''));
                $uploadedImage = data_get($uploadedMethods, $index . '.image');
                if ($uploadedImage) {
                    if ($imagePath !== '') {
                        Storage::delete($imagePath);
                    }
                    $imagePath = $uploadedImage->store('payment-methods');
                }

                return [
                    'key' => $key,
                    'label' => $label !== '' ? $label : Str::headline($key),
                    'account_name' => trim((string) ($method['account_name'] ?? '')),
                    'account_number' => trim((string) ($method['account_number'] ?? '')),
                    'image_path' => $imagePath ?: null,
                    'requires_proof' => true,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $homepageSettings = HomepageSetting::singleton();
        $homepageSettings->update(['payment_methods' => $methods]);

        return back()->with('success', 'Payment methods updated successfully.');
    }

    public function updateDeliveryFee(Request $request)
    {
        $validated = $request->validate([
            'delivery_fee' => 'required|numeric|min:0',
        ]);

        $homepageSettings = HomepageSetting::singleton();
        $homepageSettings->update(['delivery_fee' => $validated['delivery_fee']]);

        return back()->with('success', 'Delivery fee updated successfully.');
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
