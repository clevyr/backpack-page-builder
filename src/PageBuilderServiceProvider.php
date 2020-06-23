<?php

namespace Clevyr\PageBuilder;

use Clevyr\PageBuilder\app\Models\Page;
use Clevyr\PageBuilder\app\Policies\PageBuilderCrudPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PageBuilderServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool $defer
     */
    protected bool $defer = false;

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected array $policies = [
        Page::class => PageBuilderCrudPolicy::class,
    ];

    /**
     * Set the route file location
     *
     * @var string $routeFilePath
     */
    public string $routeFilePath = '/routes/pagebuilder/pagebuilder.php';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot() : void
    {
        // Publish views
        $this->publishes([__DIR__ . '/resources/views' => base_path('resources/views')], 'views');

        // Publish migrations
        $this->publishes([__DIR__ . '/database/migrations' => database_path('migrations')], 'migrations');

        // Publish seeds
        $this->publishes([__DIR__ . '/database/seeds' => database_path('seeds')], 'seeds');

        // Publish Config
        $this->publishes([__DIR__ . '/config/pagebuilder.php' => config_path('backpack/pagebuilder.php')]);

        $this->mergeConfigFrom(__DIR__.'/config/pagebuilder.php', 'backpack.pagebuilder');
        $this->loadViewsFrom(realpath(__DIR__ . '/resources/views'), 'pagebuilder');

        $this->registerGates();
        $this->registerPolicies();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function setupRoutes() : void
    {
        $routerFilePathInUse = __DIR__ . $this->routeFilePath;

        if (file_exists(base_path() . $this->routeFilePath)) {
            $routerFilePathInUse = base_path() . $this->routeFilePath;
        }

        $this->loadRoutesFrom($routerFilePathInUse);
    }

    /**
     * Register any package services
     *
     * @return void
     */
    public function register() : void
    {
        $this->setupRoutes();
    }

    /**
     * Register Gates
     *
     * Registers default gates
     *
     * @return void
     */
    public function registerGates() : void
    {
        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }

    public function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }
}
