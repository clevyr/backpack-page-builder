<?php

namespace Clevyr\PageBuilder\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Clevyr\PageBuilder\app\Models\Page;
use Exception;
use Illuminate\Contracts\View\Factory;
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
            if (!$subpage) {
                $page = $this->page->where('slug', $slug);
            } else {
                $page = $this->page->where('slug', $subpage);
            }

            $page = $page->with(['view', 'sections' => fn($query) => $query->orderBy('order', 'ASC')])
                ->firstOrfail();

            $this->data['view'] = $page->view;

            $this->data['menu'] = $this->page->menu()->get();

            if (!$page->is_dynamic) {
                $this->data['sections'] = function ($section, $field) use ($page) {
                    return $this->getSection($section, $field,
                        $this->formatSections($page->sections)
                    );
                };
            } else {
                $this->data['sections'] = $this->formatSections($page->sections);
            }

            $this->data['title'] = ucwords($page->title);

            $template = $page->view->name . '.' . 'index';

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
}
