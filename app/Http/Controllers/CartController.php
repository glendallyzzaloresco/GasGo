<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Display the user's cart
    public function index()
    {
        if (! Auth::check()) {
            return view('customer.cart', [
                'cartItems' => collect(),
                'total' => 0,
            ]);
        }

        $cartItems = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        $total = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

        return view('customer.cart', compact('cartItems', 'total'));
    }

    // Add a product to the cart
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in to save items to your cart.');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

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

        return redirect()->route('customer.cart')->with('success', 'Product added to cart.');
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
    public function clear()
    {
        if (! Auth::check()) {
            return redirect()->route('customer.cart');
        }

        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('customer.cart')->with('success', 'Cart cleared.');
    }

    // Sync localStorage cart to database
    public function sync(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in first.');
        }

        $items = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Clear existing cart and replace with localStorage items
        Cart::where('user_id', Auth::id())->delete();

        foreach ($items['items'] as $item) {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('customer.checkout');
    }
}
