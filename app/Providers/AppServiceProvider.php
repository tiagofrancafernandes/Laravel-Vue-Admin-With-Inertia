<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
    }
}
