<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\RiderController;
use App\Http\Controllers\LoyaltyController;

Route::get('/', function () {
    $totalOrders = \App\Models\Order::count();
    $revenue = \App\Models\Order::whereHas('delivery', function ($query) {
        $query->where('status', 'delivered');
    })->sum('total_amount');
    $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
    $totalCustomers = \App\Models\User::where('role', 'customer')->count();
    $activeRiders = \App\Models\Rider::whereIn('availability', ['available', 'busy'])->count();
    $products = \App\Models\Product::all();

    $user = \Illuminate\Support\Facades\Auth::user();
    $role = $user?->role ?? null;

    // Admin-specific data
    $ordersAwaitingAssignment = [];
    $availableRiders = [];
    if ($role === 'admin') {
        $ordersAwaitingAssignment = \App\Models\Order::with('user', 'orderItems.product')
            ->where('status', 'pending')
            ->doesntHave('delivery')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $availableRiders = \App\Models\User::where('role', 'rider')
            ->with('rider')
            ->get();
    }

    // Rider-specific data
    $riderAssignedOrders = [];
    if ($role === 'rider') {
        $riderAssignedOrders = \App\Models\Delivery::with('order.user', 'order.orderItems.product')
            ->where('rider_id', $user->id)
            ->whereNotIn('status', ['delivered', 'failed'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    return view('welcome', compact(
        'totalOrders', 'revenue', 'pendingOrders', 'totalCustomers', 
        'activeRiders', 'products', 'user', 'role', 
        'ordersAwaitingAssignment', 'availableRiders', 'riderAssignedOrders'
    ));
});

// ===== GUEST / AUTH ROUTES =====
Route::get('/login', [CustomerController::class, 'login'])->name('login'); // Required by auth middleware
Route::get('/customer/loginRegistration', [CustomerController::class, 'login'])->name('customer.login');
Route::post('/customer/login', [CustomerController::class, 'authenticate'])->name('customer.authenticate');
Route::post('/customer/register', [CustomerController::class, 'register'])->name('customer.register');
Route::post('/customer/logout', [CustomerController::class, 'logout'])->name('customer.logout');
Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');

// ===== GOOGLE OAUTH ROUTES =====
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// ===== CUSTOMER ROUTES =====
Route::prefix('customer')->group(function () {
    Route::get('/customerDashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');

    // Products
    Route::get('/product', [ProductController::class, 'index'])->name('customer.products');
    Route::get('/product/{product}', [ProductController::class, 'show'])->name('customer.product.show');

    // Cart
    Route::get('/productCart', [CartController::class, 'index'])->name('customer.cart');
    Route::post('/cart', [CartController::class, 'store'])->name('customer.cart.store');
    Route::post('/cart/sync', [CartController::class, 'sync'])->name('customer.cart.sync');
    Route::post('/cart/item/update', [CartController::class, 'updateItem'])->name('customer.cart.item.update');
    Route::post('/cart/item/remove', [CartController::class, 'destroyItem'])->name('customer.cart.item.destroy');
    Route::put('/cart/{cart}', [CartController::class, 'update'])->name('customer.cart.update');
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('customer.cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('customer.cart.clear');

    // Orders
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('customer.checkout');
    Route::post('/order', [OrderController::class, 'store'])->name('customer.order.store');
    Route::get('/orderHistory', [OrderController::class, 'index'])->name('customer.orders');
    Route::get('/tracking/{order}', [OrderController::class, 'track'])->name('customer.tracking');
    Route::get('/tracking/{order}/status', [OrderController::class, 'trackingStatus'])->name('customer.tracking.status');

    // Account
    Route::get('/profile', [CustomerController::class, 'profile'])->name('customer.profile');
    Route::put('/profile', [CustomerController::class, 'updateProfile'])->name('customer.profile.update');

    // Loyalty
    Route::get('/loyaltyRewards', [LoyaltyController::class, 'index'])->name('customer.loyalty');
    Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeem'])->name('customer.loyalty.redeem');
});

// ===== ADMIN ROUTES =====
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('admin.notifications');

    // Orders
    Route::get('/orders', [OrderController::class, 'adminIndex'])->name('admin.orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');

    // Products
    Route::get('/products', [ProductController::class, 'adminIndex'])->name('admin.products');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

    // Riders
    Route::get('/riders', [RiderController::class, 'adminIndex'])->name('admin.riders');
    Route::post('/riders', [RiderController::class, 'storeRider'])->name('admin.riders.store');
    Route::get('/riders/{user}', [RiderController::class, 'show'])->name('admin.riders.show');
    Route::put('/riders/{rider}/availability', [RiderController::class, 'updateAvailability'])->name('admin.riders.availability');
    Route::delete('/riders/{rider}', [RiderController::class, 'destroy'])->name('admin.riders.destroy');

    // Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('admin.deliveries');
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('admin.deliveries.store');
    Route::put('/deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('admin.deliveries.status');

    // Loyalty / Rewards
    Route::get('/rewards', [LoyaltyController::class, 'adminIndex'])->name('admin.rewards');

    // Reports & Customers (static views for now)
    Route::get('/reports', [DashboardController::class, 'reports'])->name('admin.reports');
    Route::get('/customers', [DashboardController::class, 'customers'])->name('admin.customers');

    // Settings / Maintenance
    Route::get('/settings', [DashboardController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return back()->with('success', 'Cache cleared successfully.');
    })->name('admin.settings.clear-cache');
    Route::post('/settings/clear-logs', function () {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) { file_put_contents($logPath, ''); }
        return back()->with('success', 'Log file cleared successfully.');
    })->name('admin.settings.clear-logs');
    Route::get('/settings/log-tail', function () {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) { return response('Log file is empty.', 200); }
        $lines = array_slice(file($logPath), -50);
        return response(implode('', $lines), 200)->header('Content-Type', 'text/plain');
    })->name('admin.settings.log-tail');
});

// ===== RIDER ROUTES =====
Route::prefix('rider')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [RiderController::class, 'dashboard'])->name('rider.dashboard');
    Route::get('/profile', [RiderController::class, 'profile'])->name('rider.profile');
    Route::put('/profile', [RiderController::class, 'updateProfile'])->name('rider.profile.update');
    Route::get('/history', [DeliveryController::class, 'riderHistory'])->name('rider.history');
    Route::get('/route', [RiderController::class, 'route'])->name('rider.route');
    Route::get('/route/live-map', [RiderController::class, 'liveRouteMap'])->name('rider.route.map');
    Route::get('/route/waypoints', [RiderController::class, 'routeWaypoints'])->name('rider.route.waypoints');
    Route::post('/orders/{order}/accept', [RiderController::class, 'acceptOrder'])->name('rider.order.accept');

    // Active delivery
    Route::get('/delivery/{delivery}', [DeliveryController::class, 'show'])->name('rider.delivery');
    Route::get('/delivery/{delivery}/location', [DeliveryController::class, 'getLocation'])->name('rider.delivery.location');
    Route::put('/delivery/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('rider.delivery.status');
    Route::put('/delivery/{delivery}/location', [DeliveryController::class, 'updateLocation'])->name('rider.delivery.location.update');
    Route::post('/delivery/{delivery}/proof', [DeliveryController::class, 'uploadProof'])->name('rider.delivery.proof');
});
