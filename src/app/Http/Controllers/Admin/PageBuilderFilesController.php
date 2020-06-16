<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Clevyr\PageBuilder\app\Models\PageView;
use Exception;

/**
 * Class PageBuilderFilesController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderFilesController
{
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed|string
     */
    private string $views_path = '';

    /**
     * PageBuilderFilesController constructor.
     */
    public function __construct()
    {
        $this->views_path = config('backpack.pagebuilder.page_views_path', '/views/vendor/backpack/pagebuilder/views/');
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
        }
    }

    /**
     * Load Views
     *
     * @throws Exception
     */
    private function loadViews()
    {
        if (!is_dir($this->views_path)) {
            throw new Exception('Views path does not exist');
        }

        // Get files glob
        $files = glob($this->views_path . '*.php');

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
            $path = resource_path() . $this->views_path . $base_name;

            // Check for a trashed layout
            $operation = PageView::onlyTrashed()
                ->where('path', $path)
                ->firstOr(fn() => false);

            // Check if the layout exists
            if ($operation && $operation->exists()) {
                // Restore layout
                $operation->restore();
            } else {
                // Update or create non trashed layouts
                $operation = PageView::updateOrCreate([
                    'path' => $path,
                ], [
                    'name' => $human_name,
                    'path' => $path,
                ]);
            }

//            // Update the $ids array with restored or new / updated records
            if ($operation->id) {
                $ids[] = $operation->id;
            }
        }

        // Delete layouts that are not found
        PageView::whereNotIn('id', $ids)
            ->where('deleted_at', '=', null)
            ->delete();
    }
}
