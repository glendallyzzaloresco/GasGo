<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request and ensure user has an authorized role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->guest(route('customer.login'));
        }

        $user = Auth::user();

        if (! in_array($user->role, $roles, true)) {
            ActivityLogger::log(
                'auth',
                'unauthorized_access',
                "Unauthorized access attempt to [{$request->path()}] by user #{$user->id} ({$user->name}) with role '{$user->role}' (required: ".implode(', ', $roles).')',
                [
                    'required_roles' => $roles,
                    'user_role' => $user->role,
                    'path' => $request->path(),
                    'method' => $request->method(),
                ],
                $user
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: You do not have permission to access this resource.',
                ], 403);
            }

            // Redirect user to their own role dashboard with an error notice
            $fallbackRoute = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'rider' => route('rider.dashboard'),
                default => route('customer.dashboard'),
            };

            return redirect($fallbackRoute)->with('error', 'Access denied. You do not have permission to access that area.');
        }

        return $next($request);
    }
}
