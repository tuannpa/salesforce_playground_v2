<?php

namespace App\Providers;

use App\Interfaces\SalesforceServiceInterface;
use App\Services\SalesforceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SalesforceServiceInterface::class, SalesforceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
