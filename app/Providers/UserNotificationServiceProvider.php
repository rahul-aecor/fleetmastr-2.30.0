<?php

namespace App\Providers;

use App\Services\UserNotification;
use Illuminate\Support\ServiceProvider;

class UserNotificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\UserNotificationService', function ($app) {
            return new UserNotification();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['App\Contracts\UserNotification'];
    }
}
