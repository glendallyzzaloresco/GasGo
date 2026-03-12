<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            return redirect()->route('customer.login')->with('error', 'Please log in to continue to checkout.');
        }

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart')->with('error', 'Your cart is empty.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

        return view('customer.checkout', compact('cartItems', 'subtotal'));
    }

    // Customer: place an order from cart
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in before placing an order.');
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

        $order = DB::transaction(function () use ($validated, $cartItems) {
            $subtotal    = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
            $deliveryFee = 50.00;
            $totalAmount = $subtotal + $deliveryFee;

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

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity'     => $item->quantity,
                    'price'        => $item->product->price,
                    'subtotal'     => $item->product->price * $item->quantity,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            Cart::where('user_id', Auth::id())->delete();

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

        if ($order->delivery && $order->delivery->rider) {
            $riderName = $order->delivery->rider->name;
            $riderPhone = $order->delivery->rider->phone;
            $riderLat = $order->delivery->latitude;
            $riderLng = $order->delivery->longitude;
        }

        return response()->json([
            'status' => $order->status,
            'rider_name' => $riderName,
            'rider_phone' => $riderPhone,
            'rider_lat' => $riderLat,
            'rider_lng' => $riderLng,
            'estimated_delivery' => $order->estimated_delivery_time?->format('g:i A'),
            'delivered_at' => $order->delivered_at?->format('M j, Y — g:i A'),
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

        return redirect()->back()->with('success', 'Order status updated.');
    }
}
