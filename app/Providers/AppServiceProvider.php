<?php

namespace App\Providers;

use App\Notifications\Channels\FirebaseChannel;
use Illuminate\Notifications\ChannelManager;
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
        // Register Firebase notification channel
        $this->app->make(ChannelManager::class)->extend('firebase', function ($app) {
            return $app->make(FirebaseChannel::class);
        });
    }
}
