<?php

namespace App\Providers;

use App\Interfaces\SalesforceSOAPServiceInterface;
use App\Services\SalesforceSOAPService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SalesforceSOAPServiceInterface::class, SalesforceSOAPService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
