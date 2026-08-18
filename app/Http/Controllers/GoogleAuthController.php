<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user to Google
     */
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google callback
     */
    public function callback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();

            // Check if user exists with google_id
            $findUser = User::where('google_id', $user->id)->first();

            if ($findUser) {
                // User exists with Google ID, log them in
                Auth::login($findUser);
                \App\Services\ActivityLogger::log('auth', 'login', "User {$findUser->name} logged in via Google OAuth", ['provider' => 'google'], $findUser);
                
                // Redirect based on user role
                if ($findUser->role === 'admin') {
                    return redirect()->route('admin.dashboard')->with('success', 'Welcome back! Logged in with Google.');
                } elseif ($findUser->role === 'rider') {
                    return redirect()->route('rider.dashboard')->with('success', 'Welcome back! Logged in with Google.');
                } else {
                    return redirect()->route('customer.dashboard')->with('success', 'Welcome back! Logged in with Google.');
                }
            } else {
                // Check if email already exists (from previous registration)
                $existingEmail = User::where('email', $user->email)->first();
                
                if ($existingEmail) {
                    // Email already registered, ask user to link the account or login with email
                    return redirect()->route('customer.login')->with('error', 'This email is already registered. Please login with your password or use a different Google account.');
                }
                
                // New user, create account (always as customer)
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id' => $user->id,
                    'provider' => 'google',
                    'password' => bcrypt(uniqid()), // Generate random password
                    'role' => 'customer',
                ]);

                Auth::login($newUser);
                \App\Services\ActivityLogger::log('auth', 'register', "New user registered via Google OAuth: {$newUser->name} ({$newUser->email})", ['provider' => 'google'], $newUser);
                return redirect()->route('customer.dashboard')->with('success', 'Account created and logged in with Google!');
            }

        } catch (Exception $e) {
            return redirect()->route('customer.login')->with('error', 'Google login failed. Please try again.');
        }
    }
}
