<?php

namespace Orrison\AreWeThereYet\Providers;

use Illuminate\Support\ServiceProvider;

class AreWeThereYetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['router']->middleware(\Orrison\AreWeThereYet\Middleware\TrackedMiddleware::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/awty.php' => config_path('awty.php'),
        ], 'awty-config');

        $this->loadMigrationsFrom(__DIR__.'/../Migrations');
    }
}
