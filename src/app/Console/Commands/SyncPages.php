<?php

namespace Clevyr\PageBuilder\app\Console\Commands;

use Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
use Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderFilesController;
use Clevyr\PageBuilder\app\Models\Page;
use Clevyr\PageBuilder\app\Models\PageSection;
use Clevyr\PageBuilder\app\Models\PageView;
use Illuminate\Console\Command;

/**
 * Class SyncPages
 * @package Clevyr\PageBuilder\app\Console\Commands
 */
class SyncPages extends Command
{
    use PrettyCommandOutput;

    /**
     * @var $progressBar
     */
    protected $progressBar;

    /**
     * @var string $signature
     */
    protected $signature = 'pagebuilder:sync';

    /**
     * Handle
     *
     * @return void
     */
    public function handle() : void
    {
        $this->info('Syncing started');

        try {
            $sync = new PageBuilderFilesController(new Page, new PageView, new PageSection);
            $sync->loadViews();

            $this->info('Syncing Finished');
        } catch (\Throwable $e) {
            $this->error('There was an issue syncing the pages');
        }
    }
}
