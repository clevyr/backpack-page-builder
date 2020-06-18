<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Clevyr\PageBuilder\app\Models\PageSection;
use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Clevyr\PageBuilder\app\Models\PageView;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use SplFileInfo;
use Throwable;

/**
 * Class PageBuilderFilesController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderFilesController
{
    /**
     * @var Repository|Application|mixed|string $views_path
     */
    private string $views_path = '';

    /**
     * @var Repository|Application|mixed|string $sections_path
     */
    private string $sections_path = '';

    /**
     * @var PageView $page_view
     */
    private PageView $page_view;

    /**
     * @var PageSection $page_section
     */
    private PageSection $page_section;

    /**
     * PageBuilderFilesController constructor.
     *
     * @param PageView $page_view
     * @param PageSection $page_section
     */
    public function __construct(PageView $page_view, PageSection $page_section)
    {
        $this->views_path = resource_path() . '/views/pages/';

        $this->page_view = $page_view;

        $this->page_section = $page_section;
    }

    /**
     * Syncs
     *
     * Calls the functions to sync the views / sections
     *
     * @return array|bool[]
     */
    public function sync()
    {
        try {
            $this->loadViews();

            return [
                'success' => true,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
            ];
        }
    }

    /**
     * Load Views
     *
     * @throws Exception
     * @throws Throwable
     */
    public function loadViews()
    {
        if (!is_dir($this->views_path)) {
            throw new Exception('Views path does not exist');
        }

        // Get files glob
        $filesystem = new Filesystem();
        $pages = $filesystem->directories($this->views_path);

        // Set empty ids array
        $ids = [];

        // iterate through files
        foreach($pages as $page) {
            // Set file info
            $file_info = pathinfo($page);
            $folder_name = $file_info['basename'];

            if (!$filesystem->exists($page . '/config.php')) {
                throw new Exception('Configuration file for the ' . $folder_name . ' page does not exist.');
            }

            $sections = $this->parseSections($page, $folder_name);

            // Check for a trashed layout
            $operation = $this->page_view->onlyTrashed()
                ->where('name', $folder_name)
                ->firstOr(fn() => false);

            // Check if the layout exists
            if ($operation && $operation->exists()) {
                // Restore layout
                $operation->restore();
            } else {
                // Update or create non trashed layouts
                $operation = $this->page_view->updateOrCreate([
                    'name' => $folder_name,
                ], [
                    'name' => $folder_name,
                ]);

                foreach ($sections as $key => $section) {
                    PageSectionsPivot::updateOrCreate([
                        'page_view_id' => $operation->id,
                        'section_id' => $section,
                    ], [
                        'page_view_id' => $operation->id,
                        'section_id' => $section,
                        'order' => $key,
                    ]);
                }
            }

            // Update the $ids array with restored or new / updated records
            if ($operation->id) {
                $ids[] = $operation->id;
            }
        }

        // Delete layouts that are not found
        $this->page_view->whereNotIn('id', $ids)
            ->where('deleted_at', '=', null)
            ->delete();
    }

    /**
     * Parse Sections
     *
     * @param string $page
     * @param string $folder_name
     * @return array|mixed
     * @throws Exception
     */
    public function parseSections($page, $folder_name)
    {
        $config = include($page . '/config.php');

        $filesystem = new Filesystem();
        $files = $filesystem->allFiles($page . '/sections');

        return $this->addSections($files, $folder_name, $config);
    }

    /**
     * Add Sections
     *
     * @param SplFileInfo[] $files
     * @param string $folder_name
     * @param array $config
     *
     * @return mixed
     * @throws Exception
     */
    public function addSections($files, string $folder_name, $config)
    {
        $ids = [];

        foreach($files as $file) {
            // Set file info
            $file_info = pathinfo($file);
            $name = explode('.', $file_info['basename'])[0];
            $base_name = $folder_name . '-' . $name;

            // Check that a human name was generated
            if (!$name) {
                throw new Exception('Please make sure the ' . $name . ' page file exists.');
            }

            // Check for config key value based on the file name
            if (!$config[$name]) {
                throw new Exception('Configuration for the ' . $name . ' page was not found.');
            }

            // Check for a trashed layout
            $operation = $this->page_section->onlyTrashed()
                ->where('slug', $base_name)
                ->firstOr(fn() => false);

            // Check if the layout exists
            if ($operation && $operation->exists()) {
                // Restore layout
                $operation->restore();
            } else {
                // Update or create non trashed layouts
                $operation = $this->page_section->updateOrCreate([
                    'slug' => $base_name,
                ], [
                    'name' => $name,
                    'fields' => $config[$name] // Fields configuration,
                ]);
            }

            // Update the $ids array with restored or new / updated records
            if ($operation->id) {
                $ids[] = $operation->id;
            }
        }

        // Delete layouts that are not found
        $this->page_section->whereNotIn('id', $ids)
            ->where('deleted_at', '=', null)
            ->delete();

        return $ids;
    }
}
