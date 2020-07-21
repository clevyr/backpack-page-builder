<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;

/**
 * Class PageBuilderCrudController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderBaseController extends CrudController
{
    /**
     * Setup
     */
    public function setup()
    {
        // Add the reload-files widget to all page builder pages that extend this controller
        Widget::add([
            'type' => 'view',
            'view' => 'pagebuilder::base.widgets.reload-files',
        ])
            ->to('after_content');
    }
}
