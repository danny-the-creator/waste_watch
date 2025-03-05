<?php

namespace App\Providers;

use App\Services\KeypairService;
use Illuminate\Support\ServiceProvider;

class KeypairServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(KeypairService::class, function ($_): KeypairService {
            return new KeypairService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
