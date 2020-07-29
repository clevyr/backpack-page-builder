<?php

namespace Clevyr\PageBuilder\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Clevyr\PageBuilder\app\Models\Page;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Class PageController
 */
class PageController extends Controller
{
    /**
     * @var Page $page
     */
    private Page $page;

    /**
     * @var array $data
     */
    protected array $data;

    /**
     * PageController constructor.
     *
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     *  Index
     *
     * @param Request $request
     * @param string $slug
     * @param string|null $subpage
     * @return void|View
     */
    public function index(Request $request, string $slug = '/', string $subpage = null)
    {
        try {
            // Set page
            $page = $this->page;

            // Check slug
            if (!$subpage) {
                $page = $page->where('slug', $slug);
            } else {
                $page = $page->where('slug', $subpage);
            }

            // Get page or fail
            $page = $page->with(['view', 'sections' => fn($query) => $query->orderBy('order', 'ASC')])
                ->firstOrfail();

            // If the page isn't published and the user isn't a super admin throw a 404
            if (!$page->is_published) {
                if (auth()->guest()) {
                    abort(404);
                } else if (!backpack_user()->can('Preview Pages')) {
                    abort(404);
                }
            }

            // Set view data
            $this->data['view'] = $page->view;

            // Set menu data
            $this->data['menu'] = $this->generateMenu();

            // Format sections
            if (!$page->is_dynamic) {
                $this->data['sections'] = function ($section, $field) use ($page) {
                    return $this->getSection($section, $field,
                        $this->formatSections($page->sections)
                    );
                };
            } else {
                $this->data['sections'] = $this->formatSections($page->sections);
            }

            // Set title
            $this->data['title'] = ucwords($page->title);

            // Set template
            $template = $page->view->name . '.' . 'index';

            // Return the view
            return view('pages.' . $template, $this->data);
        } catch(Exception $e) {
            abort(404);
        }
    }

    /**
     * Format Sections
     *
     * @param Collection $sections
     *
     * @return Collection
     */
    protected function formatSections(Collection $sections) : Collection
    {
        return $sections->mapWithKeys(function ($item) {
            if (!$item->is_dynamic) {
                return [$item->name => $item];
            }

            return [$item->pivot->uuid => $item];
        });
    }

    /**
     * Get Section
     *
     * Returns static section data
     *
     * @param string $section
     * @param string $field
     * @param $sections
     *
     * @return mixed
     */
    protected function getSection(string $section, string $field, $sections)
    {
        return $sections->toArray()[$section]['formatted_data'][$field];
    }

    /**
     * Generate Menu
     *
     * @return mixed
     */
    protected function generateMenu()
    {
        $user_check = !is_null(auth()->user())
            ? backpack_user()->can('Preview Page')
                ? true
                : false
            : false;

        if ($user_check) {
            return $this->page
                ->where('parent_id', null)
                ->with(['subpages' => function ($query) {
                    return $query
                        ->orderBy('lft');
                }])
                ->orderBy('lft')
                ->get();
        } else {
            return $this->page
                ->where('parent_id', null)
                ->where('published', true)
                ->with(['subpages' => function ($query) {
                    return $query
                        ->where('published', true)
                        ->orderBy('lft');
                }])
                ->orderBy('lft')
                ->get();
        }
    }
}
