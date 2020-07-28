<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Exception;

/**
 * Class PageBuilderSectionPivotCrudController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderSectionPivotCrudController extends PageBuilderBaseController
{
    /**
     * Setup
     *
     * @throws Exception
     */
    public function setup()
    {
        $this->crud->setModel(config('backpack.pagebuilder.page_sections_pivot_model',
            'Clevyr\PageBuilder\app\Models\PageSectionsPivot'));
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/section-data');
        $this->crud->setEntityNameStrings('page section data', 'page section data');

        parent::setup();
    }
}
