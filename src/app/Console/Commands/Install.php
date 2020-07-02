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
        $this->progressBar->setMaxSteps(14);
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
        $this->progressBar->advance();

        // Run migrations
        $this->migrate();

        // Publish config file
        $this->info(' Publishing Configuration files');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
            '--tag' => 'config',
        ]);
        $this->progressBar->advance();

        // Publish permission manager
        $this->info(' Publishing permissions manager');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Backpack\PermissionManager\PermissionManagerServiceProvider',
        ]);
        $this->progressBar->advance();

        // Run migrations
        $this->migrate();

        // Publish page builder
        $this->info(' Publishing configs, views, and migrations');
        $this->executeArtisanProcess('vendor:publish', [
            '--provider' => 'Clevyr\PageBuilder\PageBuilderServiceProvider',
        ]);
        $this->progressBar->advance();

        // Migrate
        $this->migrate();

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
        $this->progressBar->advance();
    }
}
