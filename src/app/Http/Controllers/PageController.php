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
     * @return void|View
     */
    public function index(Request $request, string $slug = '/')
    {
        try {

            if ($slug === '/') {
                $slug = 'homepage';
            }

            $page = $this->page->where('slug', $slug)
                ->with(['view', 'sections' => fn ($query) => $query->orderBy('order', 'ASC')])
                ->firstOrfail();

            $this->data['view'] = $page->view;
            $this->data['sections'] = $this->formatSections($page->sections);

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
        return $sections->mapWithKeys(function ($item) {
            if (!$item->is_dynamic) {
                return [$item->name => $item];
            }

            return [$item->pivot->uuid => $item];
        });
    }
}
