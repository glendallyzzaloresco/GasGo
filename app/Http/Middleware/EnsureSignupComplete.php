<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSignupComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || $request->routeIs('verification.*', 'signup.*', 'logout', 'customer.logout', 'login', 'customer.login', 'customer.authenticate', 'auth.google*', 'password.*')) {
            return $next($request);
        }

        $route = $user->requiresPasswordSetup() ? 'signup.password' :
            ($user->isCustomer() && ! $user->hasVerifiedEmail() ? 'verification.notice' : null);
        if (! $route) {
            return $next($request);
        }
        if ($request->isMethod('GET')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Please complete your account setup.', 'redirect' => route($route)], 403);
        }

        return redirect()->route($route);
    }
}
