<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Freebie;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
    public function checkout(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->guest(route('customer.login'))->with('error', 'Please log in to continue to checkout.');
        }

        // Redirect admins to their dashboard
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('info', 'Admins cannot access customer checkout.');
        }

        // Redirect riders to their dashboard
        if (Auth::user()->role === 'rider') {
            return redirect()->route('rider.dashboard')->with('info', 'Riders cannot access customer checkout.');
        }

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty.');
        }

        // Filter cart items by selected items from cart page
        $selectedIds = $request->input('selected_items', []);
        if (!empty($selectedIds)) {
            $cartItems = $cartItems->whereIn('product_id', $selectedIds);
        }

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'No items selected for checkout.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
        $productFreebieOffset = 1000000;

        $tableFreebies = Freebie::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $productFreebies = Product::query()
            ->with('inventory')
            ->where('is_active', true)
            ->where('category', 'freebie')
            ->whereHas('inventory', function ($query) {
                $query->where('status', 'active')
                    ->where('quantity_on_hand', '>', 0);
            })
            ->get()
            ->map(function ($product) use ($productFreebieOffset) {
                $product->id = $productFreebieOffset + (int) $product->id;
                $product->stock = (int) ($product->inventory->quantity_on_hand ?? 0);
                return $product;
            });

        $availableFreebies = $tableFreebies
            ->concat($productFreebies)
            ->sortBy('name')
            ->values();

        // Generate checkout freebie preview
        $rewardPreview = [
            'has_freebies' => false,
            'freebies' => [],
            'total_items' => 0,
            'small_reward_count' => 0,
        ];

        foreach ($cartItems as $item) {
            if (! $item->product) {
                continue;
            }

            $quantity = (int) $item->quantity;
            $rewardPreview['total_items'] += $quantity;

            if ($quantity >= 1 && $quantity <= 9) {
                // Small order freebie
                $rewardPreview['has_freebies'] = true;
                $rewardPreview['small_reward_count']++;
                $rewardPreview['freebies'][] = [
                    'product_name' => $item->product->name,
                    'quantity' => $quantity,
                    'freebie' => '1 Selected Freebie',
                ];
            }
        }

        return view('customer.checkout', compact('cartItems', 'subtotal', 'rewardPreview', 'availableFreebies'));
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
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'address_full'     => 'required|string|max:500',
            'selected_freebie_id' => 'nullable|integer',
        ]);

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty.');
        }

        $smallRewardCount = 0;
        foreach ($cartItems as $item) {
            $quantity = (int) $item->quantity;
            if ($quantity >= 1 && $quantity <= 9) {
                $smallRewardCount++;
            }
        }

        $productFreebieOffset = 1000000;
        $selectedFreebieId = isset($validated['selected_freebie_id']) ? (int) $validated['selected_freebie_id'] : null;
        $isProductFreebieSelection = $selectedFreebieId !== null && $selectedFreebieId >= $productFreebieOffset;
        $selectedProductFreebieId = $isProductFreebieSelection
            ? (int) ($selectedFreebieId - $productFreebieOffset)
            : null;

        if ($smallRewardCount > 0 && ! $selectedFreebieId) {
            throw ValidationException::withMessages([
                'selected_freebie_id' => 'Please select a freebie for your freebie item(s).',
            ]);
        }

        $order = DB::transaction(function () use ($validated, $cartItems, $smallRewardCount, $selectedFreebieId, $isProductFreebieSelection, $selectedProductFreebieId) {
            $subtotal = 0;
            $deliveryFee = 50.00;
            $orderItems = [];
            $hasRewardItems = false;
            $selectedFreebie = null;
            $selectedFreebieProduct = null;
            $selectedFreebieInventory = null;

            if ($smallRewardCount > 0) {
                if ($isProductFreebieSelection) {
                    $selectedFreebieProduct = Product::query()
                        ->whereKey($selectedProductFreebieId)
                        ->where('is_active', true)
                        ->where('category', 'freebie')
                        ->lockForUpdate()
                        ->first();

                    if (! $selectedFreebieProduct) {
                        throw ValidationException::withMessages([
                            'selected_freebie_id' => 'The selected freebie is no longer available.',
                        ]);
                    }

                    $selectedFreebieInventory = Inventory::query()
                        ->where('product_id', $selectedFreebieProduct->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $selectedFreebieInventory || $selectedFreebieInventory->status !== 'active') {
                        throw ValidationException::withMessages([
                            'selected_freebie_id' => 'The selected freebie is currently unavailable.',
                        ]);
                    }

                    if ((int) $selectedFreebieInventory->quantity_on_hand < $smallRewardCount) {
                        throw ValidationException::withMessages([
                            'selected_freebie_id' => 'Selected freebie has insufficient stock for this checkout.',
                        ]);
                    }
                } else {
                    $selectedFreebie = Freebie::query()
                        ->whereKey($selectedFreebieId)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $selectedFreebie) {
                        throw ValidationException::withMessages([
                            'selected_freebie_id' => 'The selected freebie is no longer available.',
                        ]);
                    }

                    if ((int) $selectedFreebie->stock < $smallRewardCount) {
                        throw ValidationException::withMessages([
                            'selected_freebie_id' => 'Selected freebie has insufficient stock for this checkout.',
                        ]);
                    }

                    $selectedFreebieProduct = Product::firstOrCreate(
                        ['name' => $selectedFreebie->name],
                        [
                            'description' => $selectedFreebie->description,
                            'price' => 0.00,
                            'stock' => max(999, (int) $selectedFreebie->stock),
                            'weight' => 'reward',
                            'image' => $selectedFreebie->image,
                            'is_active' => true,
                        ]
                    );

                    if (! $selectedFreebieProduct->is_active) {
                        $selectedFreebieProduct->is_active = true;
                        $selectedFreebieProduct->save();
                    }
                }
            }

            foreach ($cartItems as $item) {
                $product = Product::query()
                    ->with('inventory')
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the products in your cart is no longer available.',
                    ]);
                }

                $inventory = Inventory::query()
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory || $inventory->status !== 'active') {
                    throw ValidationException::withMessages([
                        'cart' => $product->name . ' is currently unavailable for ordering.',
                    ]);
                }

                if ((int) $inventory->quantity_on_hand < (int) $item->quantity) {
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

                // Small-order freebies are customer-selected.
                if ($quantity >= 1 && $quantity <= 9) {
                    $orderItems[] = [
                        'product_id'   => $selectedFreebieProduct->id,
                        'product_name' => $selectedFreebieProduct->name,
                        'quantity'     => 1,
                        'price'        => 0,
                        'subtotal'     => 0,
                        'is_reward'    => true,
                    ];
                    $hasRewardItems = true;
                }

                // Deduct stock only for regular items
                $inventory->decrement('quantity_on_hand', $quantity);
            }

            if ($smallRewardCount > 0) {
                if ($selectedFreebie) {
                    $selectedFreebie->decrement('stock', $smallRewardCount);
                }

                if ($selectedFreebieInventory) {
                    $selectedFreebieInventory->decrement('quantity_on_hand', $smallRewardCount);
                }
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

    // Customer: cancel order before admin approval
    public function cancelByCustomer(Order $order)
    {
        if (! Auth::check() || $order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('customer.tracking', $order)
                ->with('error', 'Only pending orders can be cancelled before admin approval.');
        }

        DB::transaction(function () use ($order) {
            $order->loadMissing('orderItems');

            foreach ($order->orderItems as $item) {
                if ($item->is_reward) {
                    $freebie = Freebie::query()
                        ->where('name', $item->product_name)
                        ->first();

                    if ($freebie) {
                        $freebie->increment('stock', (int) $item->quantity);
                    }

                    continue;
                }

                if ($item->product_id) {
                    Inventory::query()
                        ->where('product_id', $item->product_id)
                        ->increment('quantity_on_hand', (int) $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);
        });

        return redirect()->route('customer.orders')
            ->with('success', 'Order cancelled successfully.');
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
