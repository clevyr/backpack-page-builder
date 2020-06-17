<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Clevyr\PageBuilder\app\Models\PageFields;
use Clevyr\PageBuilder\app\Models\PageSection;
use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Clevyr\PageBuilder\app\Models\PageView;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Compilers\BladeCompiler;
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
     * @var PageFields $page_fields
     */
    private PageFields $page_fields;

    /**
     * PageBuilderFilesController constructor.
     *
     * @param PageView $page_view
     * @param PageSection $page_section
     * @param PageFields $page_fields
     */
    public function __construct(PageView $page_view, PageSection $page_section, PageFields $page_fields)
    {
        $this->views_path = config('backpack.pagebuilder.page_views_path',
            '/views/vendor/pagebuilder/views/');

        $this->sections_path = config('backpack.pagebuilder.section_views_path',
            '/views/backpack/pagebuilder/sections/');

        $this->page_view = $page_view;
        $this->page_section = $page_section;
        $this->page_fields = $page_fields;
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
            $this->loadSections();
            $this->loadViews();
            $this->loadFields();

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
     * Load Fields
     *
     * @return void
     */
    public function loadFields() : void
    {
        $filesystem = new Filesystem();
        $files = $filesystem->allFiles(resource_path('views/vendor/backpack/crud/fields'));

        foreach ($files as $file) {
            $this->page_fields->updateOrCreate([
                'name' => explode('.', $file->getBasename())[0],
            ], [
                'name' => explode('.', $file->getBasename())[0],
                'path' => $file->getPath(),
            ]);
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
        $files = $filesystem->allFiles($this->views_path);

        // Set empty ids array
        $ids = [];

        // iterate through files
        foreach($files as $file) {
            // Set file info
            $file_info = pathinfo($file);
            $base_name = $file_info['basename'];
            $human_name = explode('.', $base_name)[0] ?? false;

            // Check that a human name was generated
            if (!$human_name) {
                throw new Exception('Please make sure the layout is a .blade.php file');
            }

            // Create the path
            $path = $this->views_path . $base_name;

            // Check for a trashed layout
            $operation = $this->page_view->onlyTrashed()
                ->where('path', $path)
                ->firstOr(fn() => false);

            // Check if the layout exists
            if ($operation && $operation->exists()) {
                // Restore layout
                $operation->restore();
            } else {
                // Update or create non trashed layouts
                $operation = $this->page_view->updateOrCreate([
                    'path' => $path,
                ], [
                    'name' => $human_name,
                    'path' => $path,
                ]);

                $sections = $this->parseView($file, $human_name);

                foreach ($sections as $section) {
                    PageSectionsPivot::updateOrCreate([
                        'page_view_id' => $operation->id,
                        'section_id' => $section,
                    ], [
                        'page_view_id' => $operation->id,
                        'section_id' => $section,
                        'data' => '{}',
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
     * @throws Exception
     */
    public function loadSections()
    {
        if (!is_dir($this->sections_path)) {
            throw new Exception('Views path does not exist');
        }

        // Get files glob
        $filesystem = new Filesystem();
        $files = $filesystem->allFiles($this->sections_path);

        // Set empty ids array
        $ids = [];

        // iterate through files
        foreach($files as $file) {
            // Set file info
            $file_info = pathinfo($file);
            $base_name = $file_info['basename'];
            $human_name = explode('.', $base_name)[0] ?? false;

            // Check that a human name was generated
            if (!$human_name) {
                throw new Exception('Please make sure the layout is a .blade.php file');
            }

            $contents = $this->parseSection($file);

            // Set file info
            $file_info = pathinfo($file);
            $base_name = $file_info['basename'];
            $human_name = explode('.', $base_name)[0] ?? false;

            // Check that a human name was generated
            if (!$human_name) {
                throw new Exception('Please make sure the layout is a .blade.php file');
            }

            // Create the path
            $path = $this->sections_path . $base_name;

            // Check for a trashed layout
            $operation = $this->page_section->onlyTrashed()
                ->where('path', $path)
                ->firstOr(fn() => false);

            // Check if the layout exists
            if ($operation && $operation->exists()) {
                // Restore layout
                $operation->restore();
            } else {
                // Update or create non trashed layouts
                $operation = $this->page_section->updateOrCreate([
                    'path' => $path,
                ], [
                    'name' => $human_name,
                    'path' => $path,
                    'extras' => $contents,
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
    }

    /**
     * Parse Section
     *
     * Runs a regex on the loaded file for blade variables
     *
     * @param string $file
     * @param string $name
     *
     * @return bool|Collection
     * @throws Throwable
     */
    private function parseView(string $file, string $name)
    {
        $sections = new Collection();
        $content = strip_tags(file_get_contents($file));

        // Gather all sections
        preg_match_all("~('\s*(.*?)\s*')~", $content, $file_sections);

        foreach($file_sections[2] as $section) {
            $explode = explode('.', $section);
            $name = $explode[count($explode) - 1];

            $sections->push($this->page_section->where('name', $name)->pluck('id')->flatten()->toArray());
        }

        if ($sections->count() > 0) {
            return $sections->flatten();
        }

        return false;
    }

    /**
     * Parse Section
     *
     * Runs a regex on the loaded file for blade variables
     *
     * @param string $file
     *
     * @return bool|Collection
     */
    private function parseSection(string $file)
    {
        $content = strip_tags(file_get_contents($file));
        preg_match_all("~{{\s*(.*?)\s*}}~", $content, $matches);


        if (count($matches[1]) > 0) {
            return collect($matches[1])->map(function ($item) {
                return [
                    'name' => str_replace('$', '', $item),
                    'variable' => $item,
                ];
            });
        }

        return false;
    }
}
