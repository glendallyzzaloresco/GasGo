<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Freebie;
use App\Models\HomepageSetting;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        // Filter cart items by selected product IDs from cart page / Buy Now flow
        $selectedIds = $request->input('selected_items', []);
        $selectedIds = is_array($selectedIds)
            ? array_filter(array_map('intval', $selectedIds))
            : array_filter(array_map('intval', explode(',', (string) $selectedIds)));

        if (!empty($selectedIds)) {
            $cartItems = $cartItems->whereIn('product_id', $selectedIds);
        }

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'No items selected for checkout.');
        }

        // Check if order contains any tank products
        $hasTankProducts = $cartItems->contains(function ($item) {
            return $item->product && strtolower($item->product->category) === 'tank';
        });

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

        $allFreebies = $tableFreebies
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

        // Calculate total items in this checkout order
        $totalCheckoutItems = $rewardPreview['total_items'];

        // Separate freebies into unlocked (available) and locked (requires more items)
        // Only show freebies with required points if order contains tanks
        $unlockedFreebies = $allFreebies->filter(function ($freebie) use ($totalCheckoutItems, $hasTankProducts) {
            // If freebie has required points and order has no tanks, exclude it
            if ($freebie->reward_points_required > 0 && !$hasTankProducts) {
                return false;
            }
            return $freebie->reward_points_required <= $totalCheckoutItems;
        })->values();

        $lockedFreebies = $allFreebies->filter(function ($freebie) use ($totalCheckoutItems, $hasTankProducts) {
            // If freebie has required points and order has no tanks, exclude it
            if ($freebie->reward_points_required > 0 && !$hasTankProducts) {
                return false;
            }
            return $freebie->reward_points_required > $totalCheckoutItems;
        })->values();

        // Combine: show unlocked first, then locked
        $availableFreebies = $unlockedFreebies->concat($lockedFreebies)->values();

        $homepageSettings = HomepageSetting::singleton();
        
        // Get available voucher (only one - the one expiring soonest)
        // Only show vouchers that are:
        // 1. Not used (is_used = false)
        // 2. Not tied to any pending/active order (order_id = null or order is cancelled/delivered)
        // 3. Not expired
        $availableVouchers = \App\Models\UserVoucher::where('user_id', Auth::id())
            ->where('is_used', false)
            ->where(function ($query) {
                // Either no order is tied to it, or the order is already completed/cancelled
                $query->whereNull('order_id')
                      ->orWhereHas('order', function ($q) {
                          $q->whereIn('status', ['delivered', 'cancelled']);
                      });
            })
            ->where('expires_at', '>', now())
            ->orderBy('expires_at', 'asc')
            ->take(1)
            ->get();

        return view('customer.checkout', compact('cartItems', 'subtotal', 'rewardPreview', 'availableFreebies', 'unlockedFreebies', 'lockedFreebies', 'totalCheckoutItems', 'homepageSettings', 'availableVouchers'));
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
            'is_urgent'        => 'nullable|boolean',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'address_full'     => 'nullable|string|max:500',
            'selected_freebie_id' => 'nullable|integer',
            'voucher_id' => 'nullable|integer|exists:user_vouchers,id',
            'selected_cart_ids' => 'nullable|string',
            'proof_of_payment'  => $request->input('payment_method') === 'gcash' ? 'required|image|mimes:jpeg,png,gif,jpg|max:5120' : 'nullable|image|mimes:jpeg,png,gif,jpg|max:5120',
        ]);

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty.');
        }

        // Filter cartItems to only include selected ones
        if (!empty($validated['selected_cart_ids'])) {
            $selectedIds = array_filter(array_map('intval', explode(',', $validated['selected_cart_ids'])));
            $cartItems = $cartItems->filter(function ($item) use ($selectedIds) {
                return in_array($item->id, $selectedIds);
            });

            if ($cartItems->isEmpty()) {
                return redirect()->route('customer.checkout')->with('error', 'No items selected for checkout.');
            }
        }

        // Check if all items have sufficient stock
        foreach ($cartItems as $item) {
            $product = $item->product;
            $requestedQty = (int) $item->quantity;
            $availableStock = $product->quantity_on_hand ?? 0;
            
            if ($requestedQty > $availableStock) {
                return redirect()->route('customer.checkout')->with(
                    'error', 
                    "Insufficient stock for {$product->name}. Only {$availableStock} available, but you requested {$requestedQty}."
                );
            }
        }

        $smallRewardCount = 0;  // For freebies with required points (qty of tank products 1-9)
        $freeRewardQuantity = 0; // For freebies with no required points (total quantity)
        $hasTankInOrder = false;
        foreach ($cartItems as $item) {
            $quantity = (int) $item->quantity;
            $totalQuantity = $quantity;
            $freeRewardQuantity += $totalQuantity; // Count all quantities for no-point freebies
            
            // Only count tank products with qty 1-9 for freebie qualification
            if ($item->product && strtolower($item->product->category) === 'tank' && $quantity >= 1 && $quantity <= 9) {
                $smallRewardCount++;
                $hasTankInOrder = true;
            }
        }
        
        // Check if selected freebie requires points - only clear if it DOES have required points and no tanks
        $selectedFreebieId = isset($validated['selected_freebie_id']) ? (int) $validated['selected_freebie_id'] : null;
        if ($selectedFreebieId !== null && !$hasTankInOrder) {
            $productFreebieOffset = 1000000;
            $isProductFreebie = $selectedFreebieId >= $productFreebieOffset;
            
            if ($isProductFreebie) {
                // Product freebie - check from products (assume no required points)
                // Product freebies have no required points, so don't clear them
            } else {
                // Table freebie - check required points
                $freebieCheck = Freebie::find($selectedFreebieId);
                if ($freebieCheck && $freebieCheck->reward_points_required > 0) {
                    // Has required points and no tanks - clear it
                    $validated['selected_freebie_id'] = null;
                }
                // If no required points, keep it selected
            }
        }

        $productFreebieOffset = 1000000;
        $selectedFreebieId = isset($validated['selected_freebie_id']) ? (int) $validated['selected_freebie_id'] : null;
        $selectedVoucherId = isset($validated['voucher_id']) ? (int) $validated['voucher_id'] : null;
        
        // Validate voucher is still available (not used, not expired, belongs to user)
        if ($selectedVoucherId !== null) {
            $voucherCheck = \App\Models\UserVoucher::where('id', $selectedVoucherId)
                ->where('user_id', Auth::id())
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->first();
            
            if (!$voucherCheck) {
                return redirect()->route('customer.checkout')
                    ->with('error', 'The selected voucher is no longer available, has already been used, or has expired.');
            }
        }
        
        $isProductFreebieSelection = $selectedFreebieId !== null && $selectedFreebieId >= $productFreebieOffset;
        $selectedProductFreebieId = $isProductFreebieSelection
            ? (int) ($selectedFreebieId - $productFreebieOffset)
            : null;

        // Determine if selected freebie requires points
        $freebieRequiresPoints = false;
        if ($selectedFreebieId !== null && !$isProductFreebieSelection) {
            $freebieCheck = Freebie::find($selectedFreebieId);
            if ($freebieCheck) {
                $freebieRequiresPoints = $freebieCheck->reward_points_required > 0;
            }
        }

        // Get selected cart IDs for deletion after order is placed
        $selectedCartIds = [];
        if (!empty($validated['selected_cart_ids'])) {
            $selectedCartIds = array_filter(array_map('intval', explode(',', $validated['selected_cart_ids'])));
        }

        $order = DB::transaction(function () use ($validated, $cartItems, $smallRewardCount, $freeRewardQuantity, $selectedFreebieId, $isProductFreebieSelection, $selectedProductFreebieId, $selectedCartIds, $selectedVoucherId, $hasTankInOrder, $freebieRequiresPoints, $request) {
            $subtotal = 0;
            $deliveryFee = 50.00;
            $orderItems = [];
            $hasRewardItems = false;
            $selectedFreebie = null;
            $selectedFreebieProduct = null;
            $selectedFreebieInventory = null;

            // Check if freebie should be processed
            $shouldProcessFreebie = false;
            $freebieRequiresPoints = false;
            
            if ($selectedFreebieId !== null) {
                if ($isProductFreebieSelection) {
                    // Product freebies don't have required points, always allow
                    $shouldProcessFreebie = true;
                } else {
                    // Check table freebie's required points
                    $freebieCheck = Freebie::find($selectedFreebieId);
                    if ($freebieCheck) {
                        $freebieRequiresPoints = $freebieCheck->reward_points_required > 0;
                        
                        if ($freebieRequiresPoints) {
                            // Requires points - must have tanks
                            $shouldProcessFreebie = $hasTankInOrder && $smallRewardCount > 0;
                        } else {
                            // No required points - always allow
                            $shouldProcessFreebie = true;
                        }
                    }
                }
            }

            if ($shouldProcessFreebie && $selectedFreebieId !== null) {
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

                    $requiredQuantity = $freebieRequiresPoints ? $smallRewardCount : $freeRewardQuantity;
                    if ((int) $selectedFreebieInventory->quantity_on_hand < $requiredQuantity) {
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

                    $requiredQuantity = $freebieRequiresPoints ? $smallRewardCount : $freeRewardQuantity;
                    if ((int) $selectedFreebie->stock < $requiredQuantity) {
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

            // Track if freebie has been added (only add once)
            $freebieAdded = false;

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

                // Add freebie: 
                // - If no required points: quantity = total quantity ordered
                // - If has required points: quantity = 1 per tank product (1-9)
                $isTankProduct = $product && strtolower($product->category) === 'tank';
                $shouldAddFreebie = false;
                $freebieQuantityToAdd = 0;
                
                if ($selectedFreebieProduct !== null && !$freebieAdded) {
                    if ($freebieRequiresPoints) {
                        // Requires points - only add on tank products with qty 1-9
                        if ($isTankProduct && $quantity >= 1 && $quantity <= 9) {
                            $shouldAddFreebie = true;
                            $freebieQuantityToAdd = 1; // One freebie per qualifying item count
                        }
                    } else {
                        // No required points - add on first iteration with total quantity
                        $shouldAddFreebie = true;
                        $freebieQuantityToAdd = $freeRewardQuantity; // All ordered quantities
                    }
                }
                
                if ($shouldAddFreebie) {
                    // Resolve freebie image URL - use same logic as checkout page
                    $freebieImagePath = $selectedFreebie?->image ?? $selectedFreebieProduct?->image;
                    $freebieImageUrl = null;
                    
                    if ($freebieImagePath) {
                        $normalized = ltrim($freebieImagePath, '/');
                        if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                            $freebieImageUrl = $freebieImagePath;
                        } elseif (str_starts_with($normalized, 'storage/') || str_starts_with($normalized, 'images/')) {
                            $freebieImageUrl = asset($normalized);
                        } else {
                            $freebieImageUrl = asset('storage/' . $normalized);
                        }
                    }
                    
                    $orderItems[] = [
                        'product_id'      => $selectedFreebieProduct->id,
                        'product_name'    => $selectedFreebieProduct->name,
                        'quantity'        => $freebieQuantityToAdd,
                        'price'           => 0,
                        'subtotal'        => 0,
                        'is_reward'       => true,
                        'reward_image_url' => $freebieImageUrl,
                    ];
                    $hasRewardItems = true;
                    $freebieAdded = true; // Mark that freebie has been added
                }

                // Deduct stock only for regular items
                $inventory->decrement('quantity_on_hand', $quantity);
            }

            // Deduct freebie stock based on quantity given
            if ($freebieAdded) {
                $deductQuantity = $freebieRequiresPoints ? $smallRewardCount : $freeRewardQuantity;
                if ($selectedFreebie) {
                    $selectedFreebie->decrement('stock', $deductQuantity);
                }

                if ($selectedFreebieInventory) {
                    $selectedFreebieInventory->decrement('quantity_on_hand', $deductQuantity);
                }
            }

            // Calculate voucher discount
            $voucherDiscount = 0;
            $selectedVoucher = null;
            if ($selectedVoucherId !== null) {
                $selectedVoucher = \App\Models\UserVoucher::where('id', $selectedVoucherId)
                    ->where('user_id', Auth::id())
                    ->where('is_used', false)
                    ->where('expires_at', '>', now())
                    ->first();
                
                if ($selectedVoucher) {
                    $voucherDiscount = $selectedVoucher->discount_amount;
                }
            }

            $totalAmount = $subtotal + $deliveryFee - $voucherDiscount;

            // Create order
            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_number'     => 'GG-' . strtoupper(Str::random(8)),
                'subtotal'         => $subtotal,
                'discount'         => $voucherDiscount,
                'delivery_fee'     => $deliveryFee,
                'total_amount'     => max(0, $totalAmount), // Ensure no negative totals
                'delivery_address' => $validated['delivery_address'],
                'contact_number'   => $validated['contact_number'],
                'latitude'         => $validated['latitude'] ?? null,
                'longitude'        => $validated['longitude'] ?? null,
                'payment_method'   => $validated['payment_method'],
                'status'           => 'pending',
                'notes'            => $validated['notes'] ?? null,
                'is_urgent'        => $validated['is_urgent'] ?? false,
            ]);

            // Create all order items
            foreach ($orderItems as $itemData) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $itemData['product_id'],
                    'product_name'      => $itemData['product_name'],
                    'quantity'          => $itemData['quantity'],
                    'price'             => $itemData['price'],
                    'subtotal'          => $itemData['subtotal'],
                    'is_reward'         => $itemData['is_reward'],
                    'reward_image_url'  => $itemData['reward_image_url'] ?? null,
                ]);
            }

            // Mark voucher as used if one was selected
            if ($selectedVoucher) {
                $selectedVoucher->update([
                    'is_used' => true,
                    'order_id' => $order->id,
                    'applied_at' => now(),
                ]);
            }

            // Create Payment record
            $proofOfPaymentPath = null;
            if (isset($validated['proof_of_payment']) && $request->file('proof_of_payment')) {
                // Store the proof of payment file
                $file = $request->file('proof_of_payment');
                $fileName = 'proof_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $proofOfPaymentPath = $file->storeAs('payments/proofs', $fileName, 'public');
            }

            Payment::create([
                'order_id'           => $order->id,
                'payment_method'     => $validated['payment_method'],
                'amount'             => $totalAmount,
                'status'             => 'pending',
                'transaction_reference' => null,
                'proof_of_payment'   => $proofOfPaymentPath,
                'paid_at'            => null,
            ]);

            // Clear selected cart items only
            if (!empty($selectedCartIds)) {
                Cart::where('user_id', Auth::id())->whereIn('id', $selectedCartIds)->delete();
            } else {
                // If no specific items selected, clear all cart items (backward compatibility)
                Cart::where('user_id', Auth::id())->delete();
            }

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

            // Revert any used vouchers back to unused state
            \App\Models\UserVoucher::where('order_id', $order->id)
                ->where('is_used', true)
                ->update([
                    'is_used' => false,
                    'order_id' => null,
                    'applied_at' => null,
                ]);

            $order->update(['status' => 'cancelled']);

            // Update payment status to failed when order is cancelled
            Payment::where('order_id', $order->id)->update(['status' => 'failed']);
        });

        return redirect()->route('customer.orders')
            ->with('success', 'Order cancelled successfully.');
    }

    // Admin: list all orders
    public function adminIndex()
    {
        $orders = Order::with(['user', 'orderItems.product', 'delivery.rider'])
            ->orderByRaw("CASE WHEN status IN ('pending', 'approved', 'assigned', 'out_for_delivery') THEN 0 ELSE 1 END")
            ->orderBy('is_urgent', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $riders = \App\Models\Rider::with('user')
            ->where('availability', 'available')
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

        DB::transaction(function () use ($order, $validated) {
            // If order is being cancelled, revert any used vouchers and update payment status to failed
            if ($validated['status'] === 'cancelled') {
                \App\Models\UserVoucher::where('order_id', $order->id)
                    ->where('is_used', true)
                    ->update([
                        'is_used' => false,
                        'order_id' => null,
                        'applied_at' => null,
                    ]);

                // Update payment status to failed when order is cancelled
                Payment::where('order_id', $order->id)->update(['status' => 'failed']);
            }

            $order->update(['status' => $validated['status']]);

            if ($validated['status'] === 'delivered') {
                $order->update(['delivered_at' => now()]);
                // Update payment status to paid when order is delivered
                Payment::where('order_id', $order->id)->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        });

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
