<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private const SESSION_CART_KEY = 'cart';

    // Display the user's cart
    public function index(Request $request)
    {
        // Redirect non-customer users
        if (Auth::check() && Auth::user()->role !== 'customer') {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif (Auth::user()->role === 'rider') {
                return redirect()->route('rider.dashboard');
            }
        }

        if (Auth::check()) {
            $cartItems = Cart::with('product')
                ->where('user_id', Auth::id())
                ->get();

            $total = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

            return view('customer.cart', compact('cartItems', 'total'));
        }

        $sessionCart = $this->getSessionCart($request);

        if (empty($sessionCart)) {
            return view('customer.cart', [
                'cartItems' => collect(),
                'total' => 0,
            ]);
        }

        $products = Product::query()
            ->whereIn('id', array_keys($sessionCart))
            ->get()
            ->keyBy('id');

        $cartItems = collect($sessionCart)
            ->map(function ($quantity, $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                return (object) [
                    'product_id' => (int) $productId,
                    'quantity' => (int) $quantity,
                    'product' => $product,
                ];
            })
            ->filter()
            ->values();

        $total = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

        return view('customer.cart', compact('cartItems', 'total'));
    }

    // Add a product to the cart
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        if (! Auth::check()) {
            $cart = $this->getSessionCart($request);
            $productId = (int) $validated['product_id'];

            $cart[$productId] = ($cart[$productId] ?? 0) + (int) $validated['quantity'];

            $this->putSessionCart($request, $cart);

            $message = 'Product added to cart.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cartCount' => collect($cart)->sum(fn ($qty) => (int) $qty)
                ], 200);
            }

            return back()->with('success', $message);
        }

        $cartItem = Cart::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $validated['quantity']);
        } else {
            Cart::create([
                'user_id'    => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
            ]);
        }

        $cartCount = Cart::where('user_id', Auth::id())->sum('quantity');
        $message = 'Product added to cart.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cartCount' => $cartCount
            ], 200);
        }

        return redirect()->route('customer.cart')->with('success', $message);
    }

    // Update cart item quantity by product id (works for both guest and authenticated carts)
    public function updateItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if (Auth::check()) {
            Cart::query()
                ->where('user_id', Auth::id())
                ->where('product_id', $validated['product_id'])
                ->update(['quantity' => $validated['quantity']]);

            $cartCount = Cart::where('user_id', Auth::id())->sum('quantity');
            $message = 'Cart updated.';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'cartCount' => $cartCount], 200);
            }
            return redirect()->route('customer.cart')->with('success', $message);
        }

        $cart = $this->getSessionCart($request);
        $cart[(int) $validated['product_id']] = (int) $validated['quantity'];
        $this->putSessionCart($request, $cart);

        $cartCount = collect($cart)->sum(fn ($qty) => (int) $qty);
        $message = 'Cart updated.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'cartCount' => $cartCount], 200);
        }
        return redirect()->route('customer.cart')->with('success', $message);
    }

    // Remove cart item by product id (works for both guest and authenticated carts)
    public function destroyItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        if (Auth::check()) {
            Cart::query()
                ->where('user_id', Auth::id())
                ->where('product_id', $validated['product_id'])
                ->delete();

            $cartCount = Cart::where('user_id', Auth::id())->sum('quantity');
            $message = 'Item removed from cart.';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'cartCount' => $cartCount], 200);
            }
            return redirect()->route('customer.cart')->with('success', $message);
        }

        $cart = $this->getSessionCart($request);
        unset($cart[(int) $validated['product_id']]);
        $this->putSessionCart($request, $cart);

        $cartCount = collect($cart)->sum(fn ($qty) => (int) $qty);
        $message = 'Item removed from cart.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'cartCount' => $cartCount], 200);
        }
        return redirect()->route('customer.cart')->with('success', $message);
    }

    // Update cart item quantity
    public function update(Request $request, Cart $cart)
    {
        abort_if($cart->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update(['quantity' => $validated['quantity']]);

        return redirect()->route('customer.cart')->with('success', 'Cart updated.');
    }

    // Remove an item from the cart
    public function destroy(Cart $cart)
    {
        abort_if($cart->user_id !== Auth::id(), 403);

        $cart->delete();

        return redirect()->route('customer.cart')->with('success', 'Item removed from cart.');
    }

    // Clear entire cart for the user
    public function clear(Request $request)
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        } else {
            $request->session()->forget(self::SESSION_CART_KEY);
        }

        $message = 'Cart cleared.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'cartCount' => 0], 200);
        }
        return redirect()->route('customer.cart')->with('success', $message);
    }

    // Sync localStorage cart to database
    public function sync(Request $request)
    {
        $items = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if (Auth::check()) {
            foreach ($items['items'] as $item) {
                $cartItem = Cart::where('user_id', Auth::id())
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($cartItem) {
                    $cartItem->increment('quantity', $item['quantity']);
                } else {
                    Cart::create([
                        'user_id' => Auth::id(),
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            $message = 'Cart updated.';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message], 200);
            }
            return redirect()->route('customer.cart')->with('success', $message);
        }

        $cart = $this->getSessionCart($request);

        foreach ($items['items'] as $item) {
            $productId = (int) $item['product_id'];
            $cart[$productId] = ($cart[$productId] ?? 0) + (int) $item['quantity'];
        }

        $this->putSessionCart($request, $cart);

        $message = 'Cart updated.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message], 200);
        }
        return redirect()->route('customer.cart')->with('success', $message);
    }

    private function getSessionCart(Request $request): array
    {
        $cart = $request->session()->get(self::SESSION_CART_KEY, []);

        if (! is_array($cart)) {
            return [];
        }

        return collect($cart)
            ->map(fn ($quantity) => max(1, (int) $quantity))
            ->toArray();
    }

    private function putSessionCart(Request $request, array $cart): void
    {
        if (empty($cart)) {
            $request->session()->forget(self::SESSION_CART_KEY);

            return;
        }

        $request->session()->put(self::SESSION_CART_KEY, $cart);
    }
}
