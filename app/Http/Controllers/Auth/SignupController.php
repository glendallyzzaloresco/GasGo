<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SignupController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->finish($request);
        }

        return view('auth.complete-signup', ['passwordSetup' => false]);
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return $this->finish($request);
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->finish($request);
        }
        $sent = $request->user()->sendSignupVerification();

        return back()->with($sent ? 'status' : 'error', $sent
            ? 'Verification email sent. Check your inbox and spam folder.'
            : 'We could not send the email. Please try again in one minute.');
    }

    public function password(Request $request)
    {
        if (! $request->user()->requiresPasswordSetup()) {
            return $this->finish($request);
        }

        return view('auth.complete-signup', ['passwordSetup' => true]);
    }

    public function storePassword(Request $request)
    {
        abort_unless($request->user()->requiresPasswordSetup(), 403);
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);
        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
            'password_set_at' => now(),
        ])->save();
        $request->session()->regenerate();

        return $this->finish($request);
    }

    private function finish(Request $request)
    {
        $user = $request->user();
        if ($user->requiresPasswordSetup()) {
            return redirect()->route('signup.password');
        }
        if ($user->isCustomer() && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        $route = $user->isAdmin() ? 'admin.dashboard' : ($user->isRider() ? 'rider.dashboard' : 'customer.dashboard');

        return redirect()->intended(route($route));
    }
}
