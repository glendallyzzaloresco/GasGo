<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ForgotPasswordController extends Controller
{
    /**
     * Display the email request form.
     */
    public function showLinkRequestForm()
    {
        $settings = HomepageSetting::first() ?? new HomepageSetting();
        return view('auth.forgot-password', compact('settings'));
    }

    /**
     * Generate a 6-digit numeric OTP code and send it via email.
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $normalizedEmail = strtolower(trim($request->email));
        $throttleKey = 'forgot-password-send|' . $normalizedEmail . '|' . $request->ip();

        // Limit to 3 code requests per 5 minutes per email/IP
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Too many password reset requests. Please wait {$minutes} minute" . ($minutes === 1 ? '' : 's') . " before requesting another code."]);
        }

        $user = User::where('email', $normalizedEmail)->first();

        if (!$user) {
            // Equalize response time to prevent email enumeration
            RateLimiter::hit($throttleKey, 300);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        RateLimiter::hit($throttleKey, 300);

        // Generate secure 6-digit code
        $code = sprintf('%06d', random_int(100000, 999999));

        // Store hashed code with current timestamp in password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $normalizedEmail],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        // Send Email Notification
        try {
            $user->notify(new PasswordResetCodeNotification($code));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password reset email failed to send: ' . $e->getMessage());
            $errorMessage = config('app.debug')
                ? 'Mail Error: ' . $e->getMessage()
                : 'Unable to send verification code email right now. Please verify mail server settings or try again later.';
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $errorMessage]);
        }

        ActivityLogger::log('auth', 'password_reset_request', "Password reset 6-digit code requested for email: {$user->email}", ['email' => $user->email], $user);

        return redirect()->route('password.verify.code', ['email' => $normalizedEmail])
            ->with('status', 'A 6-digit verification code has been sent to your email.');
    }

    /**
     * Display the form to enter the 6-digit code and new password.
     */
    public function showVerifyCodeForm(Request $request)
    {
        $email = $request->query('email', old('email'));
        $settings = HomepageSetting::first() ?? new HomepageSetting();

        return view('auth.reset-password', compact('email', 'settings'));
    }

    /**
     * Verify the 6-digit code and reset the user's password.
     */
    public function verifyAndResetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $normalizedEmail = strtolower(trim($request->email));
        $verifyThrottleKey = 'forgot-password-verify|' . $normalizedEmail . '|' . $request->ip();

        // Limit to 5 verification attempts per 10 minutes to protect against OTP brute forcing
        if (RateLimiter::tooManyAttempts($verifyThrottleKey, 5)) {
            $seconds = RateLimiter::availableIn($verifyThrottleKey);
            $minutes = ceil($seconds / 60);

            ActivityLogger::log('auth', 'lockout', "OTP verification locked out for {$normalizedEmail} after exceeding attempts", [
                'email' => $normalizedEmail,
                'ip' => $request->ip(),
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['code' => "Too many incorrect verification attempts. Code verification is temporarily locked for {$minutes} minute" . ($minutes === 1 ? '' : 's') . ". Please try again later."]);
        }

        $record = DB::table('password_reset_tokens')->where('email', $normalizedEmail)->first();

        if (!$record) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['code' => 'Invalid or expired verification code. Please request a new one.']);
        }

        // Check if code has expired (5 minutes limit)
        if (Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $normalizedEmail)->delete();
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['code' => 'The verification code has expired (5-minute limit). Please request a new code.']);
        }

        // Verify the 6-digit code
        if (!Hash::check($request->code, $record->token)) {
            RateLimiter::hit($verifyThrottleKey, 600);
            $remaining = RateLimiter::remaining($verifyThrottleKey, 5);

            ActivityLogger::log('auth', 'failed_otp', "Failed 6-digit OTP verification attempt for {$normalizedEmail} ({$remaining} attempts remaining)", [
                'email' => $normalizedEmail,
                'ip' => $request->ip(),
                'remaining' => $remaining,
            ]);

            $attemptText = $remaining > 0 ? " ({$remaining} " . ($remaining === 1 ? 'attempt' : 'attempts') . " remaining)" : "";

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['code' => 'The 6-digit verification code is incorrect.' . $attemptText]);
        }

        // Find user and update password
        $user = User::where('email', $normalizedEmail)->first();

        if (!$user) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'User not found.']);
        }

        // Clear verification rate limiter
        RateLimiter::clear($verifyThrottleKey);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            'password_set_at' => now(),
        ])->save();

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $normalizedEmail)->delete();

        ActivityLogger::log('auth', 'password_reset', "User {$user->name} ({$user->email}) successfully reset their password via 6-digit verification code", ['email' => $user->email], $user);

        return redirect()->route('customer.login')
            ->with('success', 'Your password has been successfully reset! You can now log in.');
    }

    /**
     * Resend a fresh 6-digit verification code.
     */
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        return $this->sendResetCode($request);
    }
}

