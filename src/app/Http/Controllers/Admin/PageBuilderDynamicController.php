<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Clevyr\PageBuilder\app\Models\PageSectionsPivot;
use Illuminate\Http\Request;

class PageBuilderDynamicController extends Controller
{
    /**
     * @var PageSectionsPivot $page_sections_pivot;
     */
    private PageSectionsPivot $page_sections_pivot;

    /**
     * PageBuilderDynamicController constructor.
     * @param PageSectionsPivot $pageSectionsPivot
     */
    public function __construct(PageSectionsPivot $pageSectionsPivot)
    {
        $this->page_sections_pivot = $pageSectionsPivot;
    }
}
