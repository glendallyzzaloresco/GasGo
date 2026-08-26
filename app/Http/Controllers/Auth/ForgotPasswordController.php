<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        // Generate secure 6-digit code
        $code = sprintf('%06d', random_int(100000, 999999));

        // Store hashed code with current timestamp in password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
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

        \App\Services\ActivityLogger::log('auth', 'password_reset', "Password reset 6-digit code requested for email: {$user->email}", ['email' => $user->email], $user);

        return redirect()->route('password.verify.code', ['email' => $request->email])
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

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['code' => 'Invalid or expired verification code. Please request a new one.']);
        }

        // Check if code has expired (5 minutes limit)
        if (Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['code' => 'The verification code has expired (5-minute limit). Please request a new code.']);
        }

        // Verify the 6-digit code
        if (!Hash::check($request->code, $record->token)) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['code' => 'The 6-digit verification code is incorrect.']);
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'User not found.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        \App\Services\ActivityLogger::log('auth', 'password_reset', "User {$user->name} ({$user->email}) successfully reset their password via 6-digit verification code", ['email' => $user->email], $user);

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
