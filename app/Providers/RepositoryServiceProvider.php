<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository prefix in here, it will automatically bind the interface with the relevant repository instance as per register function below
     * @var string[]
     */
    private $repositories = [
        'bind' => [
            'Contact'
        ],
        'singleton' => [
            'Salesforce'
        ]
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach($this->repositories as $method => $repos) {
            foreach($repos as $repo) {
                $this->app->{$method}("App\Interfaces\\{$repo}RepositoryInterface", "App\Repositories\\{$repo}Repository");
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
