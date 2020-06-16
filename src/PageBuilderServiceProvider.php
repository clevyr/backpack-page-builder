<?php

namespace Clevyr\PageBuilder;

use Illuminate\Support\ServiceProvider;

class PageBuilderServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool $defer
     */
    protected $defer = false;

    /**
     * Set the route file location
     *
     * @var string $routeFilePath
     */
    public $routeFilePath = '/routes/pagebuilder/pagebuilder.php';

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

        // Publish Config
        $this->publishes([__DIR__ . '/config/pagebuilder.php' => config_path('backpack/pagebuilder.php')]);

        $this->mergeConfigFrom(__DIR__.'/config/pagebuilder.php', 'backpack.pagebuilder');
        $this->loadViewsFrom(realpath(__DIR__ . '/resources'), 'pagebuilder');
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
}
