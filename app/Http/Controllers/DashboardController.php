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
        $cacheKey = 'dashboard_stats_' . now()->format('Y-m-d_H-i');

        $data = Cache::remember($cacheKey, 300, fn () => [
            'stats' => $this->getStats(),
            'recentUsers' => $this->getRecentUsers(),
        ]);

        return Inertia::render('Dashboard', $data);
    }

    /**
     * Get generic system statistics.
     */
    private function getStats(): array
    {
        return [
            'totalUsers' => User::count(),
            'adminUsers' => User::where('role', 'admin')->count(),
            'regularUsers' => User::where('role', 'user')->count(),
            'verifiedUsers' => User::whereNotNull('email_verified_at')->count(),
        ];
    }

    /**
     * Get recent users (last 10).
     */
    private function getRecentUsers(): array
    {
        return User::select('id', 'name', 'email', 'role', 'created_at')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at->toISOString(),
            ])
            ->toArray();
    }
}
