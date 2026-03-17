<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerController extends Controller
{
    private const SESSION_CART_KEY = 'cart';

    public function dashboard()
    {
        $activeOrders = [];

        if (Auth::check()) {
            // Get active orders for the authenticated user
            $activeOrders = \App\Models\Order::with(['delivery.rider', 'orderItems.product'])
                ->where('user_id', Auth::id())
                ->whereIn('status', ['assigned', 'out_for_delivery'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('customer.dashboard', compact('activeOrders'));
    }

    public function products()
    {
        return view('customer.product');
    }

    public function cart()
    {
        return view('customer.cart');
    }

    public function checkout()
    {
        return view('customer.checkout');
    }

    public function tracking()
    {
        return view('customer.tracking');
    }

    public function loyalty()
    {
        return view('customer.loyalty');
    }

    public function orders()
    {
        return view('customer.orders');
    }

    public function profile()
    {
        if (! Auth::check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in to view your account.');
        }

        return view('customer.profile');
    }

    public function updateProfile(Request $request)
    {
        if (! Auth::check()) {
            $message = 'Please log in to update your account.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 401);
            }
            return redirect()->route('customer.login')->with('error', $message);
        }

        $user = User::query()->findOrFail(Auth::id());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->name = $validated['name'];
        $user->email = strtolower($validated['email']);
        $user->phone = $validated['phone'];
        $user->address = $validated['address'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        $message = 'Your account has been updated successfully.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message], 200);
        }
        return back()->with('success', $message);
    }

    public function login()
    {
        return view('customer.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            $this->mergeSessionCartToDatabase($request, $user->id);

            $redirectPath = route('customer.dashboard');
            $message = 'Welcome back!';

            if ($user->role === 'admin') {
                $redirectPath = route('admin.dashboard');
                $message = 'Welcome back, Admin!';
            } elseif ($user->role === 'rider') {
                $redirectPath = route('rider.dashboard');
                $message = 'Welcome back, Rider!';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => $redirectPath
                ], 200);
            }

            return redirect()
                ->intended($redirectPath)
                ->with('success', $message);
        }

        $message = 'The provided credentials do not match our records.';
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 401);
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', $message);
    }

    public function logout(Request $request)
    {
        $role = Auth::user()->role ?? 'customer';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'You have been logged out.';
        $redirectPath = $role === 'admin'
            ? route('customer.login')
            : route('customer.dashboard');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectPath
            ], 200);
        }

        return redirect($redirectPath)->with('success', $message);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'password' => $validated['password'],
            'role' => 'customer',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->mergeSessionCartToDatabase($request, $user->id);

        $message = 'Your customer account has been created successfully.';
        $redirectPath = route('customer.dashboard');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectPath
            ], 200);
        }

        return redirect()
            ->intended($redirectPath)
            ->with('success', $message);
    }

    private function mergeSessionCartToDatabase(Request $request, int $userId): void
    {
        $sessionCart = $request->session()->get(self::SESSION_CART_KEY, []);

        if (! is_array($sessionCart) || empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $productId => $quantity) {
            $normalizedProductId = (int) $productId;
            $normalizedQuantity = max(1, (int) $quantity);

            if ($normalizedProductId <= 0) {
                continue;
            }

            $cartItem = Cart::query()
                ->where('user_id', $userId)
                ->where('product_id', $normalizedProductId)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $normalizedQuantity);
            } else {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $normalizedProductId,
                    'quantity' => $normalizedQuantity,
                ]);
            }
        }

        $request->session()->forget(self::SESSION_CART_KEY);
    }
}
