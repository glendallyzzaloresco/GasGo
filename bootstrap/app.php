<?php

use App\Http\Middleware\EnsureCustomerEmailVerified;
use App\Http\Middleware\EnsureSignupComplete;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->web(append: [
            EnsureSignupComplete::class,
            SecurityHeaders::class,
        ]);
        $middleware->alias([
            'verified' => EnsureCustomerEmailVerified::class,
            'role' => EnsureUserRole::class,
        ]);
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handleExpired = function (Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Session expired. Please refresh the page and try again.',
                ], 419);
            }

            $target = $request->header('referer') ?: route('customer.login');

            return redirect($target)
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'Your session expired or the page was idle for too long. Please try logging in again.');
        };

        $exceptions->render(function (HttpException $e, Request $request) use ($handleExpired) {
            if ($e->getStatusCode() === 419) {
                return $handleExpired($request);
            }
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) use ($handleExpired) {
            return $handleExpired($request);
        });
    })->create();
