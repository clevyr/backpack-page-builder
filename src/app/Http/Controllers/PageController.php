<?php

namespace Clevyr\PageBuilder\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Clevyr\PageBuilder\app\Models\Page;
use Exception;
use Illuminate\Contracts\View\Factory;
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
     * @param $slug
     * @param null $subs
     *
     * @return void|View
     */
    public function index($slug, $subs = null)
    {
        try {
            $page = $this->page->where('slug', $slug)
                ->with(['view', 'sectionData'])
                ->firstOrfail();

            $this->data['view'] = $page->view;
            $this->data['sections'] = $this->formatSections($page->sectionData);

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
    protected function formatSections(Collection $sections)
    {
        return $sections->mapWithKeys(fn($item) => [$item->section->name => $item]);
    }
}
