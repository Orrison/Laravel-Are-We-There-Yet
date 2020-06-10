<?php

namespace Orrison\AreWeThereYet\Providers;

use Illuminate\Support\ServiceProvider;
use Orrison\AreWeThereYet\Providers\EventsServiceProvider;

class AreWeThereYetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['router']->middleware(\Orrison\AreWeThereYet\Middleware\TaskedMiddleware::class);
        $this->app->register(EventsServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
