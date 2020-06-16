<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;

/**
 * Class PageBuilderCrudController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderCrudController extends CrudController
{
    /**
     * Setup
     */
    public function setup()
    {
        Widget::add([
            'type' => 'view',
            'view' => 'vendor.backpack.pagebuilder.widgets.reload-files',
        ])
            ->to('after_content');
    }
}
