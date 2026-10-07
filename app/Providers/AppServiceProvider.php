<?php

namespace App\Providers;

use App\Exceptions\GeneralException;
use App\Services\Notification\Providers\PushProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerPushProvider();
    }

    /**
     * Chooses which push provider class is used, based on config/notification.php.
     */
    protected function registerPushProvider(): void
    {
        $this->app->bind(PushProvider::class, function ($app) {
            $name = config('notification.push.provider');
            $class = config("notification.push.providers.{$name}");

            if ($class === null) {
                throw new GeneralException("Unknown push provider '{$name}'. Check config/notification.php.");
            }

            return $app->make($class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paginator::useBootstrap();
        // Paginator::useBootstrapThree();
        // Paginator::useBootstrapFour();
        Paginator::useBootstrapFive();
    }
}
