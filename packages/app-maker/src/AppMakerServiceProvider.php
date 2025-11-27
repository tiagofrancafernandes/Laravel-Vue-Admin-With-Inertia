<?php

namespace AppMaker;

use Illuminate\Support\ServiceProvider;

/**
 * @property \Illuminate\Contracts\Foundation\Application $app
 */
class AppMakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/appmaker.php',
            'appmaker'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/appmaker.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/appmaker.php' => config_path('appmaker.php'),
            ], 'appmaker-config');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/Components/AppMaker'),
            ], 'appmaker-components');

            $this->publishes([
                __DIR__ . '/../resources/css' => resource_path('css/appmaker'),
            ], 'appmaker-styles');
        }

        // Register middleware
        $this->app['router']->aliasMiddleware(
            'appmaker.authorize',
            Http\Middleware\AuthorizeResource::class
        );
    }
}
