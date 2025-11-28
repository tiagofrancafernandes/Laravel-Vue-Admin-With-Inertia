<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with generic system statistics.
     */
    public function index(): Response
    {
        // Cache key for dashboard stats (cache for 5 minutes)
        $cacheKey = 'dashboard_stats_data';

        $cacheClear = request()->session()->has('clear_dashboard_stats_data');

        if ($cacheClear) {
            cache()->forget($cacheKey);
        }

        $data = Cache::remember($cacheKey, 30, fn () => [
            'stats' => static::getStats(),
            'recentUsers' => static::getRecentUsers(),
        ]);

        return Inertia::render('Dashboard', $data);
    }

    /**
     * Get generic system statistics.
     */
    protected static function getStats(): array
    {
        return [
            'totalUsers' => User::count(),
            'adminUsers' => User::role('admin')->count(),
            'regularUsers' => User::role('user')->count(),
            'verifiedUsers' => User::whereNotNull('email_verified_at')->count(),
            'unverifiedUsers' => User::whereNull('email_verified_at')->count(),
        ];
    }

    /**
     * Get recent users (last 10).
     */
    protected static function getRecentUsers(): array
    {
        return User::select(['id', 'name', 'email', 'created_at'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'roles' => $user->roles()->select(['name', 'guard_name'])->get(),
                'created_at' => $user->created_at->toISOString(),
            ])
            ->toArray();
    }
}
