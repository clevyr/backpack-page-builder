<?php

namespace Clevyr\PageBuilder\app\Console\Commands;

use Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;

/**
 * Class CreatePage
 * @package Clevyr\PageBuilder\app\Console\Commands
 */
class CreatePage extends GeneratorCommand
{
    use PrettyCommandOutput;

    protected $name = 'pagebuilder:page';

    /**
     * @var string $signature
     */
    protected $signature = 'pagebuilder:page {name}';

    /**
     * @var string $description
     */
    protected $description = 'Create a new page';

    /**
     * @var string $type
     */
    protected $type = 'Page';

    /**
     * Get Stub
     *
     * @return string[]
     */
    protected function getStub() : array
    {
        return [
            'index' => __DIR__ . '/../stubs/page/index.stub',
            'config' => __DIR__ . '/../stubs/page/config.stub',
            'section' => __DIR__ . '/../stubs/page/sections/default.stub',
        ];
    }

    /**
     * Handle
     *
     * @return void|bool
     * @throws FileNotFoundException
     */
    public function handle()
    {
        // Get folder name argument
        $name = $this->getNameInput();

        // Get the path
        $path = $this->getPath($name);

        // Check if the folder already exists
        if ($this->alreadyExists($path)) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Create the directories
        $this->files->makeDirectory($path);
        $this->files->makeDirectory($path . '/sections');

        // Place the files
        $this->files->put($path . '/index.blade.php', $this->getClass('index'));
        $this->files->put($path . '/config.php', $this->getClass('config'));
        $this->files->put($path . '/sections/default.blade.php', $this->getClass('section'));

        // Sync pages
        Artisan::call('pagebuilder:sync');

        $this->info($this->type . ' Created successfully');
    }

    /**
     * Already Exists
     *
     * Check if the folder already exists
     *
     * @param string $path
     * @return bool
     */
    protected function alreadyExists($path)
    {
        return $this->files->isDirectory($path);
    }

    /**
     * Get Path
     *
     * Get the destination folder path
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name) : string
    {
        return $this->laravel['path']
            . '/../resources/views/pages/'
            . str_replace('\\', '/', $name);
    }

    /**
     * Build Class
     *
     * @param $type
     * @return string
     * @throws FileNotFoundException
     */
    public function getClass($type) : string
    {
        return $this->files->get($this->getStub()[$type]);
    }
}
