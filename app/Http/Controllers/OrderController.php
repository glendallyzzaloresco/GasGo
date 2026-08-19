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
use App\Services\OrderInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    // Customer: view order history
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in to view your orders.');
        }

        $hasReviewTable = \Illuminate\Support\Facades\Schema::hasTable('service_reviews');
        $query = Order::with('orderItems.product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($hasReviewTable) {
            $query->with('serviceReview');
        }

        $orders = $query->get();

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

        if (! empty($selectedIds)) {
            $request->session()->put('checkout_selected_items', array_values(array_unique($selectedIds)));
        } else {
            $selectedIds = array_filter(array_map('intval', (array) $request->session()->get('checkout_selected_items', [])));
        }

        if (!empty($selectedIds)) {
            $cartItems = $cartItems->whereIn('product_id', $selectedIds);
        }

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'No items selected for checkout.');
        }

        // Check if order contains any cylinder products
        $hasCylinderProducts = $cartItems->contains(function ($item) {
            return $item->product?->isCylinder();
        });

        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
        $deliveryFee = HomepageSetting::singleton()->delivery_fee ?? 50.00;
        $productFreebieOffset = 1000000;

        $tableFreebies = Freebie::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $productFreebies = Product::query()
            ->with('inventory')
            ->where('is_active', true)
            ->where('price', 0)
            ->whereHas('inventory', function ($query) {
                $query->where('quantity_on_hand', '>', 0);
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
        // Only show freebies with required points if order contains cylinder products
        $unlockedFreebies = $allFreebies->filter(function ($freebie) use ($totalCheckoutItems, $hasCylinderProducts) {
            // If freebie has required points and order contains no cylinder products, exclude it
            if ($freebie->reward_points_required > 0 && !$hasCylinderProducts) {
                return false;
            }
            return $freebie->reward_points_required <= $totalCheckoutItems;
        })->values();

        $lockedFreebies = $allFreebies->filter(function ($freebie) use ($totalCheckoutItems, $hasCylinderProducts) {
            // If freebie has required points and order contains no cylinder products, exclude it
            if ($freebie->reward_points_required > 0 && !$hasCylinderProducts) {
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

        return view('customer.checkout', compact('cartItems', 'subtotal', 'deliveryFee', 'rewardPreview', 'availableFreebies', 'unlockedFreebies', 'lockedFreebies', 'totalCheckoutItems', 'homepageSettings', 'availableVouchers', 'hasCylinderProducts'));
    }

    // Customer: place an order from cart
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->guest(route('customer.login'))->with('error', 'Please log in before placing an order.');
        }

        $homepageSettings = HomepageSetting::singleton();
        $availableMethods = collect($homepageSettings->availablePaymentMethods());
        $paymentMethodKeys = $availableMethods->pluck('key')->all();

        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'delivery_address' => 'required|string|max:500',
            'contact_number'   => 'required|string|max:20',
            'payment_method'   => ['required', 'string', Rule::in($paymentMethodKeys)],
            'transaction_type' => 'nullable|in:exchange,new_cylinder,not_tank',
            'notes'            => 'nullable|string|max:500',
            'is_urgent'        => 'nullable|boolean',
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'address_full'     => 'nullable|string|max:500',
            'selected_freebie_id' => 'nullable|integer',
            'voucher_id' => 'nullable|integer|exists:user_vouchers,id',
            'selected_cart_ids' => 'nullable|string',
            'selected_product_ids' => 'nullable|string',
        ]);

        $selectedPaymentMethod = $availableMethods->firstWhere('key', $validated['payment_method']);

        if (! $selectedPaymentMethod) {
            return back()
                ->withInput()
                ->with('error', 'The selected payment method is no longer available.');
        }

        if (($selectedPaymentMethod['requires_proof'] ?? false) && ! $request->hasFile('proof_of_payment')) {
            return back()
                ->withInput()
                ->with('error', 'Please upload proof of payment for the selected payment method.');
        }

        if (($selectedPaymentMethod['requires_proof'] ?? false)) {
            $proofValidated = $request->validate([
                'proof_of_payment' => 'required|image|mimes:jpeg,png,gif,jpg|max:5120',
            ]);
        } else {
            $proofValidated = $request->validate([
                'proof_of_payment' => 'nullable|image|mimes:jpeg,png,gif,jpg|max:5120',
            ]);
        }
        
        // Merge proof_of_payment into validated array
        $validated = array_merge($validated, $proofValidated);

        if (($validated['payment_method'] === 'gcash') && (!filled($homepageSettings->gcash_account_number) || !filled($homepageSettings->gcash_account_name))) {
            return back()
                ->withInput()
                ->with('error', 'GCash payment is not configured yet. Please use Cash on Delivery or ask the administrator to set up the GCash account details.');
        }

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        $selectedProductIds = array_filter(array_map('intval', explode(',', (string) ($validated['selected_product_ids'] ?? ''))));
        if (empty($selectedProductIds)) {
            $selectedProductIds = array_filter(array_map('intval', (array) $request->session()->get('checkout_selected_items', [])));
        }
        if (! empty($selectedProductIds)) {
            $cartItems = $cartItems->whereIn('product_id', $selectedProductIds);
        }

        $hasCylinderProducts = $cartItems->contains(function ($item) {
            return $item->product?->isCylinder();
        });

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

        $smallRewardCount = 0;  // For freebies with required points (total qty of cylinder products)
        $freeRewardQuantity = 0; // For freebies with no required points (total quantity)
        $hasCylinderInOrder = false;
        foreach ($cartItems as $item) {
            $quantity = (int) $item->quantity;
            $totalQuantity = $quantity;
            $freeRewardQuantity += $totalQuantity; // Count all quantities for no-point freebies
            
            // Count cylinder products by their total quantity for freebie qualification
            if ($item->product?->isCylinder()) {
                $smallRewardCount += $quantity;  // Add the full quantity, not just 1
                $hasCylinderInOrder = true;
            }
        }
        
        // Check if selected freebie requires points - only clear if it DOES have required points and no cylinder products
        $selectedFreebieId = isset($validated['selected_freebie_id']) ? (int) $validated['selected_freebie_id'] : null;
        if ($selectedFreebieId !== null && !$hasCylinderInOrder) {
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

        $transactionType = $validated['transaction_type'] ?? ($hasCylinderProducts ? 'exchange' : 'not_tank');

        if ($hasCylinderProducts && !in_array($transactionType, ['exchange', 'new_cylinder'], true)) {
            return redirect()->route('customer.checkout')->with('error', 'Please choose a valid transaction type for cylinder products.');
        }

        $deliveryFee = HomepageSetting::singleton()->delivery_fee ?? 50.00;

        $order = DB::transaction(function () use ($validated, $cartItems, $smallRewardCount, $freeRewardQuantity, $selectedFreebieId, $isProductFreebieSelection, $selectedProductFreebieId, $selectedCartIds, $selectedVoucherId, $hasCylinderInOrder, $freebieRequiresPoints, $request, $deliveryFee, $transactionType) {
            $subtotal = 0;
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
                            // Requires points - must have cylinder products
                            $shouldProcessFreebie = $hasCylinderInOrder && $smallRewardCount > 0;
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
                        ->where('price', 0)
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
                // - If has required points: quantity = 1 (single reward item regardless of tank qty)
                $isCylinderProduct = $product?->isCylinder();
                $shouldAddFreebie = false;
                $freebieQuantityToAdd = 0;
                
                if ($selectedFreebieProduct !== null && !$freebieAdded) {
                    if ($freebieRequiresPoints) {
                        // Requires points - add once on any cylinder product, quantity 1
                        if ($isCylinderProduct) {
                            $shouldAddFreebie = true;
                            $freebieQuantityToAdd = 1; // One freebie regardless of cylinder quantity ordered
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
                        } elseif (str_starts_with($normalized, 'images/')) {
                            $freebieImageUrl = asset($normalized);
                        } else {
                            if (str_starts_with($normalized, 'storage/')) {
                                $normalized = substr($normalized, 8);
                            }
                            $freebieImageUrl = \Illuminate\Support\Facades\Storage::url($normalized);
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

            }

            // Deduct freebie stock based on quantity added to order
            if ($freebieAdded) {
                // If requires points: deduct 1 (single reward)
                // If no required points: deduct based on total quantity
                $deductQuantity = $freebieRequiresPoints ? 1 : $freeRewardQuantity;
                if ($selectedFreebie) {
                    $selectedFreebie->decrement('stock', $deductQuantity);
                }

                if ($selectedFreebieInventory) {
                    $selectedFreebieInventory->decrement('quantity_on_hand', $deductQuantity);

                    \App\Models\StockMovement::create([
                        'inventory_id' => $selectedFreebieInventory->id,
                        'full_in' => 0,
                        'full_out' => $deductQuantity,
                        'empty_in' => 0,
                        'empty_out' => 0,
                        'type' => 'sale',
                        'reference' => 'FREEBIE-CHECKOUT',
                        'notes' => 'Freebie stock deducted during checkout.',
                        'movement_date' => now(),
                        'created_by' => Auth::id(),
                    ]);
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
                'order_type'       => 'online',
                'transaction_type' => $transactionType,
                'subtotal'         => $subtotal,
                'discount'         => $voucherDiscount,
                'delivery_fee'     => $deliveryFee,
                'total_amount'     => max(0, $totalAmount), // Ensure no negative totals
                'customer_name'    => $validated['customer_name'],
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

            \App\Services\ActivityLogger::log('orders', 'created', "Customer {$order->customer_name} placed new order #{$order->order_number} (Total: ₱" . number_format($order->total_amount, 2) . ", Payment: " . ucfirst($order->payment_method) . ")", ['order_id' => $order->id, 'order_number' => $order->order_number, 'total' => $order->total_amount]);

            return $order;
        });

        $request->session()->forget('checkout_selected_items');

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
            $riderLat = $order->delivery->latitude ?? $order->delivery->rider->rider?->current_latitude;
            $riderLng = $order->delivery->longitude ?? $order->delivery->rider->rider?->current_longitude;

            // Default to GasGo Store Hub if location not yet updated by rider
            if (empty($riderLat) || empty($riderLng)) {
                $riderLat = 16.0196129;
                $riderLng = 120.3593023;
            }

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

        $pendingTime = $order->created_at?->format('g:i A');
        $approvedTime = $order->approved_at?->format('g:i A');
        $assignedTime = $order->delivery?->assigned_at?->format('g:i A');
        $outForDeliveryTime = $order->delivery?->picked_up_at?->format('g:i A') ?? ($order->status === 'out_for_delivery' ? $order->updated_at?->format('g:i A') : null);
        $deliveredTime = $order->delivered_at?->format('g:i A');

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
            'timestamps' => [
                'pending' => $pendingTime,
                'approved' => $approvedTime,
                'assigned' => $assignedTime,
                'out_for_delivery' => $outForDeliveryTime,
                'delivered' => $deliveredTime,
            ],
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

            \App\Services\ActivityLogger::log('orders', 'status_change', "Customer cancelled order #{$order->order_number}", ['order_id' => $order->id, 'status' => 'cancelled']);
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
        $order->load(['user', 'orderItems.product', 'delivery.rider', 'payment']);

        $riders = \App\Models\Rider::with('user')
            ->where('availability', 'available')
            ->get();

        return view('admin.order-detail', compact('order', 'riders'));
    }

    // Admin: update order status
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,assigned,out_for_delivery,delivered,cancelled',
            'transaction_type' => 'nullable|in:exchange,new_cylinder,refill,not_tank',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $previousStatus = $order->status;
            
            if (!empty($validated['transaction_type']) && $order->status !== 'delivered') {
                $order->update(['transaction_type' => $validated['transaction_type']]);
            }

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

            if ($validated['status'] === 'approved') {
                $order->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            } else {
                $order->update(['status' => $validated['status']]);
            }

            if ($validated['status'] === 'delivered') {
                if ($previousStatus !== 'delivered') {
                    OrderInventoryService::applyOnCompletion($order, Auth::id());
                }
                $order->update(['delivered_at' => now()]);
                // Update payment status to paid when order is delivered
                Payment::where('order_id', $order->id)->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            \App\Services\ActivityLogger::log('orders', 'status_change', "Admin updated order #{$order->order_number} status from {$previousStatus} to {$validated['status']}", ['order_id' => $order->id, 'previous' => $previousStatus, 'new' => $validated['status']]);
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

    // Admin: bulk update order status
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'required|integer',
            'status' => 'required|in:pending,approved,assigned,out_for_delivery,delivered,cancelled',
        ]);

        if ($validated['status'] === 'out_for_delivery') {
            $orders = Order::whereIn('id', $validated['order_ids'])
                ->where('status', 'assigned')
                ->pluck('id')
                ->all();

            if (count($orders) !== count($validated['order_ids'])) {
                return response()->json([
                    'message' => 'Only assigned orders can be marked as out for delivery.',
                ], 422);
            }
        }

        $updatedCount = 0;
        $updatedIds = [];

        DB::transaction(function () use ($validated, &$updatedCount, &$updatedIds) {
            foreach ($validated['order_ids'] as $orderId) {
                $order = Order::find($orderId);
                if (!$order) continue;

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

                if ($validated['status'] === 'approved') {
                    $order->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);
                } else {
                    $previousStatus = $order->status;
                    $order->update(['status' => $validated['status']]);
                }

                if ($validated['status'] === 'delivered') {
                    if (($previousStatus ?? null) !== 'delivered') {
                        OrderInventoryService::applyOnCompletion($order, Auth::id());
                    }
                    $order->update(['delivered_at' => now()]);
                    // Update payment status to paid when order is delivered
                    Payment::where('order_id', $order->id)->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                $updatedCount++;
                $updatedIds[] = $order->id;
            }
            
            \App\Services\ActivityLogger::log('orders', 'status_change', "Admin bulk updated {$updatedCount} order(s) to status: {$validated['status']}", ['count' => $updatedCount, 'order_ids' => $updatedIds, 'status' => $validated['status']]);
        });

        return response()->json([
            'message' => "Successfully updated {$updatedCount} order(s).",
            'count' => $updatedCount,
            'order_ids' => $updatedIds,
        ]);
    }
}
