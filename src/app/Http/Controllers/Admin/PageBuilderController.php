<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Clevyr\PageBuilder\app\Http\Controllers\Admin\PageBuilderCrudController as CrudController;
use Clevyr\PageBuilder\app\Http\Requests\PageCrud\PageCreateRequest;
use Clevyr\PageBuilder\app\Models\PageView;

class PageBuilderController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;

    public function setup()
    {
        $this->crud->setModel(config('backpack.pagebuilder.page_model_class', 'Clevyr\PageBuilder\app\Models\Page'));
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/pages');
        $this->crud->setEntityNameStrings('page', 'pages');

        $this->crud->setCreateView('vendor.backpack.pagebuilder.crud.pages.create');

        parent::setup();
    }

    public function setupCreateOperation()
    {
        $layout_options = PageView::get()->mapWithkeys(function ($map) {
           return [$map->id => $map->name];
        })->toArray();

        $this->crud->field('name')->type('text');
        $this->crud->field('title')->type('text');
        $this->crud->field('slug')->type('text');
        $this->crud->field('layout')
            ->type('select_from_array')
            ->options($layout_options);

        $this->crud->setValidation(PageCreateRequest::class);
    }

    public function setupListOperation()
    {
        $this->crud->setFromDb();
    }
}
