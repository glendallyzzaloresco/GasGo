<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Customer\CustomerController;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if ($request->query('redirect') === 'checkout') {
            $request->session()->put('url.intended', route('customer.checkout'));
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $google = Socialite::driver('google')->user();
            $email = strtolower(trim($google->getEmail() ?? ''));
            $verified = filter_var($google->user['email_verified'] ?? $google->user['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (! $verified || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return redirect()->route('customer.login')->with('error', 'Google must confirm your email before you can continue.');
            }
            $user = User::where('google_id', $google->getId())->first();
            if (! $user) {
                if (User::where('email', $email)->exists()) {
                    return redirect()->route('customer.login')->with('error', 'This email is already registered. Please log in with your password.');
                }
                $user = User::create([
                    'name' => $google->getName() ?: $email,
                    'email' => $email,
                    'google_id' => $google->getId(),
                    'provider' => 'google',
                    'password' => Hash::make(Str::random(64)),
                    'email_verified_at' => now(),
                    'role' => 'customer',
                ]);
            } elseif (strtolower($user->email) === $email && ! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
            Auth::login($user);
            $request->session()->regenerate();
            app(CustomerController::class)->mergeSessionCartToDatabase($request, $user->id);
            ActivityLogger::log('auth', 'login', 'User logged in via Google OAuth', ['provider' => 'google'], $user);
            if ($user->requiresPasswordSetup()) {
                return redirect()->route('signup.password');
            }
            if ($user->isCustomer() && ! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended(route($user->isAdmin() ? 'admin.dashboard' : ($user->isRider() ? 'rider.dashboard' : 'customer.dashboard')));
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('customer.login')->with('error', 'Google login failed. Please try again.');
        }
    }
}
