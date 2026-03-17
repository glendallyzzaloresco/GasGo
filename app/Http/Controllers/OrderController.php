<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    // Customer: view order history
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in to view your orders.');
        }

        $orders = Order::with('orderItems.product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.orders', compact('orders'));
    }

    // Customer: show checkout form
    public function checkout()
    {
        if (! Auth::check()) {
            return redirect()->guest(route('customer.login'))->with('error', 'Please log in to continue to checkout.');
        }

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

        // Generate reward preview based on tiered loyalty system
        $rewardPreview = [
            'has_rewards' => false,
            'bulk_rewards' => [],
            'small_rewards' => [],
            'total_items' => 0,
        ];

        foreach ($cartItems as $item) {
            if (! $item->product) {
                continue;
            }

            $quantity = (int) $item->quantity;
            $rewardPreview['total_items'] += $quantity;

            if ($quantity >= 10) {
                // Tier A: Bulk order
                $rewardPreview['has_rewards'] = true;
                $rewardPreview['bulk_rewards'][] = [
                    'product_name' => $item->product->name,
                    'quantity' => $quantity,
                    'reward' => '1 Free LPG Tank',
                ];
            } elseif ($quantity >= 1 && $quantity <= 9) {
                // Tier B: Small order
                $rewardPreview['has_rewards'] = true;
                $rewardPreview['small_rewards'][] = [
                    'product_name' => $item->product->name,
                    'quantity' => $quantity,
                    'reward' => '1 Free Freebie (Paste or Hanger)',
                ];
            }
        }

        return view('customer.checkout', compact('cartItems', 'subtotal', 'rewardPreview'));
    }

    // Customer: place an order from cart
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->guest(route('customer.login'))->with('error', 'Please log in before placing an order.');
        }

        $validated = $request->validate([
            'delivery_address' => 'required|string|max:500',
            'contact_number'   => 'required|string|max:20',
            'payment_method'   => 'required|in:cash,gcash',
            'notes'            => 'nullable|string|max:500',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
        ]);

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty.');
        }

        // Ensure reward products exist BEFORE transaction
        $rewardLpgTank = \App\Models\Product::firstOrCreate(
            ['name' => 'Free LPG Tank (Reward)'],
            [
                'description' => 'Complimentary LPG Tank - Loyalty Reward for Bulk Orders',
                'price' => 0.00,
                'stock' => 999,
                'weight' => '11kg',
                'is_active' => true,
            ]
        );

        $rewardDishPaste = \App\Models\Product::firstOrCreate(
            ['name' => 'Dish Washer Paste (Freebie)'],
            [
                'description' => 'Free Dish Washer Paste - Small Order Loyalty Reward',
                'price' => 0.00,
                'stock' => 999,
                'weight' => '0.2kg',
                'is_active' => true,
            ]
        );

        $rewardClothHanger = \App\Models\Product::firstOrCreate(
            ['name' => 'Cloth Hanger Set (Freebie)'],
            [
                'description' => 'Free Cloth Hanger Set - Small Order Loyalty Reward',
                'price' => 0.00,
                'stock' => 999,
                'weight' => '0.1kg',
                'is_active' => true,
            ]
        );

        $order = DB::transaction(function () use ($validated, $cartItems, $rewardLpgTank, $rewardDishPaste, $rewardClothHanger) {
            $subtotal = 0;
            $deliveryFee = 50.00;
            $orderItems = [];
            $hasRewardItems = false;

            foreach ($cartItems as $item) {
                $product = \App\Models\Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the products in your cart is no longer available.',
                    ]);
                }

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'Insufficient stock for ' . $product->name . '. Please update your cart quantity.',
                    ]);
                }

                $lineSubtotal = (float) $product->price * (int) $item->quantity;
                $subtotal += $lineSubtotal;

                $quantity = (int) $item->quantity;

                // Add regular paid item
                $orderItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'quantity'     => $quantity,
                    'price'        => $product->price,
                    'subtotal'     => $lineSubtotal,
                    'is_reward'    => false,
                ];

                // Tiered Loyalty System - Add reward item if applicable
                if ($quantity >= 10) {
                    // Tier A (Bulk): >= 10 items → Add 1 Free LPG Tank
                    $orderItems[] = [
                        'product_id'   => $rewardLpgTank->id,
                        'product_name' => $rewardLpgTank->name,
                        'quantity'     => 1,
                        'price'        => 0,
                        'subtotal'     => 0,
                        'is_reward'    => true,
                    ];
                    $hasRewardItems = true;
                } elseif ($quantity >= 1 && $quantity <= 9) {
                    // Tier B (Small): 1-9 items → Add 1 Small Freebie (random)
                    $freebieProduct = (rand(1, 2) === 1) ? $rewardDishPaste : $rewardClothHanger;
                    
                    $orderItems[] = [
                        'product_id'   => $freebieProduct->id,
                        'product_name' => $freebieProduct->name,
                        'quantity'     => 1,
                        'price'        => 0,
                        'subtotal'     => 0,
                        'is_reward'    => true,
                    ];
                    $hasRewardItems = true;
                }

                // Deduct stock only for regular items
                $product->decrement('stock', $quantity);
            }

            $totalAmount = $subtotal + $deliveryFee;

            // Create order
            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_number'     => 'GG-' . strtoupper(Str::random(8)),
                'subtotal'         => $subtotal,
                'discount'         => 0,
                'delivery_fee'     => $deliveryFee,
                'total_amount'     => $totalAmount,
                'delivery_address' => $validated['delivery_address'],
                'contact_number'   => $validated['contact_number'],
                'latitude'         => $validated['latitude'] ?? null,
                'longitude'        => $validated['longitude'] ?? null,
                'payment_method'   => $validated['payment_method'],
                'status'           => 'pending',
                'notes'            => $validated['notes'] ?? null,
            ]);

            // Create all order items
            foreach ($orderItems as $itemData) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $itemData['product_id'],
                    'product_name' => $itemData['product_name'],
                    'quantity'     => $itemData['quantity'],
                    'price'        => $itemData['price'],
                    'subtotal'     => $itemData['subtotal'],
                    'is_reward'    => $itemData['is_reward'],
                ]);
            }

            // Clear cart
            Cart::where('user_id', Auth::id())->delete();

            // Send admin notification if order has rewards
            if ($hasRewardItems) {
                $adminUser = \App\Models\User::where('role', 'admin')->first();
                if ($adminUser) {
                    $adminUser->notify(new \App\Notifications\OrderPlacedNotification($order, true));
                }
            }

            return $order;
        });

        return redirect()->route('customer.orders')->with('success', 'Order placed successfully! Order #' . $order->order_number);
    }

    // Customer: track a specific order
    public function track(Order $order)
    {
        if (! Auth::check() || $order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['orderItems.product', 'delivery.rider']);

        return view('customer.tracking', compact('order'));
    }

    // Customer: get live tracking status (JSON for polling)
    public function trackingStatus(Order $order)
    {
        if (! Auth::check() || $order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->load('delivery.rider');

        $riderName = null;
        $riderPhone = null;
        $riderLat = null;
        $riderLng = null;
        $waypoints = [];

        if ($order->delivery && $order->delivery->rider) {
            $riderName = $order->delivery->rider->name;
            $riderPhone = $order->delivery->rider->phone;
            $riderLat = $order->delivery->latitude;
            $riderLng = $order->delivery->longitude;

            // Get all active deliveries for this rider (waypoints)
            $allDeliveries = \App\Models\Delivery::with('order')
                ->where('rider_id', $order->delivery->rider_id)
                ->whereNotIn('status', ['delivered', 'failed'])
                ->orderBy('assigned_at', 'asc')
                ->get();

            $waypoints = $allDeliveries->map(function($delivery, $index) use ($order) {
                return [
                    'id' => $delivery->id,
                    'order_number' => $delivery->order->order_number,
                    'customer' => $delivery->order->user->name,
                    'address' => $delivery->order->delivery_address,
                    'latitude' => $delivery->order->latitude,
                    'longitude' => $delivery->order->longitude,
                    'status' => $delivery->status,
                    'amount' => $delivery->order->total_amount,
                    'sequence' => $index + 1,
                    'is_current' => $delivery->id === $order->delivery->id,
                ];
            })->values()->all();
        }

        return response()->json([
            'status' => $order->status,
            'rider_name' => $riderName,
            'rider_phone' => $riderPhone,
            'rider_lat' => $riderLat,
            'rider_lng' => $riderLng,
            'estimated_delivery' => $order->estimated_delivery_time?->format('g:i A'),
            'delivered_at' => $order->delivered_at?->format('M j, Y — g:i A'),
            'waypoints' => $waypoints,
            'waypoints_count' => count($waypoints),
        ]);
    }

    // Admin: list all orders
    public function adminIndex()
    {
        $orders = Order::with(['user', 'orderItems.product', 'delivery.rider'])
            ->orderBy('created_at', 'desc')
            ->get();

        $riders = \App\Models\Rider::with('user')
            ->where('availability', '!=', 'offline')
            ->get();

        return view('admin.orders', compact('orders', 'riders'));
    }

    // Admin: show single order details
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product', 'delivery', 'payment']);

        return view('admin.order-detail', compact('order'));
    }

    // Admin: update order status
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,assigned,out_for_delivery,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        if ($validated['status'] === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Order status updated.',
                'order_id' => $order->id,
                'status' => $order->status,
            ]);
        }

        return redirect()->back()->with('success', 'Order status updated.');
    }
}
