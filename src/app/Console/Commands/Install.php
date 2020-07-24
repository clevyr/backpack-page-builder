<?php

namespace Clevyr\PageBuilder\app\Console\Commands;

use Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
use Illuminate\Console\Command;

class Install extends Command
{
    use PrettyCommandOutput;

    /**
     * @var $progressBar
     */
    protected $progressBar;

    /**
     * @var string $signature
     */
    protected $signature = 'pagebuilder:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * @var string $description
     */
    protected $description = 'Install Page Builder requirements on dev and publish files.';

    /**
     * Handle
     *
     * @return void
     */
    public function handle() : void
    {
        // Setup progress bar
        $this->progressBar = $this->output->createProgressBar();
        $this->progressBar->setMaxSteps(7);
        $this->progressBar->minSecondsBetweenRedraws(0);
        $this->progressBar->maxSecondsBetweenRedraws(120);
        $this->progressBar->setRedrawFrequency(1);

        // Start progress bar
        $this->progressBar->start();

        // Permissions Manager
        $this->info(' Laravel Backpack Permission Manager installation started, please wait');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
            '--tag' => 'migrations',
        ]);

        // Run migrations
        $this->migrate();

        // Publish config file
        $this->info(' Publishing Configuration files');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
            '--tag' => 'config',
        ]);

        // Publish permission manager
        $this->info(' Publishing permissions manager migrations');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Backpack\PermissionManager\PermissionManagerServiceProvider',
            '--tag' => 'migrations',
        ]);

        // Run migrations
        $this->migrate();

        // Publish page builder
        $this->info(' Publishing configs, views, and migrations');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Clevyr\PageBuilder\PageBuilderServiceProvider',
        ]);

        // Migrate
        $this->migrate();

        // Sync
        $this->sync();

        // Finish
        $this->progressBar->finish();
        $this->info('Page Builder Installation finished.');
    }

    /**
     * Migrate
     *
     * @return void
     */
    protected function migrate() : void
    {
        $this->info(' Running Migrations');
        $this->executeArtisanProcess('migrate');
    }

    /**
     * Sync
     *
     * @return void
     */
    protected function sync() : void
    {
        $this->info(' Syncing Pages');
        $this->executeArtisanProcess('pagebuilder:sync');
    }
}
