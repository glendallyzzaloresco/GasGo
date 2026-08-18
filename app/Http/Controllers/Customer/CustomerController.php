<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerController extends Controller
{
    private const SESSION_CART_KEY = 'cart';

    public function dashboard()
    {
        $products = Product::query()
            ->with('inventory')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->get()
            ->sortByDesc('created_at')
            ->values();

        // Ensure variety in featured products by grouping by category (show 4 total: 1 tank, 1 accessories, 1 appliances, +1 more)
        if (count($products) > 0) {
            // Normalize categories to handle case variations
            $categoryMap = $products->mapToGroups(function ($item) {
                $normalized = strtolower(trim($item->category ?? 'uncategorized'));
                return [$normalized => $item];
            });
            
            $featuredByCategory = [];
            
            // Get exactly 1 product from each category first (up to 3 total)
            foreach ($categoryMap as $normalizedCategory => $categoryProducts) {
                if (count($featuredByCategory) >= 3) break;
                $first = $categoryProducts->first();
                if ($first) {
                    $featuredByCategory[] = $first;
                }
            }
            
            // Get 1 more product from any category to reach 4 total
            if (count($featuredByCategory) < 4) {
                foreach ($categoryMap as $normalizedCategory => $categoryProducts) {
                    if (count($featuredByCategory) >= 4) break;
                    // Skip first product we already took
                    $remaining = $categoryProducts->skip(1);
                    foreach ($remaining as $product) {
                        if (count($featuredByCategory) >= 4) break;
                        if (!in_array($product->id, array_column($featuredByCategory, 'id'))) {
                            $featuredByCategory[] = $product;
                        }
                    }
                }
            }
            
            $products = collect($featuredByCategory)->take(4);
        }

        return view('customer.dashboard', compact('products'));
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
            // Use DB to directly update password, bypassing the hashed cast to set argon2id
            DB::table('users')->where('id', $user->id)->update([
                'password' => password_hash($validated['password'], PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 1
                ])
            ]);
        } else {
            $user->save();
        }

        $message = 'Your account has been updated successfully.';
        \App\Services\ActivityLogger::log('auth', 'updated', "User {$user->name} updated profile information", ['user_id' => $user->id], $user);

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

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
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

        // Verify password using native PHP password_verify (supports all algorithms)
        if (!password_verify($credentials['password'], $user->password)) {
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

        // Password is valid - log the user in
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        \App\Services\ActivityLogger::log('auth', 'login', "User {$user->name} logged in successfully (" . ucfirst($user->role ?? 'customer') . ")", ['role' => $user->role], $user);

        // Auto-upgrade bcrypt passwords to argon2id on successful login
        $isBcryptHash = strpos($user->password, '$2y$') === 0 || strpos($user->password, '$2a$') === 0;
        if ($isBcryptHash) {
            try {
                // Hash with argon2id using native PHP function
                $hashedPassword = password_hash($credentials['password'], PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 1
                ]);
                DB::table('users')->where('id', $user->id)->update(['password' => $hashedPassword]);
            } catch (\Exception $e) {
                // Silently fail password upgrade, user is already logged in
            }
        }

        $this->mergeSessionCartToDatabase($request, $user->id);

        $redirectPath = route('customer.dashboard');
        $message = 'Welcome back!';

        // Check if redirect parameter is set (e.g., redirect=checkout)
        if ($request->query('redirect') === 'checkout') {
            $redirectPath = route('customer.checkout');
        } elseif ($user->role === 'admin') {
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

        return redirect($redirectPath)
            ->with('success', $message);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $role = $user?->role ?? 'customer';

        if ($user) {
            \App\Services\ActivityLogger::log('auth', 'logout', "User {$user->name} logged out", ['role' => $role], $user);
        }

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

        // Hash password with argon2id
        $hashedPassword = password_hash($validated['password'], PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1
        ]);

        // Create user using DB to bypass hashed cast
        $userId = DB::table('users')->insertGetId([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'password' => $hashedPassword,
            'role' => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($userId);

        Auth::login($user);
        $request->session()->regenerate();

        \App\Services\ActivityLogger::log('auth', 'register', "New customer registered: {$user->name} ({$user->email})", ['role' => 'customer'], $user);

        $this->mergeSessionCartToDatabase($request, $user->id);

        $message = 'Your customer account has been created successfully.';
        $redirectPath = route('customer.dashboard');
        
        // Check if redirect parameter is set (e.g., redirect=checkout)
        if ($request->query('redirect') === 'checkout') {
            $redirectPath = route('customer.checkout');
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

    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }

    public function termsOfService()
    {
        return view('pages.terms-of-service');
    }
}
