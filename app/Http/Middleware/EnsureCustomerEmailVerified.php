<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

class EnsureCustomerEmailVerified extends EnsureEmailIsVerified
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if ($request->user() && ! $request->user()->isCustomer()) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
