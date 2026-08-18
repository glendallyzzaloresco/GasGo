<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class ActivityLogger
{
    /**
     * Log an activity into the database.
     *
     * @param string $module (products, orders, deliveries, inventory, auth, loyalty, settings)
     * @param string $action (created, updated, deleted, login, register, password_reset, status_change, etc.)
     * @param string $description (Human-readable description)
     * @param array $properties (Optional metadata / diffs)
     * @param User|null $user (Optional explicit user)
     */
    public static function log(
        string $module,
        string $action,
        string $description,
        array $properties = [],
        ?User $user = null
    ): ?ActivityLog {
        try {
            $currentUser = $user ?? Auth::user();

            $userName = $currentUser ? $currentUser->name : 'System / Guest';
            $userRole = $currentUser ? ($currentUser->role ?? 'customer') : 'system';
            $userId = $currentUser ? $currentUser->id : null;

            return ActivityLog::create([
                'user_id'     => $userId,
                'user_name'   => $userName,
                'user_role'   => $userRole,
                'module'      => $module,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => Request::ip(),
                'user_agent'  => Request::userAgent(),
                'properties'  => !empty($properties) ? $properties : null,
            ]);
        } catch (Throwable $e) {
            // Log to fallback laravel log but never crash the main request
            report($e);
            return null;
        }
    }
}
