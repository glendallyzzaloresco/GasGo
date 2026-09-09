<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ServiceReview;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerController extends Controller
{
    private const SESSION_CART_KEY = 'cart';

    public function dashboard()
    {
        try {
            $products = Product::query()
                ->forNiche()
                ->with(['inventory', 'categoryModel'])
                ->where('is_active', true)
                ->where('price', '>', 0)
                ->get();
        } catch (QueryException $e) {
            if (! str_contains($e->getMessage(), "Unknown column 'category'")) {
                throw $e;
            }

            $products = Product::query()
                ->with(['inventory', 'categoryModel'])
                ->where('is_active', true)
                ->where('price', '>', 0)
                ->get();
        }

        $products = $products
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
                if (count($featuredByCategory) >= 3) {
                    break;
                }
                $first = $categoryProducts->first();
                if ($first) {
                    $featuredByCategory[] = $first;
                }
            }

            // Get 1 more product from any category to reach 4 total
            if (count($featuredByCategory) < 4) {
                foreach ($categoryMap as $normalizedCategory => $categoryProducts) {
                    if (count($featuredByCategory) >= 4) {
                        break;
                    }
                    // Skip first product we already took
                    $remaining = $categoryProducts->skip(1);
                    foreach ($remaining as $product) {
                        if (count($featuredByCategory) >= 4) {
                            break;
                        }
                        if (! in_array($product->id, array_column($featuredByCategory, 'id'))) {
                            $featuredByCategory[] = $product;
                        }
                    }
                }
            }

            $products = collect($featuredByCategory)->take(4);
        }

        $serviceReviews = collect();
        $averageRating = 5.0;
        $totalReviewCount = 0;

        try {
            if (Schema::hasTable('service_reviews')) {
                $serviceReviews = ServiceReview::with(['user', 'order'])
                    ->where('is_approved', true)
                    ->where('rating', 5)
                    ->latest()
                    ->take(6)
                    ->get();

                $averageRating = ServiceReview::where('is_approved', true)->avg('rating') ?: 5.0;
                $totalReviewCount = ServiceReview::where('is_approved', true)->count();
            }
        } catch (\Throwable $e) {
            // Graceful fallback if table doesn't exist yet on production
        }

        return view('customer.dashboard', compact('products', 'serviceReviews', 'averageRating', 'totalReviewCount'));
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
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user->name = $validated['name'];
        $user->email = strtolower($validated['email']);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $user->google_id = null;
        }
        $user->phone = $validated['phone'];
        $user->address = $validated['address'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        if ($user->wasChanged('email')) {
            $user->sendSignupVerification();
        }
        $message = 'Your account has been updated successfully.';
        ActivityLogger::log('auth', 'updated', "User {$user->name} updated profile information", ['user_id' => $user->id], $user);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message], 200);
        }

        return back()->with('success', $message);
    }

    public function login(Request $request)
    {
        $activeTab = $request->input('tab') ?? old('auth_tab', 'login');

        return view('customer.login', compact('activeTab'));
    }

    public const MAX_LOGIN_ATTEMPTS = 5;
    public const LOCKOUT_SECONDS = 60;
    public const MAX_IP_ATTEMPTS = 20;
    public const IP_LOCKOUT_SECONDS = 300;

    /**
     * Generate standard throttle key for user login (email + IP).
     */
    protected function throttleKey(Request $request, string $email): string
    {
        return Str::transliterate(strtolower(trim($email)) . '|' . $request->ip());
    }

    /**
     * Generate IP-wide throttle key to defend against credential stuffing.
     */
    protected function ipThrottleKey(Request $request): string
    {
        return 'login-ip|' . $request->ip();
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $normalizedEmail = strtolower(trim($credentials['email']));
        $throttleKey = $this->throttleKey($request, $normalizedEmail);
        $ipThrottleKey = $this->ipThrottleKey($request);

        // 1. Check if account or IP is currently locked out
        $isAccountLocked = RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS);
        $isIpLocked = RateLimiter::tooManyAttempts($ipThrottleKey, self::MAX_IP_ATTEMPTS);

        if ($isAccountLocked || $isIpLocked) {
            $seconds = max(
                $isAccountLocked ? RateLimiter::availableIn($throttleKey) : 0,
                $isIpLocked ? RateLimiter::availableIn($ipThrottleKey) : 0
            );

            ActivityLogger::log('auth', 'lockout', "Blocked login attempt during lockout for {$normalizedEmail} from IP {$request->ip()} ({$seconds}s remaining)", [
                'email' => $normalizedEmail,
                'ip' => $request->ip(),
                'lockout_seconds' => $seconds,
            ]);

            $message = "Too many login attempts. Your account has been temporarily locked for security. Please try again in {$seconds} second" . ($seconds === 1 ? '' : 's') . '.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'lockout_seconds' => $seconds,
                ], 429)->header('Retry-After', (string) $seconds);
            }

            return back()
                ->withInput($request->only('email'))
                ->with('error', $message)
                ->with('lockout_seconds', $seconds);
        }

        // 2. Find user by normalized email
        $user = User::where('email', $normalizedEmail)->first();

        // Mitigation against timing attacks and account enumeration:
        // Always run password verification using a dummy hash if user doesn't exist.
        $dummyHash = '$2y$12$e0MYzXyjpJS7Pd0RVvHwHeFj7bK2Q1h4w9a2Q7UfHlAomjSre5bKW';
        $userPasswordHash = $user ? $user->password : $dummyHash;
        $passwordMatches = password_verify($credentials['password'], $userPasswordHash);

        if (! $user || ! $passwordMatches) {
            // Record failed attempt in both account and IP throttles
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
            RateLimiter::hit($ipThrottleKey, self::IP_LOCKOUT_SECONDS);

            $attemptsLeft = RateLimiter::remaining($throttleKey, self::MAX_LOGIN_ATTEMPTS);

            if ($attemptsLeft <= 0) {
                $seconds = RateLimiter::availableIn($throttleKey);

                ActivityLogger::log('auth', 'lockout', "Account temporarily locked after 5 failed login attempts for {$normalizedEmail}", [
                    'email' => $normalizedEmail,
                    'ip' => $request->ip(),
                    'lockout_seconds' => $seconds,
                ], $user);

                $message = "Too many login attempts. Your account has been temporarily locked for security. Please try again in {$seconds} second" . ($seconds === 1 ? '' : 's') . '.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'lockout_seconds' => $seconds,
                    ], 429)->header('Retry-After', (string) $seconds);
                }

                return back()
                    ->withInput($request->only('email'))
                    ->with('error', $message)
                    ->with('lockout_seconds', $seconds);
            }

            ActivityLogger::log('auth', 'failed_login', "Failed login attempt for {$normalizedEmail} ({$attemptsLeft} attempt" . ($attemptsLeft === 1 ? '' : 's') . " remaining)", [
                'email' => $normalizedEmail,
                'ip' => $request->ip(),
                'attempts_remaining' => $attemptsLeft,
            ], $user);

            $attemptWord = $attemptsLeft === 1 ? 'attempt' : 'attempts';
            $message = "The provided credentials do not match our records. You have {$attemptsLeft} {$attemptWord} remaining before your account is temporarily locked.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'attempts_remaining' => $attemptsLeft,
                ], 401);
            }

            return back()
                ->withInput($request->only('email'))
                ->with('error', $message)
                ->with('attempts_remaining', $attemptsLeft);
        }

        // 3. Password is valid: clear all rate limiting counters
        RateLimiter::clear($throttleKey);
        RateLimiter::clear($ipThrottleKey);

        // A successful password login proves a legacy Google user knows their password.
        if ($user->requiresPasswordSetup()) {
            $user->forceFill(['password_set_at' => now()])->save();
        }

        // Password is valid - log the user in
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLogger::log('auth', 'login', "User {$user->name} logged in successfully (".ucfirst($user->role ?? 'customer').')', ['role' => $user->role], $user);

        // Rehash password if the hashing configuration has changed
        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
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

        if ($user->isCustomer() && ! $user->hasVerifiedEmail()) {
            $request->session()->put('url.intended', $request->session()->get('url.intended', $redirectPath));
            $redirectPath = route('verification.notice');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectPath,
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
            ActivityLogger::log('auth', 'logout', "User {$user->name} logged out", ['role' => $role], $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'You have been logged out.';
        $redirectPath = $role === 'admin'
            ? route('customer.login')
            : route('customer.dashboard');

        if ($user->isCustomer() && ! $user->hasVerifiedEmail()) {
            $request->session()->put('url.intended', $request->session()->get('url.intended', $redirectPath));
            $redirectPath = route('verification.notice');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectPath,
            ], 200);
        }

        return redirect($redirectPath)->with('success', $message);
    }

    public function register(Request $request)
    {
        // Rate limit registration requests per IP (max 5 registrations per 15 minutes)
        $regThrottleKey = 'register-ip|' . $request->ip();
        if (RateLimiter::tooManyAttempts($regThrottleKey, 5)) {
            $seconds = RateLimiter::availableIn($regThrottleKey);
            $minutes = ceil($seconds / 60);
            $message = "Too many account registrations from this network. Please try again in {$minutes} minute" . ($minutes === 1 ? '' : 's') . '.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 429)->header('Retry-After', (string) $seconds);
            }

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', $message);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        RateLimiter::hit($regThrottleKey, 900);

        // Create user
        $userId = DB::table('users')->insertGetId([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($userId);

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLogger::log('auth', 'register', "New customer registered: {$user->name} ({$user->email})", ['role' => 'customer'], $user);

        $this->mergeSessionCartToDatabase($request, $user->id);

        $sent = $user->sendSignupVerification();
        $message = $sent ? 'Check your email to verify your account.' : 'Your account was created, but the verification email could not be sent. Please retry from the verification page.';
        $redirectPath = route('customer.dashboard');

        // Check if redirect parameter is set (e.g., redirect=checkout)
        if ($request->query('redirect') === 'checkout') {
            $redirectPath = route('customer.checkout');
        }

        if ($user->isCustomer() && ! $user->hasVerifiedEmail()) {
            $request->session()->put('url.intended', $request->session()->get('url.intended', $redirectPath));
            $redirectPath = route('verification.notice');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectPath,
            ], 200);
        }

        return redirect()
            ->to($redirectPath)
            ->with('success', $message);
    }

    public function mergeSessionCartToDatabase(Request $request, int $userId): void
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
