<?php

namespace Clevyr\PageBuilder;

use App\User;
use Clevyr\PageBuilder\app\Console\Commands\CreatePage;
use Clevyr\PageBuilder\app\Console\Commands\SyncPages;
use Clevyr\PageBuilder\app\Models\Permission;
use Clevyr\PageBuilder\app\Models\Role;
use Clevyr\PageBuilder\app\Observers\PageObserver;
use Clevyr\PageBuilder\app\Observers\PageSectionsPivotObserver;
use Clevyr\PageBuilder\app\Console\Commands\CreateUser;
use Clevyr\PageBuilder\app\Console\Commands\Install;
use Clevyr\PageBuilder\app\Models\Page;
use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Clevyr\PageBuilder\app\Policies\PageBuilderCrudPolicy;
use Clevyr\PageBuilder\app\Policies\PermissionCrudPolicy;
use Clevyr\PageBuilder\app\Policies\RoleCrudPolicy;
use Clevyr\PageBuilder\app\Policies\UserCrudPolicy;
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
        User::class => UserCrudPolicy::class,
        Role::class => RoleCrudPolicy::class,
        Permission::class => PermissionCrudPolicy::class,
    ];

    /**
     * @var string[] $commands
     */
    protected $commands = [
        Install::class,
        CreateUser::class,
        CreatePage::class,
        SyncPages::class,
    ];

    /**
     * Set the route file location
     *
     * @var string $routeFilePath
     */
    public string $routeFilePath = '/routes/pagebuilder/pagebuilder.php';

    /**
     * @var string $permissionRoutePath
     */
    public string $permissionRoutePath = '/routes/backpack/permissionmanager.php';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot() : void
    {
        // Publish views
        $this->publishes([
            __DIR__ . '/resources/views/layouts' => base_path('resources/views/layouts'),
            __DIR__ . '/resources/views/pages' => base_path('resources/views/pages'),
            __DIR__ . '/resources/views/vendor' => base_path('resources/views/vendor'),
        ], 'views');

        // Publish migrations
        $this->publishes([__DIR__ . '/database/migrations' => database_path('migrations')], 'migrations');

        // Publish seeds
        $this->publishes([__DIR__ . '/database/seeds' => database_path('seeds')], 'seeds');

        // Publish Config
        $this->publishes([__DIR__ . '/config/pagebuilder.php' => config_path('backpack/pagebuilder.php')], 'configs');

        // Publish custom permissions config
        $this->publishes([__DIR__ . '/routes/pagebuilder/permissionmanager.php' => base_path($this->permissionRoutePath)], 'permissions-route');

        // Merge config
        $this->mergeConfigFrom(__DIR__. '/config/pagebuilder.php', 'backpack.pagebuilder');
        $this->mergeConfigFrom(__DIR__ . '/config/permissionmanager.php', 'backpack.permissionmanager');

        // Load Views
        $this->loadViewsFrom(realpath(__DIR__ . '/resources/views'), 'pagebuilder');

        $this->registerGates();
        $this->registerPolicies();
        $this->registerObservers();
    }
    /**
     * Register any package services
     *
     * @return void
     */
    public function register() : void
    {
        // Setup routes
        $this->setupRoutes();

        // Setup commands
        $this->commands($this->commands);
    }


    /**
     * Define the routes for the application.
     *
     * @return void
     */
    protected function setupRoutes() : void
    {
        $routerFilePathInUse = __DIR__ . $this->routeFilePath;

        if (file_exists(base_path() . $this->routeFilePath)) {
            $routerFilePathInUse = base_path() . $this->routeFilePath;
        }

        $this->loadRoutesFrom($routerFilePathInUse);
    }

    /**
     * Register Gates
     *
     * @return void
     */
    protected function registerGates() : void
    {
        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }

    /**
     * Register policies
     *
     * @return void
     */
    protected function registerPolicies() : void
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    /**
     * Register Observers
     *
     * @return void
     */
    protected function registerObservers() : void
    {
        PageSectionsPivot::observe(PageSectionsPivotObserver::class);
        Page::observe(PageObserver::class);
    }
}
