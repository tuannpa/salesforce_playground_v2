<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register repository prefix in here, it will automatically bind the interface with the relevant repository instance as per register function below
     * @var string[]
     */
    private array $services = [
        'bind' => [
            'Salesforce',
            'Campaign'
        ],
        'singleton' => []
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach($this->services as $method => $services) {
            foreach($services as $serviceName) {
                $this->app->{$method}("App\Interfaces\\{$serviceName}ServiceInterface", "App\Services\\{$serviceName}Service");
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
