<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Clevyr\PageBuilder\app\Models\Page;
use Clevyr\PageBuilder\app\Models\PageSection;
use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Clevyr\PageBuilder\app\Models\PageView;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\FileViewFinder;
use SplFileInfo;
use Throwable;

/**
 * Class PageBuilderFilesController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderFilesController extends Controller
{
    /**
     * @var Page $page
     */
    private Page $page;

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
     * @param Page $page
     * @param PageView $page_view
     * @param PageSection $page_section
     */
    public function __construct(Page $page, PageView $page_view, PageSection $page_section)
    {
        $this->page = $page;

        $this->page_view = $page_view;

        $this->page_section = $page_section;
    }

    /**
     * Syncs
     *
     * Calls the functions to sync the views / sections
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sync(Request $request)
    {
        // Pages can only be synced if the user is a super admin
        if(!backpack_user()->hasRole('Super Admin')) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                ], 403);
            }

            abort(403);
        }

        try {
            $this->loadViews();

            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
        $filesystem = new Filesystem();
        $pages = $filesystem->directories(resource_path() . '/views/pages');

        // Set empty ids array
        $ids = [];

        // iterate through files
        foreach($pages as $page) {
            // Set file info
            $file_info = pathinfo($page);
            $folder_name = $file_info['basename'];

            // Check for a trashed layout
            $operation = $this->page_view->onlyTrashed()
                ->where('name', $folder_name)
                ->firstOr(fn() => false);

            // Check if the layout exists
            if ($operation && $operation->exists()) {
                // Restore layout
                $operation->restore();
            } else {
                $is_dynamic = Str::contains($page, 'dynamic');

                // Update or create non trashed layouts
                $view = $this->page_view->updateOrCreate([
                    'name' => $folder_name,
                ], [
                    'name' => $folder_name,
                ]);

                if (!$is_dynamic) {
                    $page_entity = $this->page->updateOrCreate([
                        'name' => $folder_name,
                        'title' => $folder_name,
                        'page_view_id' => $view->id,
                    ], [
                        'name' => $folder_name,
                        'title' => $folder_name,
                        'page_view_id' => $view->id,
                        'slug' => Str::slug($folder_name),
                    ]);
                }

                // Check for page configuration
                if ($filesystem->exists($page . '/config.php')) {
                    // Load config
                    $config = include($page . '/config.php');

                    $is_dynamic = Str::contains($page, 'dynamic');

                    // Get sections
                    $sections = $this->parseSections($page, $folder_name, $config, $is_dynamic);

                    // update or create the pivot data for the static sections
                    if (!$is_dynamic) {
                        // Update sections
                        foreach ($sections as $key => $fields) {
                            $uoc = PageSectionsPivot::updateOrCreate([
                                'page_id' => $page_entity->id,
                                'section_id' => $key,
                            ], [
                                'page_id' => $page_entity->id,
                                'section_id' => $key,
                                'order' => $key,
                            ]);

                            if (is_null($uoc->data)) {
                                PageSectionsPivot::find($uoc->id)
                                    ->update([
                                        'data' => $this->parseFields($fields),
                                    ]);
                            }

                            $non_dynamic_sections[] = $uoc->id;
                        }

                        // Soft delete missing sections
                        PageSectionsPivot::where('page_id', $page_entity->id)
                            ->whereNotIn('id', $non_dynamic_sections)
                            ->delete();
                    }
                }
            }

            // Update the $ids array with restored or new / updated records
            if ($view->id) {
                $ids[] = $view->id;
            }
        }

        // Delete layouts that are not found
        $this->page_view->whereNotIn('id', $ids)
            ->where('deleted_at', '=', null)
            ->delete();
    }

    /**
     * Parse Fields
     *
     * Pulls out the field's key and creates a new empty array
     *
     * @param array $fields
     * @return array
     */
    private function parseFields(array $fields) : array
    {
        return collect($fields)
            ->keys()
            ->mapWithKeys(fn(string $key): array => [$key => ''])
            ->toArray();
    }

    /**
     * Parse Sections
     *
     * Iterates through the sections and adds them to the database
     *
     * @param string $page
     * @param string $folder_name
     * @param array $config
     * @param bool $is_dynamic
     * @return array|mixed
     * @throws Exception
     */
    public function parseSections(string $page, string $folder_name, array $config, bool $is_dynamic)
    {
        $filesystem = new Filesystem();
        $files = $filesystem->allFiles($page . '/sections');

        return $this->addSections($files, $folder_name, $config, $is_dynamic);
    }

    /**
     * Add Sections
     *
     * Adds the sections to the database
     *
     * @param SplFileInfo[] $files
     * @param string $folder_name
     * @param array $config
     * @param bool $base_is_dynamic
     * @return mixed
     * @throws Exception
     */
    public function addSections($files, string $folder_name, array $config, bool $base_is_dynamic)
    {
        $sections = [];

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
                // Check for base_is_dynamic, base_is_dynamic is only set to true if the sections
                // are being loaded from the dynamic folder
                if (!$base_is_dynamic) {
                    // Check if the section config is set to dynamic
                    $is_dynamic = isset($config[$name]['is_dynamic']) && $config[$name]['is_dynamic'];

                    // Unset the is_dynamic property if it is dynamic so we don't insert it into
                    // the data column
                    if ($is_dynamic) {
                        unset($config[$name]['is_dynamic']);
                    }
                } else {
                    // Set is_dynamic to true if it is in the dynamic folder
                    $is_dynamic = true;
                }

                // Update or create non trashed layouts
                $operation = $this->page_section->updateOrCreate([
                    'slug' => $base_name,
                ], [
                    'name' => $name,
                    'fields' => $config[$name], // Fields configuration,
                    'is_dynamic' => $is_dynamic,
                ]);
            }

            // Update the $ids array with restored or new / updated records
            if ($operation->id) {
                $sections[$operation->id] = $config[$name];
            }
        }

        return $sections;
    }
}
